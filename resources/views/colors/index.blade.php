@extends('layouts.app',['title' => 'Manage Colours'])
@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.5/css/rowReorder.bootstrap4.min.css"/>
{{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
<div class="card-body">
	<div class="row">
		<div class="col-md-6 text-left"><h4>Manage Colours:</h4></div>
		<div class="col-md-6 text-right"><a href="{{route('color.create')}}" class="btn btn-primary"><i class="fa fa-plus" title="add color"></i> Add Colour</a></div>
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
		<table class="table table-striped text-center display" id="table" width="100%">
			<thead class="thead-dark">
				<tr>
					<th>Position</th>
					<th>Colour Name</th>
					<th>Hex Value</th>
					<th>Preview</th>
					<th>Move</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody >
				@forelse($Colors as $color)
				<tr id="{{$color->id}}">
					<td >{{$color->position}}</td>
					<td>{{$color->name}}</td>
					<td>{{$color->value_code}}</td>
					<td>
						<i class="fa fa-square" style="color:{{$color->value_code}};font-size: 30px;"></i>
					</td>
					<td class="newPointer"> <i class="fa fa-long-arrow-up"></i>  <i class="fa fa-long-arrow-down"></i></td>
					<td><a href="{{route('color.edit',$color->id)}}" class="btn btn-primary"><i class="fa fa-edit" title="Edit"></i></a></td>
					<td>
						<button class="btn btn-danger" data-toggle="modal" data-target="#myModal_{{$color->id}}" title="Delete"><i class="fa fa-trash"></i></button>
						<div id="myModal_{{$color->id}}" class="modal fade" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header bg-dark text-center">
										<h4 class="modal-title text-light">You want delete this Colour?</h4>
										<button type="button" class="close text-light" data-dismiss="modal">&times;</button>
									</div>
									<div class="modal-body">
										<form action="{{ route('color.destroy',$color->id) }}" method="POST">
											@csrf
											@method('DELETE')
											<div class="row">
												<div class="col-md-6 text-center"><button type="submit" class="btn btn-danger">Delete</button></div>
												<div class="col-md-6 text-center"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
											</div>
										</form>
									</div>
									<div class="modal-footer">
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="7">Nothing to show</td>
				</tr>
				@endforelse
			</tbody>
		</table>
		<div id="result">
		</div>
		{{--{{ $Colors->links() }}--}}
	</div>
</div>
@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="//cdn.datatables.net/rowreorder/1.2.5/js/dataTables.rowReorder.min.js"></script>
<script type="text/javascript">
	function savePosition(values) {
		$.ajax({
			headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
			url: "/color/order",
			type: 'POST',
			data: { position: values },
			success: function(result) {
				console.log(result);
			}
		});
	}
	jQuery(document).ready(function($) {
		var table = $('#table').DataTable({
			'info': false,
			//'lengthChange': false,
			"order": [[ 0, "asc" ]],
			"pageLength": 50,
			"lengthMenu": [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, "All"]],
			rowReorder: {selector: '.newPointer'}
		});
		table.on('row-reorder', function(e, diff, edit) {
			savePosition(edit.values);
		});
	});
</script>
@endsection
