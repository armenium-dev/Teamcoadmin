@extends('layouts.app',['title' => 'Change Default colours'])
@section('styles')
    {{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 text-center">
                <h5>Edit product:</h5>
                <h4>{{$product->name}}</h4>
            </div>
        </div>

        <hr>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="row">
            <div class="col-md-4">
                @if($product->url_svg)
                <div class="form-row text-center">
                    <div class="col-md-12">
                        <div class="containerSVG">
                            @svg("public/jerseys/$product->url_svg",(isset($variantColors['background']) && is_array($variantColors['background']))?key($variantColors['background']).' image':'some image')
                            <div class="middle">
                                <button class="btn btn-success" id="showForm">Change SVG</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="form-row text-center invisible" id="hiddenForm">
                    <form action="{{route('builder.updatefilesvg',$product->id)}}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input name="_method" type="hidden" value="PATCH">
                        <div class="col-md-12">
                            <input type="hidden" name="shopify_id" id="{{$product->id}}">
                            <div class="form-group">
                                <input type="file" class="form-control-file border" name="uploadSVG">
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-danger closeForm" type="button">Cancel</button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="form-row text-center" id="HideChange">
                    <div class="col-md-12">

                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <h5 class="section-title">Product default colours:</h5>
                <form method="POST" action="{{route('builder.updatecolors',$product->id)}}">
                    @csrf
                    <input name="_method" type="hidden" value="PATCH">
                    @if(isset($variantColors['background']) && is_array($variantColors['background']))
                        @foreach($variantColors['background'] as $key => $value)
                            <div class="row form-group">
                                <div class="col-md-3">
                                    <select fortype="background" class="form-control colorChange" name="colors[{{$key}}]" id="{{$key}}">
                                        @foreach($Colors as $code => $color)
                                            <option style="background-color: {{$code}}" value="{{$code}}" {{($value == $code)?'selected':''}}>{{$color}}</option>
                                        @endforeach
                                        @if(!isset($Colors[$value]))
                                            <option style="background-color: {{$value}}" value="$value" selected="selected">Default</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check label">
                                        <label class=" form-check-label" for="">
                                            <input type="checkbox" class="form-check-input" name="hide[{{$key}}]" {{(isset($variantColors['hide']) && in_array($key,$variantColors['hide']))?'checked="checked"':''}}><span>Hide</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="sort[{{$key}}]" id="{{$key}}" class="form-control">
                                        @for($i = 1; $i < $positions;$i++)
                                            <option value="{{$i}}" {{(isset($variantColors['sort'],$variantColors['sort'][$key])&& $variantColors['sort'][$key]==$i)?'selected="selected"':''}}>{{$i}}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @if(isset($variantColors['colors']))
                        @foreach($variantColors['colors'] as $key => $value)
                            <div class="row form-group">
                                <div class="col-md-3">
                                    <select fortype="colors" class="form-control colorChange" name="colors[{{$key}}]" id="{{$key}}">
                                        @foreach($Colors as $code => $color)
                                            <option style="background-color: {{$code}}" value="{{$code}}" {{($value == $code)?'selected':''}}>{{$color}}</option>
                                        @endforeach
                                        @if(!isset($Colors[$value]))
                                            <option style="background-color: {{$value}}" value="$value" selected="selected">Default</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check label">
                                        <label class=" form-check-label" for="">
                                            <input type="checkbox" class="form-check-input" name="hide[{{$key}}]" {{(isset($variantColors['hide']) && in_array($key,$variantColors['hide']))?'checked="checked"':''}}> <span>Hide</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="sort[{{$key}}]" id="{{$key}}" class="form-control">
                                        @for($i = 1; $i < $positions;$i++)
                                            <option value="{{$i}}" {{(isset($variantColors['sort'],$variantColors['sort'][$key])&& $variantColors['sort'][$key]==$i)?'selected="selected"':''}}>{{$i}}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <div class="row form-group">
                        <div class="col-md-6 text-left">
                            <label class="single">
                                <input type="checkbox" class="form-check-input" name="color_autoupdate" {{(isset($product->color_autoupdate) && $product->color_autoupdate == 1) ? 'checked="checked"' : ''}}>
                                <span>Automatically update all colors of this product when adding, editing, and deleting a set of colors.</span>
                            </label>
                        </div>
                        <div class="col-md-6 text-left">
                            <button class="btn btn-primary" type="submit">Update</button>
                        </div>
                    </div>
                </form>

                <hr>

                <div class="colorset-ctrl">
                    <a class="select" data-type="all" role="button">Select all</a>
                    <a class="select" data-type="none" role="button">Unselect all</a>
                    <h5 class="section-title">Product color sets:</h5>
                </div>
                <form method="POST" action="{{route('builder.updatecolorsets',$product->id)}}">
                    @csrf
                    <input name="_method" type="hidden" value="PATCH">
                    <div class="form-group">
                        <div class="row color-sets-list">
                            @foreach($Colors as $code => $color)
                                <div class="col-md-2">
                                    <label>
                                        <div class="color-box {{(isset($colorSets) && in_array($code, $colorSets)) ? '' : 'trans-half'}}" style="background-color: {{$code}}" }}></div>
                                        <div class="check-box">
                                            <input type="checkbox" class="" name="colorset[]" value="{{$code}}" {{(isset($colorSets) && in_array($code, $colorSets)) ? 'checked="checked"' : ''}}> <span>{{$color}}</span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 text-left">
                            <button class="btn btn-primary" type="submit">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        let jsonData = @json($jsonData);
    var itemColor = {
        background: jsonData.background,
        colors: jsonData.colors,
        gradients: [],
        selected: '',
        type: '',
        lineargradients: jsonData.linearGradients,
        colorToGradient: jsonData.colorToGradient,
        calculateGradients: function(startColor, endColor) {
            this.gradients = [];
            for (var i = 0; i < 256; i++) {
                this.gradients[i] = [];
                this.gradients[i].push(Math.round(startColor.r + (endColor.r - startColor.r) * i / 255));
                this.gradients[i].push(Math.round(startColor.g + (endColor.g - startColor.g) * i / 255));
                this.gradients[i].push(Math.round(startColor.b + (endColor.b - startColor.b) * i / 255));
            }
        },
        setGradient: function(className, startColor, endColor) {
            this.calculateGradients(startColor, endColor);
            lengthOfGradient = this.lineargradients[className].length;
            for (var i = 0; i < lengthOfGradient; i++) {
                var tmpColorArray = this.gradients[this.lineargradients[className][i]['idxRGB']];
                var gradColor = itemColor.rgbToHex(tmpColorArray[0], tmpColorArray[1], tmpColorArray[2]);
                this.lineargradients[className][i]['color'] = gradColor;
                $('[forClass="' + className + '"][num="' + i + '"]').css('background-color', gradColor);
            }
            $('.' + className).each(function(i) {
                $(this).children('stop').each(function(j) {
                    $(this).css('stop-color', itemColor.lineargradients[className][j]['color']);
                });
            });
        },
        setTmpGradient: function(className, startColor, endColor) {
            this.calculateGradients(startColor, endColor);
            $('.' + className).each(function(i) {
                $(this).children('stop').each(function(j) {
                    var tmpColorArray = itemColor.gradients[itemColor.lineargradients[className][j]['idxRGB']];
                    var gradColor = itemColor.rgbToHex(tmpColorArray[0], tmpColorArray[1], tmpColorArray[2]);
                    $(this).css('stop-color', gradColor);
                    $('[forClass="' + className + '"][num="' + j + '"]').css('background-color', gradColor);
                });
            });
        },
        componentToHex: function(c) {
            var hex = c.toString(16);
            return hex.length == 1 ? "0" + hex : hex;
        },
        rgbToHex: function(r, g, b) {
            return "#" + this.componentToHex(r) + this.componentToHex(g) + this.componentToHex(b);
        },
        hexToRgb: function(hex) {
            var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        },
        convertRGB: function(color) {
            var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(color);
            var red = parseInt(digits[2]);
            var green = parseInt(digits[3]);
            var blue = parseInt(digits[4]);
            return {
                r: red,
                g: green,
                b: blue
            }
        },
        resetGradientsForColorClass: function(colorClassName) {
            if (typeof itemColor.colorToGradient[colorClassName] !== 'undefined') {
                for (var iCTG in itemColor.colorToGradient[colorClassName]) {
                    var gradientClass = itemColor.colorToGradient[className][iCTG]['gradientClass'];
                    lengthOfGraduent = itemColor.lineargradients[gradientClass].length;
                    itemColor.setTmpGradient(gradientClass, itemColor.hexToRgb(itemColor.lineargradients[gradientClass][0]['color']), itemColor.hexToRgb(itemColor.lineargradients[gradientClass][lengthOfGraduent - 1]['color']));
                }
            }
        },
        setGradientsForColorClass: function(colorClassName, colorChange) {
            if (typeof itemColor.colorToGradient[colorClassName] !== 'undefined') {
                var gradChangeColor = colorChange;
                for (var iCTG in itemColor.colorToGradient[colorClassName]) {
                    var gradientClass = itemColor.colorToGradient[colorClassName][iCTG]['gradientClass'];
                    lengthOfGraduent = itemColor.lineargradients[gradientClass].length;
                    if (itemColor.colorToGradient[colorClassName][iCTG]['point'] > 0) {
                        itemColor.setGradient(gradientClass, itemColor.hexToRgb(itemColor.lineargradients[gradientClass][0]['color']), itemColor.hexToRgb(gradChangeColor));
                    } else {
                        itemColor.setGradient(gradientClass, itemColor.hexToRgb(gradChangeColor), itemColor.hexToRgb(itemColor.lineargradients[gradientClass][lengthOfGraduent - 1]['color']));
                    }
                }
            }
        }
    };

    $(document).ready(function(){
        $(document).on('change', '.colorChange', function(event){
            var className;
            var selectedColor;
            selectedColor = $(this).find(":selected").val();
            itemColor.selected = className = $(this).attr('id');
            itemColor.type = $(this).attr('forType');
            if(itemColor.selected != '' && itemColor.type != ''){
                switch(itemColor.type){
                    case 'colors':
                        $('.' + itemColor.selected).css('fill', selectedColor);
                        itemColor.colors[className] = selectedColor;
                        itemColor.setGradientsForColorClass(className, selectedColor);
                        break;
                    case 'background':
                        $('.' + itemColor.selected).css('fill', selectedColor);
                        itemColor.background[className] = selectedColor;
                        itemColor.setGradientsForColorClass(className, selectedColor);
                        break;
                }
            } else {
                alert('Select color for change!');
            }
        });
        $('#showForm').click(function(){
            $('#HideChange').addClass('invisible');
            $('#hiddenForm').removeClass('invisible')
        });
        $('.closeForm').click(function(){
            $('#hiddenForm').addClass('invisible');
        });
        $('.colorset-ctrl .select').on('click', function(){
            var type = $(this).data('type');
            var $colors_list = $('.color-sets-list');
            switch(type){
                case 'all':
                    $colors_list
                        .find('.color-box').removeClass('trans-half')
                        .end()
                        .find('input[type="checkbox"]').attr('checked', true).prop('checked', true);
                    break;
                case 'none':
                    $colors_list
                        .find('.color-box').addClass('trans-half')
                        .end()
                        .find('input[type="checkbox"]').attr('checked', false).prop('checked', false);
                    break;
            }
        });
        $('.color-sets-list').find('input[type="checkbox"]').on('click', function(){
            if($(this).is(':checked')){
                $(this).parents('label').find('.color-box').removeClass('trans-half');
            }else{
                $(this).parents('label').find('.color-box').addClass('trans-half');
            }
        });
    });
</script>
@endsection
