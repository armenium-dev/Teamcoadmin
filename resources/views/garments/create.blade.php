@extends('layouts.app',['title' => 'Add new Garment Type'])
@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/easy-autocomplete/1.3.5/easy-autocomplete.min.css" />
{{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
<div class="card-body">
	<div class="row">
		<div class="col-md-12 text-center">
			<h4>Add New Garment Type:</h4>
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
	@if ($errors->any())
	<div class="alert alert-danger">
		<ul>
			@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
	@endif
    <form action="{{route('garment.store')}}" method="POST" enctype="multipart/form-data" class="mx-auto max-w-500">
        @csrf
        <div class="form-group">
            <label for="name">1. Main Image:</label>
            <input type="file" class="form-control-file border" name="mainImage">
        </div>
        <div class="form-group">
            <label for="name">2. Garment Code:</label>
            <input type="text" class="form-control" name="code">
        </div>
        <div class="form-group">
            <label for="name">3. Main Title:</label>
            <input type="text" class="form-control" name="title">
        </div>
        <div class="form-group">
            <label for="name">4. Description:</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="name">5. Size Chart:</label>
            <input type="file" class="form-control-file border" name="sizeImage">
        </div>

        <button class="btn btn-primary" type="submit">Submit</button>
    </form>
</div>
@endsection
