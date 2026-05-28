<?php

namespace App\Http\SVG;

use DOMDocument;
use App\Http\SVG\lotColor;
use App\Http\SVG\lotSVGHelper;

class Svg
{
    public static function GetDataFromSVGOptimized($filepath)
    {
        $svgContent = file_get_contents(public_path('jerseys/' . $filepath . '.svg'));

        // Быстрый поиск всех HEX-цветов через регулярные выражения (без полного парсинга DOM)
        $colors = [];
        preg_match_all('/fill="(#[0-9a-fA-F]{3,6})"/', $svgContent, $fillMatches);
        preg_match_all('/stop-color:(#[0-9a-fA-F]{3,6})/', $svgContent, $stopMatches);

        $colors = array_unique(array_merge($fillMatches[1] ?? [], $stopMatches[1] ?? []));

        // Обработка градиентов (если они критичны)
        $gradients = [];
        if (strpos($svgContent, '<linearGradient') !== false) {
            preg_match_all('/<linearGradient.*?>(.*?)<\/linearGradient>/s', $svgContent, $gradientMatches);

            foreach ($gradientMatches[0] as $gradientXML) {
                preg_match_all('/stop-color:(#[0-9a-fA-F]{3,6})/', $gradientXML, $gradientColors);
                if (!empty($gradientColors[1])) {
                    $gradients[] = [
                        'startColor' => $gradientColors[1][0],
                        'endColor' => end($gradientColors[1]),
                        'colors' => array_slice($gradientColors[1], 1, -1),
                    ];
                }
            }
        }

        return [
            'background' => '#000000',
            'colors' => $colors,
            'linearGradients' => $gradients,
        ];
    }

    /**
     * We Obtain all data of svg File
     * like: colors and gradient Colors if anyone exists into the file
     * @param string $filepath
     * @return Info Array
     */
    public static function GetDataFromSVG($filepath)
    {
        $svgFile = file_get_contents(public_path('jerseys/' . $filepath . '.svg'));

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadXML($svgFile);

        $elements = $dom->getElementsByTagName('*');

        $colors = [];
        foreach ($elements as $elm) {
            if ($elm->hasAttribute('fill')) {
                $tmpAttrVal = $elm->getAttribute('fill');
                //if (!in_array($tmpAttrVal, ['', 'none']) && !in_array($tmpAttrVal, $colors) && preg_match("/^#[A-Za-z0-9]{3,6}$/i", $tmpAttrVal)) {
                if ($tmpAttrVal && $tmpAttrVal !== 'none' && strpos($tmpAttrVal, '#') === 0) {
                    $colors[] = $tmpAttrVal;
                }
            }
        }

        if (!empty($colors)) {
            $colors = array_unique(array_filter($colors));
        }

        $linearGradients = $dom->getElementsByTagName('linearGradient');
        $linearGradientsArray = [];
        $idGrad = 0;

        foreach ($linearGradients as $linearGradient) {
            $linearGradientsArray[$idGrad] = [];
            foreach ($linearGradient->getElementsByTagName('stop') as $stopEl) {
                if ($stopEl->hasAttribute('style')) {
                    $tmpAttrVal = $stopEl->getAttribute('style');
                    $colorTmp = str_replace('stop-color:', '', $tmpAttrVal);
                    if (preg_match("/^#[A-Za-z0-9]{3,6}$/i", $colorTmp)) {
                        $linearGradientsArray[$idGrad][] = $colorTmp;
                    }
                }
            }
            $idGrad++;
        }
        $linearGradientsRawArray = array_unique($linearGradientsArray, SORT_REGULAR);

        $iLnGr = 0;
        $linearGradientsArray = [];
        if (count($linearGradientsRawArray) > 0) {
            foreach ($linearGradientsRawArray as $linearGradientsRaw) {
                if (count($linearGradientsRaw) > 0) {
                    if (count($linearGradientsRaw) == 1) {
                        $tmpLnGr = array_shift($linearGradientsRaw);
                        $linearGradientsArray[$iLnGr] = array(
                            'startColor' => $tmpLnGr,
                            'endColor' => $tmpLnGr,
                            'colors' => [],
                        );
                    } else {
                        $linearGradientsArray[$iLnGr] = array(
                            'startColor' => array_shift($linearGradientsRaw),
                            'endColor' => array_pop($linearGradientsRaw),
                        );

                        if (count($linearGradientsRaw) > 0) {
                            $linearGradientsArray[$iLnGr]['colors'] = $linearGradientsRaw;
                        } else {
                            $linearGradientsArray[$iLnGr]['colors'] = [];
                        }
                    }
                    $iLnGr++;
                }
            }
        }
        $result = [];
        $result['background'] = '#000000';

        if (count($colors) > 0) {
            $result['colors'] = $colors;
        }
        if (count($linearGradientsArray) > 0) {
            $result['linearGradients'] = $linearGradientsArray;
        }
        return $result;
    }

    /**
     * We write new data just like colors and gradients
     * into the SVG file
     * @param int $itemId
     * @param string $newFilename
     * @param array $svgData
     * @return Info Array
     */
    public static function setDataToNewSVG($itemId, $newFilename, $svgData)
    {
        $svgFile = file_get_contents(public_path('jerseys/' . $newFilename . '.svg'));
        $result = [];
        $colors = [];
        $iColor = 0;
        if (isset($svgData['colors']) && is_array($svgData['colors'])) {

            foreach ($svgData['colors'] as $color) {
                $iColor++;
                $className = 'ts_item_' . $itemId . '_color' . $iColor;
                $colors[$className] = $color;
                $svgFile = str_replace('fill="' . $color . '"', 'class="' . $className . '"', $svgFile);
            }
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadXML($svgFile);
        $background = [];
        if (isset($svgData['background'])) {
            $className = 'ts_item_' . $itemId . '_bgd';
            $background[$className] = $svgData['background'];

            if ($dom->documentElement->hasAttribute('class')) {
                $dom->documentElement->removeAttribute('class');
            }

            $dom->documentElement->setAttribute('class', $className);

            $result['background'] = $background;
        }


        $colorToGradient = [];

        if (isset($svgData['linearGradients'])) {
            $linearGradients = [];
            $linearGradientsForSave = [];

            $iGradient = 0;
            foreach ($svgData['linearGradients'] as $linearGradient) {
                $iGradient++;
                $className = 'ts_item_' . $itemId . '_gradient_' . $iGradient;

                $linearGradient['color_from'] = mb_strtoupper($linearGradient['color_from']);
                $linearGradient['color_to'] = mb_strtoupper($linearGradient['color_to']);

                $startColor = new LotColor();
                $startColor->setFromHex($linearGradient['color_from']);

                $endColor = new LotColor();
                $endColor->setFromHex($linearGradient['color_to']);

                $gradients = LotSVGHelper::getGradients($startColor, $endColor);

                if (isset($linearGradient['color_from'])) {

                    $linearGradients[$className][] = $linearGradient['color_from'];
                    $inxRGBtmp = LotSVGHelper::getIndexOfNearestColor($startColor, $gradients);
                    $linearGradientsForSave[$className][] = array(
                        'color' => $linearGradient['color_from'],
                        'idxRGB' => $inxRGBtmp,
                    );

                    if (in_array($linearGradient['color_from'], $colors)) {
                        $colorClassName = array_search($linearGradient['color_from'], $colors);
                        $colorToGradient[$colorClassName][] = array('point' => $inxRGBtmp, 'gradientClass' => $className); //
                    } elseif (in_array($linearGradient['color_from'], $background)) {

                        $colorClassName = array_search($linearGradient['color_from'], $background);
                        $colorToGradient[$colorClassName][] = array('point' => $inxRGBtmp, 'gradientClass' => $className);

                    } else {
                        $iColor++;
                        $colorClassName = 'ts_item_' . $itemId . '_color' . $iColor;
                        $colors[$colorClassName] = $linearGradient['color_from'];
                        $colorToGradient[$colorClassName][] = array('point' => $inxRGBtmp, 'gradientClass' => $className);
                    }
                } else {
                    continue;
                }

                if (isset($linearGradient['colors']) && is_array($linearGradient['colors'])) {
                    foreach ($linearGradient['colors'] as $color) {
                        $color = mb_strtoupper($color);
                        $colorClass = new LotColor(); //
                        $colorClass->setFromHex($color);
                        $linearGradients[$className][] = $color;
                        $linearGradientsForSave[$className][] = array(
                            'color' => $color,
                            'idxRGB' => LotSVGHelper::getIndexOfNearestColor($colorClass, $gradients),
                        );
                    }
                }

                if (isset($linearGradient['color_to'])) {
                    $linearGradients[$className][] = $linearGradient['color_to'];
                    $inxRGBtmp = LotSVGHelper::getIndexOfNearestColor($endColor, $gradients);
                    $linearGradientsForSave[$className][] = array(
                        'color' => $linearGradient['color_to'],
                        'idxRGB' => $inxRGBtmp,
                    );

                    if (in_array($linearGradient['color_to'], $colors)) {
                        $colorClassName = array_search($linearGradient['color_to'], $colors);
                        $colorToGradient[$colorClassName][] = array('point' => $inxRGBtmp, 'gradientClass' => $className); //
                    } elseif (in_array($linearGradient['color_to'], $background)) {
                        $colorClassName = array_search($linearGradient['color_to'], $background);
                        $colorToGradient[$colorClassName][] = array('point' => $inxRGBtmp, 'gradientClass' => $className);

                    } else {
                        $iColor++;
                        $colorClassName = 'ts_item_' . $itemId . '_color' . $iColor;
                        $colors[$colorClassName] = $linearGradient['color_to'];
                        $colorToGradient[$colorClassName][] = array('point' => $inxRGBtmp, 'gradientClass' => $className);
                    }

                } else {
                    unset($linearGradients[$className]);
                    continue;
                }

            }

            if (count($linearGradients) > 0) {

                $linearGradientsXML = $dom->getElementsByTagName('linearGradient');

                foreach ($linearGradientsXML as $linearGradient) {
                    $linearGradientsArray = [];
                    foreach ($linearGradient->getElementsByTagName('stop') as $stopEl) {
                        if ($stopEl->hasAttribute('style')) {
                            $tmpAttrVal = $stopEl->getAttribute('style');
                            $colorTmp = str_replace('stop-color:', '', $tmpAttrVal);
                            if (preg_match("/^#[A-Za-z0-9]{3,6}$/i", $colorTmp)) {
                                $linearGradientsArray[] = mb_strtoupper($colorTmp);
                            }
                        }
                    }
                    foreach ($linearGradients as $className => $gradientsArray) {
                        if ($linearGradientsArray == $gradientsArray) {
                            $linearGradient->setAttribute('class', $className);
                        }
                    }
                }

                $result['linearGradients'] = $linearGradientsForSave;
            }
        }

        if (count($colors) > 0)
            $result['colors'] = $colors;

        $result['colorToGradient'] = $colorToGradient;

        $domStyle = new DOMDocument('1.0', 'utf-8');
        $style = LotSVGHelper::generateSVGCss($result);
        $elementStyle = $dom->createElement('style', $style);
        $elementStyle->setAttribute('id', 'tsCustomStyle');
        $elementStyle->setAttribute('type', 'text/css');
        $dom->documentElement->appendChild($elementStyle);

        file_put_contents(public_path('jerseys/' . $newFilename . '.svg'), $dom->saveXML());

        return $result;

    }

    /**
     * We update only the the SVG styles
     * into the SVG file
     * @param string $svgPath
     * @param array $svgData
     * @return Bool
     */
    public static function updateStyleSVG($svgPath, $svgData)
    {

        $File = 'jerseys/' . $svgPath . '.svg';
        if ($File) {
            $svgFile = file_get_contents(public_path('jerseys/' . $svgPath . '.svg'));
            $style = LotSVGHelper::generateSVGCss($svgData);
            $dom = new DOMDocument('1.0', 'utf-8');
            $dom->loadXML($svgFile);
            $linearGradientsXML = $dom->getElementsByTagName('linearGradient');

            foreach ($linearGradientsXML as $linearGradient) {

                if ($linearGradient->hasAttribute('class')
                    && isset($svgData['linearGradients'][$linearGradient->getAttribute('class')])
                ) {
                    $gradientColors = $svgData['linearGradients'][$linearGradient->getAttribute('class')];
                    reset($gradientColors);
                    foreach ($linearGradient->getElementsByTagName('stop') as $stopEl) {
                        if ($stopEl->hasAttribute('style')) {
                            $tmpGrad = current($gradientColors);
                            $stopEl->setAttribute('style', 'stop-color:' . $tmpGrad['color']);
                        }
                        next($gradientColors);
                    }
                }
            }
            $oldStyle = $dom->getElementsByTagName('style');

            foreach ($oldStyle as $node) {
                $node->parentNode->removeChild($node);
            }

            $elementStyle = $dom->createElement('style', $style);
            $elementStyle->setAttribute('id', 'tsCustomStyle');
            $elementStyle->setAttribute('type', 'text/css');
            $dom->documentElement->appendChild($elementStyle);

            if (file_put_contents(public_path('jerseys/' . $svgPath . '.svg'), $dom->saveXML())) {
                return true;
            }
        }
        return false;
    }
}



