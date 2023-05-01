@extends('layouts.app',['title' => 'Manage Roster'])
@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css"/>
{{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
<div class="card-body">
	<div class="row">
		<div class="col-md-12 text-center"><h4>Show all Roster:</h4></div>
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
	<table class="table table-striped" id="table">
		<thead class="thead-dark">
			<tr>
				<th>Reference #</th>
				<th>Name</th>
				<th>Organization</th>
				<th>Date Submitted</th>
				<th data-sortable="false">Total Jerseys</th>
				<th data-sortable="false">Total Shorts</th>
				<th data-sortable="false">View</th>
				<th data-sortable="false">Edit</th>
				<th data-sortable="false">Delete</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
<div id="myModal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-dark text-center">
				<h4 class="modal-title text-light">You want delete this Roster?</h4>
				<button type="button" class="close text-light" data-dismiss="modal">&times;</button>
			</div>
			<form action="#" method="POST">
				<div class="modal-body">
					<h5 id="reference_id" class="text-center mt-3 mb-4">Reference # <span></span></h5>
					@csrf
					@method('DELETE')
				</div>
				<div class="modal-footer">
					<div class="row">
						<div class="col-md-6 text-center"><button type="submit" class="btn btn-danger">Delete</button></div>
						<div class="col-md-6 text-center"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#table').DataTable({
			"order": [[0, "desc"]],
			"pageLength": 25,
			"lengthMenu": [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, "All"]],
			"processing": true,
			"serverSide": true,
			"ajax": "/roster/parts",
			"columnDefs": [
				{ targets: [0], className: "text-nowrap"},
			],
			"columns": [
				{className: "text-right"},
				null,
				null,
				null,
				{className: "text-right", order: false},
				{className: "text-right"},
				null,
				null,
				null,
			]
		});

		$(document).on('click', '.btn-remove', function(e){
			var action = $(this).data('action'),
				$target = $($(this).data('target')),
				reference_id = $(this).data('reference_id');
			$target
				.find('form').attr('action', action)
				.end()
				.find('#reference_id').find('span').text(reference_id);

		});
	});
</script>
@endsection
