@extends('layouts.app',['title' => 'Manage SVG'])
@section('styles')
    <style type="text/css">
        .fa-square:before {
            text-shadow: 1px 1px 2px #000000;
        }
    </style>
@endsection
@section('content')
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 text-center"><h4>Manage SVG:</h4></div>
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
            <div class="col-md-3">
                @if($product->url_svg)
                    @svg("public/jerseys/$product->url_svg")
                @endif
            </div>
            <div class="col-md-9">
                <form method="POST" action="{{route('builder.update',$product->id)}}">
                    @csrf
                    <input name="_method" type="hidden" value="PATCH">
                    @if($infoSVG)
                        <div class="row">
                            <div class="col-md-3">
                                Colour 0 :
                            </div>
                            <div class="col-md-2">
                                {{$infoSVG['background']}}
                            </div>
                            <div class="col-md-1">
                                <i class="fa fa-square" style="color:{{$infoSVG['background']}};font-size: 25px;"></i>
                            </div>
                            <div class="col-md-1">
                                <input type="checkbox" class="form-check-input" name="item[background]" value="{{$infoSVG['background']}}">
                            </div>
                        </div>
                        @foreach($infoSVG['colors'] as $color)
                            <div class="row">
                                <div class="col-md-3">
                                    Colour {{$loop->index+1}} :
                                </div>
                                <div class="col-md-2">
                                    {{$color}}
                                </div>
                                <div class="col-md-1">
                                    <i class="fa fa-square" style="color:{{$color}};font-size: 25px;"></i>
                                </div>
                                <div class="col-md-1">
                                    <input type="checkbox" class="form-check-input" name="item[colors][{{$loop->index}}]" value="{{$color}}">
                                </div>
                            </div>
                        @endforeach
                        @if(isset($infoSVG['linearGradients']))
                            @foreach($infoSVG['linearGradients'] as $key => $gradient)
                                <div class="row">
                                    <div class="col-md-3">
                                        Linear Gradient {{$loop->index}} :
                                    </div>
                                    <div class="col-md-2">
                                        {{$gradient['startColor']}}
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fa fa-square" style="color:{{$gradient['startColor']}};font-size: 25px;"></i>
                                        <input type="hidden" name="item[linear_grad_color][{{$key}}][color_from]" value="{{$gradient['startColor']}}">
                                        @foreach($gradient['colors'] as $colorGradiant)
                                            <i class="fa fa-square" style="color:{{$colorGradiant}};font-size: 25px;"></i>
                                            <input name="item[linear_grad_color][{{$key}}][colors][]" value="{{$colorGradiant}}" type="hidden">
                                        @endforeach
                                        <i class="fa fa-square" style="color:{{$gradient['endColor']}};font-size: 25px;"></i>
                                        <input type="hidden" name="item[linear_grad_color][{{$key}}][color_to]" value="{{$gradient['endColor']}}">
                                    </div>
                                    <div class="col-md-2">
                                        {{$gradient['endColor']}}
                                        <i class="fa fa-square" style="color:{{$gradient['endColor']}};font-size: 25px;"></i>
                                    </div>
                                    <div class="col-md-1">
                                        <input type="checkbox" class="form-check-input" name="item[lin_grad][{{$key}}]" value="{{$loop->index}}">
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endif
                    <hr>
                    <div class="row text-center">
                        <div class="col-md-12 text-center">
                            <button class="btn btn-primary" type="submit">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
