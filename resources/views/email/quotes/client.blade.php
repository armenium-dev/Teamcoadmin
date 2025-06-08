<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>

		</title>
		<style>
		body{
			background-color: #fff !important;
			color:#000;
		}
		.color{
			margin:0;
			font: normal 12px Arial, sans-serif;
			color:#000;
		}
		.message{
			font: normal 12px Arial, sans-serif;
			color:#000;
		}
		table{
			font: normal 12px Arial, sans-serif;
			color:#000;
		}
		.clear {margin-bottom:10px;}
		</style>

	</head>
	<body>
		<div class="ml-10">
			<table>
				<tbody>
					<tr>
						<td colspan="2">
							<p class="message">Dear {{$data['quote']->client->name}},</p>
							<p class="message">Thank you for your inquiry. We will review it and contact you within 1-3 business days - depending on seasonal demand.</p>
							<p class="message">Thanks,<br>
							<span>Teamco Customer Service</span><br>
							<span>1-888-668-6443</span>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="contact-info">
				<tbody>
					<tr>
						<td colspan="2" class="group-header"><b>1. Contact Information:</b></td>
					</tr>
					<tr>
						<td>Reference #:</td>
						<td>{{$data['quote']->id}}</td>
					</tr>
					<tr>
						<td>Name:</td>
						<td>{{$data['quote']->client->name}}</td>
					</tr>
					<tr>
						<td>Company:</td>
						<td>{{$data['quote']->client->company}}</td>
					</tr>
					<tr>
						<td>Address 1:</td>
						<td>{{$data['quote']->client->address}}</td>
					</tr>
					<tr>
						<td>Address 2:</td>
						<td>{{$data['quote']->client->address_2}}</td>
					</tr>
					<tr>
						<td>City:</td>
						<td>{{$data['quote']->client->city}}</td>
					</tr>
					<tr>
						<td>Prov/State:</td>
						<td>{{$data['quote']->client->state}}</td>
					</tr>
					<tr>
						<td>Postal Code:</td>
						<td>{{$data['quote']->client->zip}}</td>
					</tr>
					<tr>
						<td>Country:</td>
						<td>{{$data['quote']->client->country}}</td>
					</tr>
					<tr>
						<td>Email:</td>
						<td>{{$data['quote']->client->email}}</td>
					</tr>
					<tr>
						<td>Phone:</td>
						<td>{{$data['quote']->client->phone}}</td>
					</tr>
					<tr>
						<td>Date required:</td>
						<td>{{$data['quote']->date_required}}</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="clear"></div>
		<div class="ml-10">
			<table>
					<tr>
						<td colspan="2" class="group-header"><b>2. Quote Details:</b></td>
					</tr>
				<tbody>
					@forelse($data['products'] as $product)
					<tr>
						<td colspan="2"> {{$loop->iteration}}. - {{$product['data']->title}}</td>
					</tr>
					<tr>
						<td colspan="2">
							@foreach($product['data']->colors as $color)
							<p class="color">
                                @if(isset($color->label))
                                    {{$color->label}}: {{$color->name}}
                                @else
                                    Color {{$loop->iteration}}: {{$color->name}}
                                @endif
                            </p>
							@endforeach
						</td>
					</tr>
					<tr>
						<td colspan="2">Quantity: <b>{{$product['quantity']}}</b></td>
					</tr>
					<tr>
						<td colspan="2"><hr></td>
					</tr>
					@empty
					<tr>
						<td colspan="2">
							<p>No products</p>
						</td>
					</tr>
					@endforelse
					<tr>
						<td colspan="2"><p class="font-weight-bold text-danger color"><b>Total Order Quantity: {{ $data['quote']->styles->sum('quantity') }}</b></p></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="clear"></div>
		<div class="ml-10">
			<table>
				<tbody>
					<tr>
						<td colspan="2" class="group-header"><b>3. Artwork Placement and Order Description:</b></td>
					</tr>
					<tr>
						<td>
							{!! $data['quote']->description ? nl2br($data['quote']->description) : 'No description' !!}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</body>
</html>
