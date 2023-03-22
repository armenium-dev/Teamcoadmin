@extends('layouts.app',['title' => 'Jersey Builder Logs'])
@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css"/>
{{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
<div class="card-body">
	<div class="row">
		<div class="col-md-12 text-center"><h4>View Logs:</h4></div>
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
		<table class="table table-striped text-left" id="table">
			<thead class="thead-dark">
				<tr>
					<th>Time</th>
					<th>Content</th>
				</tr>
			</thead>
			<tbody>
				@forelse($logs as $log)
				<tr>
					<td nowrap="nowrap">{{$log['time']}}</td>
					<td>{{$log['content']}}</td>
				</tr>
				@empty
				<tr>
					<td colspan="4">Nothing to show</td>
				</tr>
				@endforelse
			</tbody>
		</table>
		{{-- $Products->links() --}}
	</div>
</div>
@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#table').DataTable({
			"order": [[ 0, "asc" ]],
			"pageLength": 50,
			"lengthMenu": [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, "All"]]
		});
	});
</script>
@endsection