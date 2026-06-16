<?php
declare(strict_types=1);

namespace App\Helpers;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;

/**
 * Converts the gradient encoding of an Adobe-style SVG 1.1 file into the
 * expanded, style-based form produced by older SVG 1.0 exporters.
 *
 * It does NOT just relabel version="1.0"; it reshapes the gradients:
 *   1. Flattens xlink:href / href inheritance so every <linearGradient> and
 *      <radialGradient> carries its own complete <stop> list (chains resolved).
 *   2. Moves stop-color / stop-opacity presentation attributes into
 *      style="stop-color:...;stop-opacity:...", expanding #abc -> #AABBCC.
 *   3. Collapses gradientTransform transform-lists into a single matrix(...).
 *   4. Sets version="1.0" and drops the 1.1-only baseProfile attribute.
 *
 * Pure DOMDocument/DOMXPath - no Composer dependency.
 */
final class Svg11To10Converter
{
    private const SVG_NS   = 'http://www.w3.org/2000/svg';
    private const XLINK_NS = 'http://www.w3.org/1999/xlink';

    /** Presentation attributes whose value is (or may contain) a color. */
    private const COLOR_ATTRS = [
        'fill', 'stroke', 'color', 'stop-color',
        'flood-color', 'lighting-color', 'solid-color',
    ];

    /** Gradient attributes that inherit through xlink:href (SVG spec). */
    private const INHERITABLE = [
        'gradientUnits', 'gradientTransform', 'spreadMethod',
        'x1', 'y1', 'x2', 'y2',            // linearGradient geometry
        'cx', 'cy', 'r', 'fx', 'fy', 'fr', // radialGradient geometry
    ];

    public function convertString(string $svg): string
    {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = true;
        $doc->formatOutput = false;

        libxml_use_internal_errors(true);
        if (!$doc->loadXML($svg)) {
            libxml_clear_errors();
            throw new RuntimeException('Не удалось разобрать файл как XML/SVG.');
        }
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('svg', self::SVG_NS);
        $xpath->registerNamespace('xlink', self::XLINK_NS);

        $this->flattenGradients($xpath);
        $this->normalizeStops($xpath);
        $this->normalizeGradientTransforms($xpath);
        $this->downgradeRoot($doc);
        $this->normalizeAllColors($xpath);

        return $doc->saveXML();
    }

    /**
     * Читает версию из корневого элемента <svg>.
     *
     * @return string|null Например "1.0" или "1.1"; null, если атрибута нет
     *                     или файл не разобрался как SVG.
     */
    public function detectVersion(string $svg): ?string
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $ok = $doc->loadXML($svg);
        libxml_clear_errors();

        if (!$ok
            || $doc->documentElement === null
            || $doc->documentElement->localName !== 'svg'
        ) {
            return null;
        }

        $v = trim($doc->documentElement->getAttribute('version'));
        return $v === '' ? null : $v;
    }

    /**
     * Конвертирует только при необходимости.
     *
     * Если файл уже объявлен как version="1.0" — возвращается без изменений,
     * байт в байт (никакой переразметки/пересериализации).
     * Во всех остальных случаях (1.1, отсутствует, 1.2, ...) прогоняется
     * через convertString().
     */
    public function convertIfNeeded(string $svg): string
    {
        return $this->detectVersion($svg) === '1.0'
            ? $svg
            : $this->convertString($svg);
    }

    /**
     * Загрузка файла → конвертация при необходимости → запись результата.
     * Файлы 1.0 копируются как есть.
     */
    public function convertFile(string $in, string $out = null): void
    {
        if (is_null($out))
            $out = $in;

        $svg = file_get_contents($in);

        if ($svg === false) {
            throw new RuntimeException("Не удалось прочитать файл: {$in}");
        }

        if ($this->detectVersion($svg) !== '1.0')
            file_put_contents($out, $this->convertString($svg));
    }

    // --- 1. Flatten xlink:href / href inheritance ------------------------

    private function flattenGradients(DOMXPath $xpath): void
    {
        /** @var array<string,DOMElement> $byId */
        $byId = [];
        foreach ($xpath->query('//svg:linearGradient | //svg:radialGradient') as $g) {
            /** @var DOMElement $g */
            if ($g->hasAttribute('id')) {
                $byId[$g->getAttribute('id')] = $g;
            }
        }

        foreach ($xpath->query('//svg:linearGradient | //svg:radialGradient') as $g) {
            /** @var DOMElement $g */
            $chain = $this->hrefChain($g, $byId);          // [$g, base, base-of-base, ...]

            // Fill in inheritable attributes from the nearest ancestor that defines them.
            foreach (self::INHERITABLE as $attr) {
                if ($g->hasAttribute($attr)) {
                    continue;
                }
                foreach ($chain as $anc) {
                    if ($anc->hasAttribute($attr)) {
                        $g->setAttribute($attr, $anc->getAttribute($attr));
                        break;
                    }
                }
            }

            // If this gradient has no stops, copy them from the nearest ancestor that does.
            if (!$this->hasStops($g)) {
                foreach ($chain as $anc) {
                    if ($anc !== $g && $this->hasStops($anc)) {
                        foreach ($anc->childNodes as $child) {
                            if ($child instanceof DOMElement && $child->localName === 'stop') {
                                $g->appendChild($child->cloneNode(true));
                            }
                        }
                        break;
                    }
                }
            }
        }

        // Strip every href now that stops/attributes are materialised.
        foreach ($xpath->query('//svg:linearGradient | //svg:radialGradient') as $g) {
            /** @var DOMElement $g */
            $g->removeAttributeNS(self::XLINK_NS, 'href');
            if ($g->hasAttribute('href')) {
                $g->removeAttribute('href');
            }
        }
    }

    /** @param array<string,DOMElement> $byId @return DOMElement[] */
    private function hrefChain(DOMElement $g, array $byId): array
    {
        $chain = [$g];
        $seen  = [];
        $cur   = $g;
        while (true) {
            $ref = $cur->getAttributeNS(self::XLINK_NS, 'href');
            if ($ref === '') {
                $ref = $cur->getAttribute('href'); // plain href fallback (SVG2)
            }
            if ($ref === '' || $ref[0] !== '#') {
                break;
            }
            $id = substr($ref, 1);
            if (!isset($byId[$id]) || isset($seen[$id])) {
                break; // missing target or cyclic reference
            }
            $seen[$id] = true;
            $cur = $byId[$id];
            $chain[] = $cur;
        }
        return $chain;
    }

    private function hasStops(DOMElement $g): bool
    {
        foreach ($g->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'stop') {
                return true;
            }
        }
        return false;
    }

    // --- 2. stop-color attribute -> style="stop-color:..." ---------------

    private function normalizeStops(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//svg:stop') as $stop) {
            /** @var DOMElement $stop */
            $style = $this->parseStyle($stop->getAttribute('style'));

            foreach (['stop-color', 'stop-opacity'] as $prop) {
                if ($stop->hasAttribute($prop)) {
                    // presentation attribute fills the gap only if style lacks it
                    if (!isset($style[$prop])) {
                        $style[$prop] = $stop->getAttribute($prop);
                    }
                    $stop->removeAttribute($prop);
                }
            }

            if (isset($style['stop-color'])) {
                $style['stop-color'] = $this->normalizeColor($style['stop-color']);
            }

            if ($style !== []) {
                $stop->setAttribute('style', $this->buildStyle($style));
            }
        }
    }

    /** @return array<string,string> */
    private function parseStyle(string $style): array
    {
        $out = [];
        foreach (explode(';', $style) as $decl) {
            if (strpos($decl, ':') === false) {
                continue;
            }
            [$k, $v] = explode(':', $decl, 2);
            $k = trim($k);
            if ($k !== '') {
                $out[$k] = trim($v);
            }
        }
        return $out;
    }

    /** @param array<string,string> $style */
    private function buildStyle(array $style): string
    {
        $parts = [];
        foreach ($style as $k => $v) {
            $parts[] = "{$k}:{$v}";
        }
        return implode(';', $parts);
    }

    /** #abc -> #AABBCC, #aabbcc -> #AABBCC; leaves named colors untouched. */
    private function normalizeColor(string $c): string
    {
        $c = trim($c);
        if (preg_match('/^#([0-9a-fA-F]{3})$/', $c, $m)) {
            $h = $m[1];
            $c = '#' . $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
        }
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $c)) {
            $c = strtoupper($c);
        }
        return $c;
    }

    // --- 2b. Global colour normalisation --------------------------------

    /**
     * Раскрывает короткие HEX-цвета (#abc -> #AABBCC) и приводит 6-значные
     * к верхнему регистру по всему документу — в цветовых атрибутах
     * (fill, stroke, color, *-color) и внутри атрибутов style.
     *
     * Ссылки url(#id) (например fill="url(#linear-gradient)") и значения
     * вроде none / currentColor остаются нетронутыми. 4- и 8-значные HEX
     * (#rgba / #rrggbbaa) тоже не трогаются, чтобы не повредить альфа-канал.
     */
    private function normalizeAllColors(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//*') as $el) {
            /** @var DOMElement $el */
            foreach (self::COLOR_ATTRS as $attr) {
                if ($el->hasAttribute($attr)) {
                    $old = $el->getAttribute($attr);
                    $new = $this->normalizeColorsInText($old);
                    if ($new !== $old) {
                        $el->setAttribute($attr, $new);
                    }
                }
            }
            if ($el->hasAttribute('style')) {
                $old = $el->getAttribute('style');
                $new = $this->normalizeColorsInText($old);
                if ($new !== $old) {
                    $el->setAttribute('style', $new);
                }
            }
        }
    }

    /**
     * Нормализует все самостоятельные HEX-токены в произвольной строке,
     * пропуская содержимое url(...). Переиспользует normalizeColor().
     */
    private function normalizeColorsInText(string $text): string
    {
        // Порядок альтернатив важен: сперва url(...) (его не трогаем),
        // затем 6-значный HEX, затем 3-значный. Lookahead (?![0-9a-fA-F])
        // не даёт зацепить часть более длинного (4/8-значного) HEX.
        $pattern = '/url\([^)]*\)'
            . '|#[0-9a-fA-F]{6}(?![0-9a-fA-F])'
            . '|#[0-9a-fA-F]{3}(?![0-9a-fA-F])/';

        return preg_replace_callback(
            $pattern,
            function (array $m): string {
                $tok = $m[0];
                if (stripos($tok, 'url(') === 0) {
                    return $tok; // ссылка на градиент/паттерн — не трогаем
                }
                return $this->normalizeColor($tok);
            },
            $text
        ) ?? $text;
    }

    // --- 3. gradientTransform list -> single matrix(...) -----------------

    private function normalizeGradientTransforms(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//svg:linearGradient | //svg:radialGradient') as $g) {
            /** @var DOMElement $g */
            if (!$g->hasAttribute('gradientTransform')) {
                continue;
            }
            $m = $this->transformListToMatrix($g->getAttribute('gradientTransform'));
            if ($m !== null) {
                $g->setAttribute('gradientTransform', $this->matrixToString($m));
            }
        }
    }

    /** @return float[]|null [a,b,c,d,e,f] */
    private function transformListToMatrix(string $list): ?array
    {
        $result = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0]; // identity
        if (!preg_match_all('/(\w+)\s*\(([^)]*)\)/', $list, $mm, PREG_SET_ORDER)) {
            return null;
        }
        foreach ($mm as $fn) {
            $name = $fn[1];
            $args = preg_split('/[\s,]+/', trim($fn[2]));
            $args = array_map('floatval', array_filter($args, static fn($v) => $v !== ''));
            $m = $this->primitiveMatrix($name, $args);
            if ($m === null) {
                return null; // unknown primitive -> leave original alone
            }
            $result = $this->multiply($result, $m);
        }
        return $result;
    }

    /** @param float[] $a @return float[]|null */
    private function primitiveMatrix(string $name, array $a): ?array
    {
        switch ($name) {
            case 'matrix':
                return count($a) === 6 ? $a : null;
            case 'translate':
                return [1, 0, 0, 1, $a[0] ?? 0, $a[1] ?? 0];
            case 'scale':
                $sx = $a[0] ?? 1;
                return [$sx, 0, 0, $a[1] ?? $sx, 0, 0];
            case 'rotate':
                $r = deg2rad($a[0] ?? 0);
                $cos = cos($r); $sin = sin($r);
                if (isset($a[1], $a[2])) { // rotate around a point
                    $t1 = [1, 0, 0, 1, $a[1], $a[2]];
                    $rot = [$cos, $sin, -$sin, $cos, 0, 0];
                    $t2 = [1, 0, 0, 1, -$a[1], -$a[2]];
                    return $this->multiply($this->multiply($t1, $rot), $t2);
                }
                return [$cos, $sin, -$sin, $cos, 0, 0];
            case 'skewX':
                return [1, 0, tan(deg2rad($a[0] ?? 0)), 1, 0, 0];
            case 'skewY':
                return [1, tan(deg2rad($a[0] ?? 0)), 0, 1, 0, 0];
            default:
                return null;
        }
    }

    /** @param float[] $m1 @param float[] $m2 @return float[] */
    private function multiply(array $m1, array $m2): array
    {
        [$a1,$b1,$c1,$d1,$e1,$f1] = $m1;
        [$a2,$b2,$c2,$d2,$e2,$f2] = $m2;
        return [
            $a1*$a2 + $c1*$b2,
            $b1*$a2 + $d1*$b2,
            $a1*$c2 + $c1*$d2,
            $b1*$c2 + $d1*$d2,
            $a1*$e2 + $c1*$f2 + $e1,
            $b1*$e2 + $d1*$f2 + $f1,
        ];
    }

    /** @param float[] $m */
    private function matrixToString(array $m): string
    {
        $fmt = static function (float $v): string {
            $s = rtrim(rtrim(sprintf('%.4f', $v), '0'), '.');
            return $s === '-0' ? '0' : $s;
        };
        return 'matrix(' . implode(' ', array_map($fmt, $m)) . ')';
    }

    // --- 4. Root downgrade ----------------------------------------------

    private function downgradeRoot(DOMDocument $doc): void
    {
        $svg = $doc->documentElement;
        if ($svg === null || $svg->localName !== 'svg') {
            return;
        }
        $svg->setAttribute('version', '1.0');
        if ($svg->hasAttribute('baseProfile')) {
            $svg->removeAttribute('baseProfile');
        }
        // SVG 1.0 still uses the xlink namespace declaration; keep it.
        if (!$svg->hasAttribute('xmlns:xlink')) {
            $svg->setAttributeNS(
                'http://www.w3.org/2000/xmlns/', 'xmlns:xlink', self::XLINK_NS
            );
        }
    }
}

