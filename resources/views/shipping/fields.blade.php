
<div class="row mb-3 align-items-center d-none">
	<div class="col-12"><input type="text" class="form-control" name="country_code" placeholder="Country (code)*" value="CA"></div>
</div>
<div class="row mb-3 align-items-center">
	<div class="col-12">
		@if(!empty($ship_engine_province_codes))
			<select class="form-control" name="state_province">
				<option value="">Province</option>
				@foreach($ship_engine_province_codes as $code)
					<option value="{!! $code !!}">{!! $code !!}</option>
				@endforeach
			</select>
		@else
			<input type="text" class="form-control" name="state_province" placeholder="Province (Abbreviation)*">
		@endif
	</div>
</div>
<div class="row mb-3 align-items-center">
	<div class="col-12"><input type="text" class="form-control" name="postal_code" placeholder="Postal Code*"></div>
</div>
@if(!empty($ship_engine_jersey_type_options))
	@php $_key = 0 @endphp
	<div class="row mb-3 align-items-center">
		<div class="col-12">
			<div class="radio-label mb-3">Jersey Type*:</div>
			@foreach($ship_engine_jersey_type_options as $k => $v)
				@if($v['status'] == 1)
					@php $_key++ @endphp
					<label class="custom-radio">
						<input type="radio" name="jersey_type" @if($_key == 1) checked="checked" @endif value="{{$k}}"> {{$v['title']}}
					</label>
				@endif
			@endforeach
		</div>
	</div>
@endif
<div class="row mb-3 align-items-center">
	<div class="col-12">
		<input id="js_units" type="number" class="form-control" name="units" min="1" max="120" value="" placeholder="Total # of Units in Order (max 60)*">
		<div class="input-desc">Please include the total number of jerseys, shorts, and/or socks that would be in the order. [E.g. an order for 20 soccer jerseys and 20 soccer shorts would have a total quantity of 40]</div>
	</div>
</div>
