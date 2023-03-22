@extends('layouts.app',['title' => 'Manage Quotes'])
@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css"/>
{{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
<div class="card-body">
	@if (session('status'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		{{ session('status') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
		</button>
	</div>
	@endif
	<h4 class="float-title">Mail Logs <small>({!! $logs_count !!})</small></h4>
	<table class="table table-striped text-center" id="table">
		<thead class="thead-dark">
			<tr>
				<th>Log ID</th>
				<th>Body</th>
				<th>Sent</th>
				<th>Controller</th>
				<th>Obj ID</th>
				<th>Job ID</th>
				<th>Updated</th>
				<th data-sortable="false">Check</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#table').DataTable({
			"order": [[0, "desc"]],
			"pageLength": 10,
			"lengthMenu": [[10, 25, 50, 75, 100, 200, -1], [10, 25, 50, 75, 100, 200, "All"]],
			"processing": true,
			"serverSide": true,
			"ajax": "/logger/parts"
		});

		$(document).on('click', '.js_check_status', function(e){
			const $target = $(this);
			let action = $target.data('action'),
				reference_id = $target.data('reference_id');


		});
	});
</script>
@endsection
