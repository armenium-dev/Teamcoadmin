@extends('layouts.app', ['title' => 'Web Roster '.$roster->id])
@section('styles')
<style type="text/css">
	.fa-square:before {text-shadow: 1px 1px 2px #000000;}
	@media print {
		table,
		table th,
		table thead th,
		table tbody th,
		table td,
		table tbody td,
		table tfoot td,
		table tr {border: 1px solid #000 !important;}
		.print-mt-big {margin-top: 80px;}
		.pagebreak {clear: both; page-break-before: always;}
	}
</style>
@endsection
@section('content')
<div class="card-body">
	<div class="row">
		<div class="col-md-12 text-center">
            <a href="{{route('roster.edit', $roster->id)}}" class="float-sm-right">Edit</a>
            <h4>Roster #{{ $roster->id }}</h4>
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
		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">1. (a) Contact and Shipping Information:</h4>
			<table class="table table-bordered border-balck narrow-first-col slim-td-padding">
				<tbody>
					<tr>
						<td><b>Reference #:</b></td>
						<td>{{ $roster->reference }}</td>
					</tr>
					<tr>
						<td><b>Name:</b></td>
						<td>{{ $roster->client->name ?? 'No Name' }}</td>
					</tr>
					<tr>
						<td><b>Company / Org:</b></td>
						<td>{{ $roster->client->company ?? 'No Company' }}</td>
					</tr>
					<tr>
						<td><b>Address:</b></td>
						<td>{{ $roster->client->address ?? 'No Address' }}</td>
					</tr>
					<tr>
						<td><b>Address 2:</b></td>
						<td>{{ $roster->client->address_2 ?? 'No Address' }}</td>
					</tr>
					<tr>
						<td><b>City:</b></td>
						<td>{{ $roster->client->city ?? 'No City' }}</td>
					</tr>
					<tr>
						<td><b>Prov/State:</b></td>
						<td>{{ $roster->client->state ?? 'No State' }}</td>
					</tr>
					<tr>
						<td><b>Postal Code:</b></td>
						<td>{{ $roster->client->zip ?? 'No Postal Code' }}</td>
					</tr>
					<tr>
						<td><b>Country:</b></td>
						<td>{{ $roster->client->country ?? 'No Country' }}</td>
					</tr>
					<tr>
						<td><b>Email:</b></td>
						<td>{{ $roster->client->email ?? 'No Email' }}</td>
					</tr>
					<tr>
						<td><b>Phone:</b></td>
						<td>{{ $roster->client->phone ?? 'No Phone' }}</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">1. (b) Billing Information:</h4>
			<table class="table table-bordered border-balck narrow-first-col slim-td-padding">
				<tbody>
					<tr>
						<td><b>Name:</b></td>
						<td>{{ $roster->client->billing->name ?? 'No Name' }}</td>
					</tr>
					<tr>
						<td><b>Company / Org:</b></td>
						<td>{{ $roster->client->billing->company ?? 'No Company' }}</td>
					</tr>
					<tr>
						<td><b>Address:</b></td>
						<td>{{ $roster->client->billing->address ?? 'No Address' }}</td>
					</tr>
					<tr>
						<td><b>Address 2:</b></td>
						<td>{{ $roster->client->billing->address_2 ?? 'No Address' }}</td>
					</tr>
					<tr>
						<td><b>City:</b></td>
						<td>{{ $roster->client->billing->city ?? 'No City' }}</td>
					</tr>
					<tr>
						<td><b>Prov/State:</b></td>
						<td>{{ $roster->client->billing->state ?? 'No State' }}</td>
					</tr>
					<tr>
						<td><b>Postal Code:</b></td>
						<td>{{ $roster->client->billing->zip ?? 'No Postal Code' }}</td>
					</tr>
					<tr>
						<td><b>Country:</b></td>
						<td>{{ $roster->client->billing->country ?? 'No Country' }}</td>
					</tr>
					<tr>
						<td><b>Email:</b></td>
						<td>{{ $roster->client->billing->email ?? 'No Email' }}</td>
					</tr>
					<tr>
						<td><b>Phone:</b></td>
						<td>{{ $roster->client->billing->phone ?? 'No Phone' }}</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">2. Shipping Method:</h4>
			<p class="mb-1">{!! $roster->shipping_method ? $roster->shipping_method : 'No Preference - Teamco will choose' !!}</p>
		</div>
		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">3. Jersey Details</h4>
			<table class="table table-bordered border-balck narrow-first-col slim-td-padding">
				<tbody>
					<tr>
						<td><b>Style Code:</b></td>
						<td>{{ $roster->jersey->style_code }}</td>
					</tr>
					@if(isset($jersey_detail))
						@forelse($jersey_detail as $detail)
							<tr>
								<td>
									<b>Color {{ $loop->iteration }}:</b>
								</td>
								<td>
									{{ ucfirst($detail) }}
								</td>
							</tr>
						@empty
						@endforelse
					@endif
				</tbody>
			</table>
		</div>
		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">4. Accessory Items:</h4>
			<p class="mb-1">{{ $roster->accessory_items ?? 'No accessory items' }}</p>
		</div>
		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">5. Numbers Colors:</h4>
			<table class="table table-bordered border-balck narrow-first-col slim-td-padding">
				<tbody>
					<tr>
						<td><b>Number Colors:</b></td>
						<td>{{ $roster->number_color }}</td>
					</tr>
					<!--
					<tr>
						<td><b>Inside color:</b></td>
						<td>{{ $roster->inside_color }}</td>
					</tr>
					<tr>
						<td><b>Outside color:</b></td>
						<td>{{ $roster->outside_color }}</td>
					</tr>
					-->
				</tbody>
			</table>
		</div>
		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">6. Artwork Placement and Comments:</h4>
			<p class="mb-1">{!! $roster->comments ? nl2br($roster->comments) : 'No comments' !!}</p>
		</div>
		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">8. Jersey Quantities Per Set:</h4>
			<table class="table table-bordered border-balck narrow-first-col slim-td-padding">
				<thead>
				@if($roster->jersey_set_name)
					<tr>
						<th class="font-dark-blue">Set Name</th>
						<th class="font-dark-blue">{{$roster->jersey_set_name}}</th>
					</tr>
				@endif
					<tr>
						<th>Size</th>
						<th>Quantity</th>
					</tr>
				</thead>
				<tbody>
					@forelse($roster->tops as $quantity)
						<tr>
							<td>{{ $quantity->size }}</td>
							<td>{{ $quantity->quantity }}</td>
						</tr>
					@empty
						<tr>
							<td>No info.</td>
							<td></td>
						</tr>
					@endforelse
				</tbody>
				<tfoot>
					<tr>
						<td class="text_left">Total Quantity:</td>
						<td class="text_right">{{ $roster->quantitySumByType('top') }}</td>
					</tr>
				</tfoot>
			</table>
		</div>

		<div class="pagebreak"> </div>

		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">9. Shorts or Socks Quantities Per Set:</h4>
			<table class="table table-bordered border-balck narrow-first-col slim-td-padding">
				<thead>
				@if($roster->short_set_name)
					<tr>
						<th class="font-dark-blue">Set Name</th>
						<th class="font-dark-blue">{{$roster->short_set_name}}</th>
					</tr>
				@endif
					<tr>
						<th>Size</th>
						<th>Quantity</th>
					</tr>
				</thead>
				<tbody>
					@forelse($roster->shorts as $quantity)
						<tr>
							<td>{{ $quantity->size }}</td>
							<td>{{ $quantity->quantity }}</td>
						</tr>
					@empty
						<tr>
							<td>No info.</td>
							<td></td>
						</tr>
					@endforelse
				</tbody>
				<tfoot>
					<tr>
						<td class="text_left">Total Quantity:</td>
						<td class="text_right">{{ $roster->quantitySumByType('short') }}</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="col-md-6 offset-md-3 mb-20">
			<h4 class="font-weight-bold font-red font-18">{!! $roster->settings['section_9']['title'] !!}:</h4>
			<table class="table table-bordered border-balck slim-td-padding font-16">
					<tr>
						<th>Row</th>
						<th>Size</th>
						<th>Number</th>
						<th>{!! $roster->settings['section_9']['table_head']['head_4'] !!}</th>
						<th>{!! $roster->settings['section_9']['table_head']['head_5'] !!}</th>
						<th>Short</th>
					</tr>
				<tbody>
					@forelse($roster->teams as $team)
						<tr data-style="background-color: {{ $team->rowcolor }} !important;">
							<td style="background-color: {{ $team->rowcolor }} !important;">{{ $loop->iteration }}.</td>
							<td style="background-color: {{ $team->rowcolor }} !important;">{{ $team->size }} </td>
							<td style="background-color: {{ $team->rowcolor }} !important;">{{ $team->number }}</td>
							<td style="background-color: {{ $team->rowcolor }} !important;">{{ $team->name }}</td>
							<td style="background-color: {{ $team->rowcolor }} !important;">{{ $team->note }}</td>
							<td style="background-color: {{ $team->rowcolor }} !important;">{{ $team->shortsize }}</td>
						</tr>
					@empty
					<tr>
						<td colspan="4"> No info.</td>
					</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection
