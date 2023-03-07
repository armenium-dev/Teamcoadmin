<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<style type="text/css">
		body{ background-color: #fff !important; }
		table {width: auto; border-collapse: collapse;border-spacing: 0; max-width: 800px;}
		table td {border: 1px solid #c0c0c0; text-align: left; font: normal 12px Arial, sans-serif; color: #000; padding: 5px 15px;}
		table thead td {font-weight: bold;}
		table td.text_left {text-align: left;}
		table td.text_right {text-align: right;}
		table td.text_center {text-align: center;}
		table td.text_bold {font-weight: bold;}
		h1 {font: bold 17px Arial, sans-serif;color: #4e4e4e; text-align: left;}
		h2 {color: #a33c4a; font: bold 14px Arial, sans-serif;padding: 5px 0; text-align: left; text-transform: uppercase;}
		.container {width:990px;color: #4e4e4e;}
		.clear {clear: both;}
		.roster_attach {font: bold 16px Arial, sans-serif;font-style: italic;color: #a33c4a;text-align:left;}
		.font-dark-blue {color: #22228c;}
		.bold {font-weight: bold;}
	</style>
</head>
<body>
<div class="container">
	<!--<div><h1>TEAMCO<br>INCOMING ROSTER FORM - {{ $data['roster']->client->company }}<br>Roster Form - Sublimation</h1></div>-->
	<div class="ml-10">
		<h1>ROSTER FORM # {{$data['roster']->id}} - {{ $data['roster']->client->name }} - {{$data['roster']->client->company}}</h1>
	</div>
	<div class="clear"></div>
	<div class="roster_attach"></div>
	<div class="clear"></div>
	<div class="ml-10">
		<div class="mt-0">
			<h2>1. (a) Contact and Shipping Information</h2>
			<div>
				<table>
					<tr>
						<td class="text_bold">TEAMCO Reference Number:</td>
						<td>{{ $data['roster']->reference }}</td>
					</tr>
					<tr>
						<td class="text_bold">Name:</td>
						<td>{{ $data['roster']->client->name }}</td>
					</tr>
					<tr>
						<td class="text_bold">Organization:</td>
						<td>{{ $data['roster']->client->company }}</td>
					</tr>
					<tr>
						<td class="text_bold">Address 1:</td>
						<td>{{ $data['roster']->client->address }}</td>
					</tr>
					<tr>
						<td class="text_bold">Address 2:</td>
						<td>{{ $data['roster']->client->address_2 }}</td>
					</tr>
					<tr>
						<td class="text_bold">City:</td>
						<td>{{ $data['roster']->client->city }}</td>
					</tr>
					<tr>
						<td class="text_bold">Prov/State:</td>
						<td>{{ $data['roster']->client->state }}</td>
					</tr>
					<tr>
						<td class="text_bold">Postal Code:</td>
						<td>{{ $data['roster']->client->zip }}</td>
					</tr>
					<tr>
						<td class="text_bold">Country:</td>
						<td>{{ $data['roster']->client->country }}</td>
					</tr>
					<tr>
						<td class="text_bold">Email:</td>
						<td>{{ $data['roster']->client->email }}</td>
					</tr>
					<tr>
						<td class="text_bold">Phone:</td>
						<td>{{ $data['roster']->client->phone }}</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="clear"></div>
		<div class="mt-20">
			<h2>1. (b) Billing Information</h2>
			<div>
				<table>
					<tr>
						<td class="text_bold">Name:</td>
						<td>{{ $data['billing']->name }}</td>
					</tr>
					<tr>
						<td class="text_bold">Organization:</td>
						<td>{{ $data['billing']->company }}</td>
					</tr>
					<tr>
						<td class="text_bold">Address 1:</td>
						<td>{{ $data['billing']->address }}</td>
					</tr>
					<tr>
						<td class="text_bold">Address 2:</td>
						<td>{{ $data['billing']->address_2 }}</td>
					</tr>
					<tr>
						<td class="text_bold">City:</td>
						<td>{{ $data['billing']->city }}</td>
					</tr>
					<tr>
						<td class="text_bold">Prov/State:</td>
						<td>{{ $data['billing']->state }}</td>
					</tr>
					<tr>
						<td class="text_bold">Postal Code:</td>
						<td>{{ $data['billing']->zip }}</td>
					</tr>
					<tr>
						<td class="text_bold">Country:</td>
						<td>{{ $data['billing']->country }}</td>
					</tr>
					<tr>
						<td class="text_bold">Email:</td>
						<td>{{ $data['billing']->email }}</td>
					</tr>
					<tr>
						<td class="text_bold">Phone:</td>
						<td>{{ $data['billing']->phone }}</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="clear"></div>
        <div class="mt-20">
	        <h2>2. Shipping Method</h2>
	        <div>
		        {!! $data['roster']->shipping_method ? $data['roster']->shipping_method : 'No Preference - Teamco will choose' !!}
	        </div>
	    </div>
        <div class="clear"></div>
        <div class="mt-20">
	        <h2>3. Jersey Details</h2>
		    <div>
			    <table>
				    <tr>
					    <td class="text_bold">Style Code:</td>
					    <td>{{  $data['roster']->jersey->style_code }}</td>
				    </tr>
				    @if(isset($data['jersey_detail']))
						@forelse($data['jersey_detail'] as $detail)
						<tr>
							<td class="text_bold">Color {{ $loop->iteration }}:</td>
							<td>{{ $detail }}</td>
						</tr>
						@empty
						@endforelse
					@else
					@endif
			    </table>
		    </div>
	    </div>
	    <div class="clear"></div>
		<div class="mt-20">
			<h2 class="mt-5">4. Accessory Items</h2>
			<div>{{$data['roster']->accessory_items}}</div>
		</div>
	    <div class="clear"></div>
	    <div class="mt-20">
	        <h2>5. Numbers Colors</h2>
		    <div>
			    <table>
				    <tr>
					    <td class="text_bold">Number Colors:</td>
					    <td>{{ $data['roster']->number_color }}</td>
				    </tr>
				    <!--
				    <tr>
					    <td class="text_bold">Inside Color:</td>
					    <td>{{ $data['roster']->inside_color }}</td>
				    </tr>
				    <tr>
					    <td class="text_bold">Outside Color:</td>
					    <td>{{ $data['roster']->outside_color }}</td>
				    </tr>
				    -->
			    </table>
		    </div>
	    </div>
	    <div class="clear"></div>
	    <div class="mt-20">
	        <h2 class="mt-5">6. Artwork Placement and Comments</h2>
		    <div>
			    {!! $data['roster']->comments ? nl2br($data['roster']->comments) : 'No comments' !!}
			</div>
	    </div>
	    <div class="clear"></div>
	    <div class="mt-20">
	        <h2>8. Jersey Quantities Per Set</h2>
		    <div>
			    <table>
					@if($data['roster']->jersey_set_name)
						<tr>
							<td class="font-dark-blue bold">Set Name</td>
							<td class="font-dark-blue bold">{{$data['roster']->jersey_set_name}}</td>
						</tr>
					@endif
			    	@forelse($data['roster']->quantities as $quantity)
					    @if($quantity->type == "top")
						    <tr>
							    <td class="text_left">{{$quantity->size}}</td>
							    <td class="text_right">{{ $quantity->quantity }}</td>
						    </tr>
					    @endif
					@empty
					
					@endforelse
					<tr>
						<td class="text_left">Total Quantity:</td>
				    	<td class="text_right">{{$data['roster']->top_quantity}}</td>
					</tr>
			    </table>
		    </div>
	    </div>
		<div class="clear"></div>
		<div class="mt-20">
			<h2>9. Shorts or Socks Quantities Per Set</h2>
			<div>
				<table>
					@if($data['roster']->short_set_name)
						<tr>
							<td class="font-dark-blue bold">Set Name</td>
							<td class="font-dark-blue bold">{{$data['roster']->short_set_name}}</td>
						</tr>
					@endif
					@forelse($data['roster']->quantities as $quantity)
						@if($quantity->type == "short")
							<tr>
								<td class="text_left">{{$quantity->size}}</td>
								<td class="text_right">{{ $quantity->quantity }}</td>
							</tr>
						@endif
					@empty

					@endforelse
					<tr>
						<td class="text_left">Total Quantity:</td>
						<td class="text_right">{{$data['roster']->short_quantity}}</td>
					</tr>
				</table>
			</div>
		</div>
	    <div class="clear"></div>
		<div class="mt-20">
			<h2>{!! $data['roster']->settings['section_9']['title'] ?? '9. Team Roster'!!}</h2>
			<table>
				<thead>
				<tr>
					<td class="text_left">-</td>
					<td class="text_center">Size</td>
					<td class="text_center">Number</td>
					<td class="text_center">{!! $data['roster']->settings['section_9']['table_head']['head_4'] ?? 'Name' !!}</td>
					<td class="text_center">{!! $data['roster']->settings['section_9']['table_head']['head_5'] ?? 'Notes' !!}</td>
					<td class="text_center">Short</td>
				</tr>
				</thead>
				<tbody>
				@forelse($data['roster']->teams as $team)
					<tr style="background-color: {{ $team->rowcolor }} !important;">
						<td class="text_left" style="background-color: {{ $team->rowcolor }} !important;">{{$loop->iteration}}.</td>
						<td class="text_center" style="background-color: {{ $team->rowcolor }} !important;">{{ $team->size }}</td>
						<td class="text_center" style="background-color: {{ $team->rowcolor }} !important;"> {{ $team->number }}</td>
						<td class="text_center" style="background-color: {{ $team->rowcolor }} !important;">{{ $team->name }}</td>
						<td class="text_center" style="background-color: {{ $team->rowcolor }} !important;">{{ $team->note }}</td>
						<td class="text_center" style="background-color: {{ $team->rowcolor }} !important;">{{ $team->shortsize }}</td>
					</tr>
				@empty
				@endforelse
				</tbody>
			</table>
		</div>
    </div>
</div>	
</body>
</html>
