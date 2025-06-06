@extends('layouts.app',['title' => 'Add new Builder'])
@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/easy-autocomplete/1.3.5/easy-autocomplete.min.css" />
{{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
<div class="card-body">
	<div class="row">
		<div class="col-md-12 text-center">
			<h4>Add New Builder:</h4>
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
	<div class="row">
		<div class="form-group center-form">
			<label for="svg">1.Type in/Select product to add builder to:</label>
			<input type="text" id="basics" class="form-control">
		</div>
	</div>
	<div class="row" id="showForm">
		<form action="{{route('builder.store')}}" method="POST" enctype="multipart/form-data" class="center-form">
			{{ csrf_field() }}
			<div id="selectType" class="form-group invisible">
                <label>2. Type of Builder</label>
                <label class="d-flex gap-5">
                    <input type="radio" name="builder_type" value="classic" class="">
                    <span>Classic</span>
                </label>
                <label class="d-flex gap-5">
                    <input type="radio" name="builder_type" value="dynamic" class="">
                    <span>Dynamic Theme</span>
                </label>
                <label class="d-flex gap-5">
                    <input type="radio" name="builder_type" value="artisan" class="">
                    <span>Artisan Theme</span>
                </label>
            </div>
			<div id="selectFile" class="form-group invisible">
				<label for="name">3.Upload SVG file:</label>
				<input type="hidden" name="shopify_id" id="idProduct">
				<input type="file" class="form-control-file border" name="uploadSVG" >
			</div>
			<button class="btn btn-primary" type="submit">Submit</button>
		</form>
	</div>
</div>
@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/easy-autocomplete/1.3.5/jquery.easy-autocomplete.min.js"></script>
<script type="text/javascript">
    const $basics = $("#basics");
    const $idProduct = $("#idProduct");
    const $selectFile = $("#selectFile");
    const $selectType = $("#selectType");
	const options = {
		url: function(phrase) {
			//console.log(phrase);
			return "/api/info";
		},
		getValue: function(element){
			return element.title;
		},
		requestDelay: 800,
		highlightPhrase: true,
		ajaxSettings: {
			dataType: "json",
			method: "GET",
			data: {
				dataType: "json",
			}
		},
		preparePostData: function(data) {
			data.phrase = $("#basics").val();
			return data;
		},
		list: {
			maxNumberOfElements: 30,
			match: {
				enabled: true
			},
			showAnimation: {
				type: "fade", //normal|slide|fade
				time: 200,
			},
			hideAnimation: {
				type: "slide", //normal|slide|fade
				time: 200,
			}
		},
		template: {
			type: "custom",
			method: function(value, item){
				return '<a href="#" onclick="checkAvailability(' + item.id + ')">' + item.title + '</a>';
			}
		}
	};

	$basics.easyAutocomplete(options);

	function checkAvailability(id){
		$.ajax({
			url: '/api/availability/' + id,
			success: function(result){
				if(result.message == 'yes'){
					$selectType.removeClass('invisible');
					$idProduct.val(id);
				}else{
					alert('this product has a SVG file');
				}
			}
		});
	}

    function selectType(e) {
        const value = $(this).val();

        switch (value) {
            case 'classic':
            case 'dynamic':
                $selectFile.removeClass('invisible');
                break;
            case 'artisan':
                $selectFile.addClass('invisible');
                break;
        }
    }

    $(document).on('click', '[name="builder_type"]', selectType);
</script>
@endsection
