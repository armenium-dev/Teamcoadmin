@extends('layouts.app',['title' => 'Add new Garment Type'])
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/easy-autocomplete/1.3.5/easy-autocomplete.min.css"/>
    {{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 text-center">
                <h4>View Garment Type:</h4>
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
        <div class="mx-auto max-w-500">
            <div class="mb-20">
                <div>1. Main Image:</div>
                @if($garment->main_image)
                    <img src="{{ Storage::url($garment->main_image) }}" class="img-fluid d-block" alt=""/>
                @endif
            </div>
            <div class="mb-20">
                <div>2. Garment Code:</div>
                <div>{{$garment->code}}</div>
            </div>
            <div class="mb-20">
                <div>3. Main Title:</div>
                <div>{{$garment->title}}</div>
            </div>
            <div class="mb-20">
                <div>4. Description:</div>
                <div>{{$garment->description}}</div>
            </div>
            <div class="mb-20">
                <div>5. Size Chart:</div>
                @if($garment->size_image)
                    <img src="{{ Storage::url($garment->size_image) }}" class="img-fluid d-block" alt=""/>
                @endif
            </div>
        </div>
    </div>
@endsection
