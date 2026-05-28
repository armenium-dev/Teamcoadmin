@extends('layouts.app',['title' => 'Garment Types'])
@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.5/css/rowReorder.bootstrap4.min.css"/>
{{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
<div class="card-body">
	<div class="row">
		<div class="col-md-6 text-left">
			<h4>Manage Garment Types:</h4>
		</div>
        <div class="col-md-6 text-right">
            <a href="{{route('garment.create')}}" class="btn btn-primary"><i class="fa fa-plus" title="add color"></i> Add New Garment Type</a>
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
    <table id="table" class="table table-striped text-center display">
        <thead class="thead-dark">
        <tr>
            <th>Position</th>
            <th>Garment Code</th>
            <th>Title</th>
            <th>Description</th>
            <th>Move</th>
            <th>View</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody>
            @forelse($garments as $garment)
				<tr id="{{$garment->id}}">
					<td>{{$garment->position}}</td>
					<td>{{$garment->code}}</td>
					<td>{{$garment->title}}</td>
					<td>{{$garment->description}}</td>
					<td class="newPointer"> <i class="fa fa-long-arrow-up"></i>  <i class="fa fa-long-arrow-down"></i></td>
					<td><a href="{{route('garment.show', $garment->id)}}" class="btn btn-primary"><i class="fa fa-edit" title="Edit"></i></a></td>
					<td><a href="{{route('garment.edit', $garment->id)}}" class="btn btn-primary"><i class="fa fa-edit" title="Edit"></i></a></td>
					<td>
						<button class="btn btn-danger btn-remove"
                                data-garment-id="{{$garment->id}}"
                                data-garment-name="{{$garment->title}}"
                                data-action="{{route('garment.destroy', $garment->id)}}"
                                data-toggle="modal"
                                data-target="#myModal"
                                title="Delete"><i class="fa fa-trash"></i></button>
					</td>
				</tr>
            @empty
				<tr>
					<td colspan="8">Nothing to show</td>
				</tr>
            @endforelse
        </tbody>
    </table>
</div>
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-center">
                <h4 class="modal-title text-light">You want delete this Garment Type?</h4>
                <button type="button" class="close text-light" data-dismiss="modal">&times;</button>
            </div>
            <form action="#" method="POST">
                <div class="modal-body">
                    <h5 id="modal_content" class="text-center mt-3 mb-4"></h5>
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
<script type="text/javascript" src="//cdn.datatables.net/rowreorder/1.2.5/js/dataTables.rowReorder.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function($) {

        const table = $('#table').DataTable({
            "info": false,
            "order": [[0, "asc"]],
            "pageLength": 50,
            "lengthMenu": [[10, 20, 50, 75, 100, -1], [10, 20, 50, 75, 100, "All"]],
            //"processing": true,
            //"serverSide": true,
            //"ajax": "/garment/parts",
            /*"columnDefs": [
                { orderable: false, targets: [1], className: "text-nowrap"},
                { orderable: false, targets: '_all' }
            ],*/
            /*"columns": [
                {className: "text-center", order: false},
                {className: "text-center", order: false},
                {className: "text-left", order: false},
                {className: "text-left", order: false},
                {className: "text-center", order: false},
                {className: "text-center newPointer", order: false},
                {className: "text-center", order: false},
                {className: "text-center", order: false},
                {className: "text-center", order: false},
            ],*/
            "rowReorder": {
                selector: ".newPointer",
                //dataSrc: "data-id"
            },
            /*"createdRow": function(row, data, dataIndex) {
                // Добавляем data-id к строке
                $(row).attr('data-id', data[0]); // data[0] содержит ID
            }*/
        });

        function savePosition(values) {
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: "/garment/order",
                type: 'POST',
                data: { position: values },
                success: function(result) {
                    console.log("Positions updated:", result);
                    //table.ajax.reload(null, false);
                }
            });
        }

        table.on('row-reorder', function(e, diff, edit) {
			savePosition(edit.values);
		});

        /*table.on('row-reordered', function(e, details, edit) {
            console.log(details);
            console.log(edit);

            //const allRows = table.rows({ order: 'reordered' }).nodes();

            const positions = details.map(item => ({
                id: $(item.node).attr('data-id'),
                newPosition: item.newPosition
            }));
            console.log(positions);

            savePosition(positions);
        });*/

        $(document)
            .on('click', '.btn-remove', function(e){
                const action = $(this).data('action')
                    $target = $($(this).data('target')),
                    id = $(this).data('garment-id'),
                    name = $(this).data('garment-name');

                console.log(id, name);

                $target
                    .find('form').attr('action', action)
                    .end()
                    .find('#modal_content').text(id + ', ' + name);

            });
	});
</script>
@endsection
