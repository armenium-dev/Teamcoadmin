@extends('layouts.app',['title' => 'Edit Roster'])
@section('content')
	@php $last_key = 0 @endphp
<div class="card-body">
	<div class="row">
		<div class="col-md-6 text-left">
			<h4>Edit Roster:</h4>
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

	<form action="{{ route('roster.update',$roster->id) }}" method="POST" enctype="multipart/form-data">
		@csrf
		<input name="_method" type="hidden" value="PATCH">
		<input name="environment" type="hidden" value="live">
		<input name="roster[id]" type="hidden" value="{{$roster->id}}">
		<input name="client[id]" type="hidden" value="{{$roster->client->id}}">
		<input name="jersey_detail[id]" type="hidden" value="{{$roster->jersey->id}}">

		<div class="row mt-3 text-center">
			<div class="col-md-6 offset-md-3">
				<div class="my-4"><h4 class="font-weight-bold font-red font-18">1. (a) Contact and Shipping Information</h4></div>
				<div class="form-group"><input type="text" value="{{$roster->reference}}" name="roster[reference]" placeholder="Web Inquiry # or PO# (enter N/A if not applicable)*" required="required" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->name}}" name="client[name]" placeholder="Name*" required="required" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->company}}" name="client[company]" placeholder="Company / Organization" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->address}}" name="client[address]" id="autocomplete" placeholder="Address*" required="required" class="form-control pac-target-input" autocomplete="off"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->address_2}}" name="client[address_2]" placeholder="Address 2" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->city}}" name="client[city]" placeholder="City*" required="required" class="form-control"></div>
				<div class="form-group">
					<select name="client[state]" required="required" class="form-control">
						<option value="">Prov / State*</option>
						@foreach($states as $key => $states_group)
							<optgroup label="{{$states_group['name']}}">
								@foreach($states_group['states'] as $state)
									@php $selected = ($state->state_code == $roster->client->state) ? 'selected=selected' : '' @endphp
									<option value="{{$state->state_code}}" {{$selected}}>{{$state->name}}</option>
								@endforeach
							</optgroup>
						@endforeach
					</select>
				</div>
				<div class="form-group"><input type="text" value="{{$roster->client->zip}}" name="client[zip]" placeholder="Postal Code*" required="required" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->country}}" name="client[country]" placeholder="Country*" required="required" class="form-control"></div>
				<div class="form-group"><input type="email" value="{{$roster->client->email}}" name="client[email]" placeholder="Email*" required="required" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->phone}}" name="client[phone]" placeholder="Phone" class="form-control"></div>

				<div class="my-4"><h4 class="font-weight-bold font-red font-18">1. (b) Billing Information</h4></div>
				<div class="form-group"><input type="text" value="{{$roster->client->billing->name}}" name="billing[name]" placeholder="Name*" required="required" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->billing->company}}" name="billing[company]" placeholder="Company / Organization" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->billing->address}}" name="billing[address]" id="billing_autocomplete" placeholder="Address*" required="required" class="form-control pac-target-input" autocomplete="off"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->billing->address_2}}" name="billing[address_2]" placeholder="Address 2" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->billing->city}}" name="billing[city]" placeholder="City*" required="required" class="form-control"></div>
				<div class="form-group">
					<select name="billing[state]" required="required" class="form-control">
						<option value="">Prov / State*</option>
						@foreach($states as $key => $states_group)
							<optgroup label="{{$states_group['name']}}">
								@foreach($states_group['states'] as $state)
									@php $selected = ($state->state_code == $roster->client->billing->state) ? 'selected=selected' : '' @endphp
									<option value="{{$state->state_code}}" {{$selected}}>{{$state->name}}</option>
								@endforeach
							</optgroup>
						@endforeach
					</select>
				</div>
				<div class="form-group"><input type="text" value="{{$roster->client->billing->zip}}" name="billing[zip]" placeholder="Postal Code*" required="required" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->billing->country}}" name="billing[country]" placeholder="Country*" required="required" class="form-control"></div>
				<div class="form-group"><input type="email" value="{{$roster->client->billing->email}}" name="billing[email]" placeholder="Email*" required="required" class="form-control"></div>
				<div class="form-group"><input type="text" value="{{$roster->client->billing->phone}}" name="billing[phone]" placeholder="Phone" class="form-control"></div>

				<div class="my-4"><h4 class="font-weight-bold font-red font-18">2. Shipping Method</h4></div>
				<p class="text-center"><em class="itl">Please list any other items that are also part of your order (e.g. matching shorts, hockey socks, etc.)</em></p>
				<div class="form-group">
					<select name="roster[shipping_method]" class="form-control select-form-control">
						@foreach($shipping_services as $s => $c)
							@php $selected = ($c['name'] == $roster->shipping_method) ? 'selected=selected' : '' @endphp
							<option value="{{$c['name']}}" {{$selected}}>{{$c['name']}}</option>
						@endforeach
					</select>
				</div>

				<div class="my-4"><h4 class="font-weight-bold font-red font-18">3. Jersey Details</h4></div>
				<div class="form-group"><input type="text" value="{{$roster->jersey->style_code}}" name="jersey_detail[style_code]" placeholder="Style Code*" required="required" class="form-control"></div>
                @foreach($jersey_detail as $k => $jdetail)
				    <div class="form-group"><input type="text" value="{{$jdetail}}" name="jersey_detail[colors][{{$k}}]" placeholder="Color {{$k}}" class="form-control"></div>
                @endforeach

				<div class="my-4"><h4 class="font-weight-bold font-red font-18">4. Accessory Items</h4></div>
				<p class="text-center"><em class="itl">Please list any other items that are also part of your order (e.g. matching shorts, hockey socks, etc.)</em></p>
				<div class="form-group"><input value="{{$roster->accessory_items}}" name="roster[accessory_items]" type="text" placeholder="Accessory Items" class="form-control"></div>

				<p class="my-4"><h4 class="font-weight-bold font-red font-18">5. Number Colors</h4></p>
				<div class="form-group"><input type="text" value="{{$roster->number_color}}" name="roster[number_color]" placeholder="Number Colors" class="form-control"></div>

				<div class="my-4"><h4 class="font-weight-bold font-red font-18">6. Artwork Placement and Order Description</h4></div>
				<p>Tell us the artwork that you would like on the jerseys, where it should be placed and any other relevant information.*</p>
				<div class="form-group">
					<label class="artwork-link"><em><a href="https://cdn.shopify.com/s/files/1/0040/0791/9729/files/Teamco_Artwork_Placement_Guide.png?8233878047739465168" target="_blank">(View our artwork placement guide)</a></em></label>
					<textarea name="roster[comments]" cols="20" rows="5" required="required" placeholder="Please describe the artwork you would like on the jerseys, where it should be placed and any other relevant information. Please also include the correct spelling of your Team Name (if applicable)" class="form-control text-area">{{$roster->comments}}</textarea>
				</div>

                <div class="my-4"><h4 class="font-weight-bold font-red font-18">7. Jersey Quantities</h4></div>
                <div class="form-group">
                    <table class="table table-bordered custom-table mb-3">
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($roster->tops as $size => $qty)
                            <tr>
                                <td>{{$size}}</td>
                                <td><input value="{{$qty}}" name="quantity[top][{{$size}}]" type="text" class="form-control"></td>
                            </tr>
                        @endforeach
                        <tr>
                            <td>Total</td>
                            <td><input type="text" id="js_total_qty_top" value="{{$roster->quantitySumByType('top')}}" class="form-control" readonly></td>
                        </tr>
                        </tbody>
                    </table>
                </div>

				<div class="my-4"><h4 class="font-weight-bold font-red font-18">8. Shorts or Socks Quantities</h4></div>
                <div class="form-group">
                    <table class="table table-bordered custom-table mb-3">
                        <thead>
	                        <tr>
	                            <th>Size</th>
	                            <th>Quantity</th>
	                        </tr>
                        </thead>
                        <tbody>
                        @foreach($roster->shorts as $size => $qty)
	                        <tr>
		                        <td>{{$size}}</td>
		                        <td><input value="{{$qty}}" name="quantity[short][{{$size}}]" type="text" class="form-control"></td>
	                        </tr>
                        @endforeach
                        <tr>
                            <td>Total</td>
                            <td><input type="text" id="js_total_qty_short" value="{{$roster->quantitySumByType('short')}}" class="form-control" readonly></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
			</div>

			<div class="col-md-8 offset-md-2">
				<div class="my-4">
					<h4 class="font-weight-bold font-red font-18">{!! $roster->settings['section_9']['title'] !!} <a role="button" class="title-edit-btn js_edit_title"><i class="fa fa-edit"></i></a></h4>
					<input value="{!! $roster->settings['section_9']['title'] !!}" name="roster[settings][section_9][title]" type="text" placeholder="9. Team Roster" class="form-control hidden">
				</div>
				<div class="text-center team-roster">
					<p>Please note: Names will be printed exactly as entered (Names are usually printed in UPPERCASE). For teams over 30 players, please use our <a href="https://cdn.shopify.com/s/files/1/0040/0791/9729/files/TEAMCO_Roster_Form.xlsx?16321">Excel Roster Form</a></p>
					<p class="color3 font-weight-bold">**We highly recommend consulting the Size Chart, before choosing your sizes. Please contact us if you would like us to send you the applicable size chart for your jerseys.**</p>
					<p class="color2">Note - For hockey goalie jerseys please let us know the size in the Notes column.</p>
				</div>
				<table class="table table-bordered custom-table-2 mb-3">
					<thead>
						<tr>
							<th width="30">
								<span>{!! $roster->settings['section_9']['table_head']['head_1'] !!} <a role="button" class="title-edit-btn js_edit_title hidden"><i class="fa fa-edit"></i></a></span>
								<input value="{!! $roster->settings['section_9']['table_head']['head_1'] !!}" name="roster[settings][section_9][table_head][head_1]" type="text" placeholder="-" class="form-control hidden">
							</th>
							<th width="*">
								<span>{!! $roster->settings['section_9']['table_head']['head_2'] !!} <a role="button" class="title-edit-btn js_edit_title hidden"><i class="fa fa-edit"></i></a></span>
								<input value="{!! $roster->settings['section_9']['table_head']['head_2'] !!}" name="roster[settings][section_9][table_head][head_2]" type="text" placeholder="Jersey Size" class="form-control hidden">
							</th>
							<th class="text-nowrap" width="*">
								<span>{!! $roster->settings['section_9']['table_head']['head_3'] !!} <a role="button" class="title-edit-btn js_edit_title hidden"><i class="fa fa-edit"></i></a></span>
								<input value="{!! $roster->settings['section_9']['table_head']['head_3'] !!}" name="roster[settings][section_9][table_head][head_3]" type="text" placeholder="Jersey #" class="form-control hidden">
							</th>
							<th width="50%">
								<span>{!! $roster->settings['section_9']['table_head']['head_4'] !!} <a role="button" class="title-edit-btn js_edit_title"><i class="fa fa-edit"></i></a></span>
								<input value="{!! $roster->settings['section_9']['table_head']['head_4'] !!}" name="roster[settings][section_9][table_head][head_4]" type="text" placeholder="Jersey Name" class="form-control hidden">
							</th>
							<th width="50%">
								<span>{!! $roster->settings['section_9']['table_head']['head_5'] !!} <a role="button" class="title-edit-btn js_edit_title"><i class="fa fa-edit"></i></a></span>
								<input value="{!! $roster->settings['section_9']['table_head']['head_5'] !!}" name="roster[settings][section_9][table_head][head_5]" type="text" placeholder="Notes" class="form-control hidden">
							</th>
							<th width="*">
								<span>{!! $roster->settings['section_9']['table_head']['head_6'] !!} <a role="button" class="title-edit-btn js_edit_title hidden"><i class="fa fa-edit"></i></a></span>
								<input value="{!! $roster->settings['section_9']['table_head']['head_6'] !!}" name="roster[settings][section_9][table_head][head_6]" type="text" placeholder="Shorts Size" class="form-control hidden">
							</th>
						</tr>
					</thead>
					<tbody>
						@foreach($roster->teams as $k => $team)
							@php $last_key = $k+1 @endphp
						<tr>
							<td>{{$k+1}}.</td>
							<td>
								<select name="team[{{$team->id}}][size]" class="form-control select-form-control">
									<option value="false">--</option>
									@foreach($colors_sizes as $s => $c)
										@php $selected = ($team->size == $s) ? 'selected=selected' : '' @endphp
										<option value="{{$s}}" {{$selected}}>{{$s}}</option>
									@endforeach
								</select>
							</td>
							<td><input type="text" value="{{$team->number}}" name="team[{{$team->id}}][number]" class="form-control"></td>
							<td><input type="text" value="{{$team->name}}" name="team[{{$team->id}}][name]" class="form-control"></td>
							<td><input type="text" value="{{$team->note}}" name="team[{{$team->id}}][note]" class="form-control"></td>
							<td>
								<select name="team[{{$team->id}}][shortsize]" class="form-control select-form-control">
									<option value="false">--</option>
									@foreach($colors_sizes as $s => $c)
										@php $selected = ($team->shortsize == $s) ? 'selected=selected' : '' @endphp
										<option value="{{$s}}" {{$selected}}>{{$s}}</option>
									@endforeach
								</select>
							</td>
						</tr>
						@endforeach
						@for($k = 1; $k <= $teams_empty_rows; $k++)
						<tr>
							<td>{{$k+$last_key}}.</td>
							<td>
								<select name="team[new-{{$k}}][size]" class="form-control select-form-control">
									<option value="false">--</option>
									@foreach($colors_sizes as $s => $c)
										<option value="{{$s}}">{{$s}}</option>
									@endforeach
								</select>
							</td>
							<td><input type="text" value="" name="team[new-{{$k}}][number]" class="form-control"></td>
							<td><input type="text" value="" name="team[new-{{$k}}][name]" class="form-control"></td>
							<td><input type="text" value="" name="team[new-{{$k}}][note]" class="form-control"></td>
							<td>
								<select name="team[new-{{$k}}][shortsize]" class="form-control select-form-control">
									<option value="false">--</option>
									@foreach($colors_sizes as $s => $c)
										<option value="{{$s}}">{{$s}}</option>
									@endforeach
								</select>
							</td>
						</tr>
						@endfor
					</tbody>
				</table>

				<div class="mt-3">
					<div class="my-4"><h4 class="font-weight-bold font-red font-18">10. Attach Logo(s)</h4></div>
					<div class="my-2 d-flex flex-row flex-wrap justify-content-center align-items-end uploaded-files">
						<div class="text-center w-100 mb-2">Uploaded files:<br><em><small>Select files for remove</small></em></div>
						@forelse($roster->files as $file)
							<label class="img-item">
								<img src="{{$file->url}}">
								<input type="checkbox" name="remove_file_roster[]" value="{{$file->id}}">
							</label>
						@empty
							<div class="text-center"><em>No files.</em></div>
						@endforelse
					</div>
					<p class="font-weight-bold">Please attach your logos. For non-vector logos (e.g. PNG, JPEG, etc.) conversion charges may apply.</p>
					<!--<button type="button" class="btn btn-secondary">Choose Files</button>-->
					<input type="file" name="files[]" multiple>
				</div>
				<div class="mt-5">
					<div class="form-group text-center">
						<div class="my-4"><h4 class="font-weight-bold font-red font-18">11. Resend email to:</h4></div>
						<label class="ml-3 mr-3"><input type="checkbox" name="send_email[]" value="admin"> Admin</label>
						<label class="ml-3 mr-3"><input type="checkbox" name="send_email[]" value="client"> Client</label>
					</div>
				</div>
				<div class="mt-5">
					<div class="form-group text-center">
						<button type="submit" class="btn btn-primary-custom btn-lg btn-block btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</div>

	</form>
	<script type="text/javascript">
		var onPlaceChanged = function(){
			var my_place = {};
			var place = autocomplete.getPlace();
			if(!place.geometry){
				window.alert("No details available for input: '" + place.name + "'");
				return;
			}
			//console.log(place);
			var formated_place = formatResult(place);
			//console.log(formated_place);

			my_place.city = formated_place.city;
			my_place.state = formated_place.state;
			my_place.zip = formated_place.zip;
			my_place.country = formated_place.country;
			my_place.address = place.name;

			fillFields(my_place, 'client');
		};
		var onPlaceChangedBilling = function(){
			var my_place = {};
			var place = bill_autocomplete.getPlace();
			if(!place.geometry){
				window.alert("No details available for input: '" + place.name + "'");
				return;
			}
			//console.log(place);
			var formated_place = formatResult(place);
			//console.log(formated_place);

			my_place.city = formated_place.city;
			my_place.state = formated_place.state;
			my_place.zip = formated_place.zip;
			my_place.country = formated_place.country;
			my_place.address = place.name;

			fillFields(my_place, 'billing');
		};
		var formatResult = function(addressComponent){
			var address = {};
			address.formatted = addressComponent.formatted_address;
			address.latlng = addressComponent.geometry.location;
			addressComponent.address_components.forEach(function(component){
				if(component.types.indexOf("street_number") > -1){
					address.number = component.long_name;
				}
				if(component.types.indexOf("route") > -1){
					address.address = component.long_name;
				}
				if(component.types.indexOf("locality") > -1){
					address.city = component.long_name;
				}
				if(component.types.indexOf("administrative_area_level_2") > -1){
					address.department = component.long_name;
				}
				if(component.types.indexOf("administrative_area_level_1") > -1){
					address.state = component.short_name;
				}
				if(component.types.indexOf("country") > -1){
					address.country = component.long_name;
				}
				if(component.types.indexOf("postal_code") > -1){
					address.zip = component.long_name;
				}
			});

			return address;
		};
		var fillFields = function(my_place, type){
			$('input[name="'+type+'[address]"]').val(my_place.address);
			$('input[name="'+type+'[city]"]').val(my_place.city);
			$('input[name="'+type+'[zip]"]').val(my_place.zip);
			$('input[name="'+type+'[country]"]').val(my_place.country);
			$('select[name="'+type+'[state]"]').find('option').attr('selected', false).end().val(my_place.state);
		};

		const center = { lat: 50.064192, lng: -130.605469 };
		const defaultBounds = {north: center.lat + 0.1, south: center.lat - 0.1, east: center.lng + 0.1, west: center.lng - 0.1};
		const input = document.getElementById("autocomplete");
		const bill_input = document.getElementById("billing_autocomplete");
		const options = {
			bounds: defaultBounds,
			componentRestrictions: { country: "ca" },
			fields: ["address_components", "geometry", "icon", "name"],
			strictBounds: false,
			types: ["establishment"],
		};
		const autocomplete = new google.maps.places.Autocomplete(input, options);
		autocomplete.addListener("place_changed", onPlaceChanged);
		const bill_autocomplete = new google.maps.places.Autocomplete(bill_input, options);
		bill_autocomplete.addListener("place_changed", onPlaceChangedBilling);

		jQuery(document).ready(function($){

			var setTotalQty = function(){
				var total = 0;
				$('input[name^="quantity[top]"]').each(function(i, el){
					var v = ~~$(el).val();
					if(v == '') v = 0;
					total += v;
				});
				$('#js_total_qty_top').val(total);

				total = 0;
				$('input[name^="quantity[short]"]').each(function(i, el){
					var v = ~~$(el).val();
					if(v == '') v = 0;
					total += v;
				});
				$('#js_total_qty_short').val(total);

			};

			var editTitle = function(){
				var $parent = $(this).parent(),
					$input = $parent.next('input');

				$input.removeClass('hidden');
				$parent.addClass('hidden');
			};

			$(document)
				.on('keyup', 'input[name^="quantity"]', setTotalQty)
				.on('click', '.js_edit_title', editTitle);
		});

	</script>
</div>
@endsection
