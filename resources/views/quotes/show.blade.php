@extends('layouts.app',['title' => 'View Reference #'.$quote->id])
@section('styles')
<style type="text/css">
	.fa-square:before{
		text-shadow: 1px 1px 2px #000000;
	}
</style>
@endsection
@section('content')
<div class="card-body">
	<div class="row">
		<div class="col-md-5 text-left"><h4>Web Inquiry #{{ $quote->id }}</h4></div>
		<div class="col-md-7 text-right">
			<form method="post" action="{{ route('quote.resend',$quote->id) }}">
				@csrf
				<input type="hidden" name="quote_id" value="{{ $quote->id }}">
				<label class="">Resend Emails to:</label>
				<label class="ml-3 mr-3">Client <input type="checkbox" name="send_email[]" value="client"></label>
				<label class="ml-3 mr-3">Admin <input type="checkbox" name="send_email[]" value="admin"></label>
				<button type="submit" class="btn btn-primary-custom btn-primary">Submit</button>
			</form>
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
		<div class="col-md-6">
			<h4 class="font-weight-bold">Contact Information:</h4>
			<p class="mb-1"><span class="font-weight-bold">Reference #:</span> {{ $quote->id }}</p>
			<!--<p class="mb-1"><span class="font-weight-bold">Registered Dealer:</span> {{$customerShopify}}</p>-->
			<p class="mb-1"><span class="font-weight-bold">Name:</span> {{ $quote->client->name ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">Company / Org:</span> {{ $quote->client->company ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">Address:</span> {{ $quote->client->address ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">Address 2:</span> {{ $quote->client->address_2 ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">City:</span> {{ $quote->client->city ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">Prov/State::</span> {{ $quote->client->state ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">Postal Code:</span> {{ $quote->client->zip ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">Country:</span> {{ $quote->client->country ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">Email:</span> {{ $quote->client->email ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">Phone:</span> {{ $quote->client->phone ?? '-' }}</p>
			<p class="mb-1"><span class="font-weight-bold">Date Required:</span> {{ $quote->date_required ?? '-' }}</p>
		</div>
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-12">
					<h4 class="font-weight-bold">Artwork Placement and Order Description:</h4>
				</div>
				<div class="col-md-12">
					<p>{!! $quote->description ? nl2br($quote->description) : 'No description' !!}</p>
				</div>
			</div>
		</div>
	</div>
	<hr>
	<div class="row mt-5">
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-12">
					<h4 class="font-weight-bold">Quote Details:</h4>
				</div>
			</div>
			 @forelse($products as $product)
			<div class="row">
				<div class="col-md-12">
					<p class="mb-1">{{ $loop->iteration }}. {{ $product['data']->title }}</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 mt-2">
					@foreach($product['data']->colors as $color)
					<p class="mb-1"><span class="font-weight-bold">Color {{ $loop->iteration }}:</span> {{ $color->name }} <i class="fa fa-square" style="color:{{ $color->code }};font-size:20px;"></i></p>
					@endforeach
					<p class="font-weight-bold text-danger">Quantity: {{ (isset($product['quantity']))?$product['quantity']:'0' }} </p>
				</div>
				<div class="col-md-6">
					<img src="{{ $product['url_svg_temp'] }}" alt="">
				</div>
			</div>
			<hr>
			@empty
			@endforelse
			<p class="font-weight-bold text-danger">Total Quantity: {{ $quote->styles->sum('quantity') }}</p>

		</div>
		{{--  
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-12">
					<h4 class="font-weight-bold">Files: </h4>
				</div>
				<ul>
					@forelse($quote->files as $file)
					<li>
						<p class="mb-1">{{ $file->name }} <a href="{{ asset($file->url) }}" download="{{ $file->url }}"><i class="fa fa-download"></i></a></p>
					</li>
					@empty
					@endforelse
				</ul>
			</div>
		</div>
		--}}
	</div>
</div>
@endsection