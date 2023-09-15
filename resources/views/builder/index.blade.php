@extends('layouts.app',['title' => 'Jersey Builder'])
@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css"/>
{{--<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>--}}
@endsection
@section('content')
<div class="card-body">
	<div class="row">
		<div class="col-md-12 text-center">
			<a href="{{route('builder.shopify')}}" class="btn btn-warning float-right" role="button">Sync products</a>
			<h4>Manage Jersey Builders:</h4>
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
    <table id="table" class="table table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Position</th>
            <th>Product Title</th>
            <th>Autoupdate Colors set</th>
            <th>Manage SVG and Colors set</th>
            <th>Delete</th>
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
                <h4 class="modal-title text-light">You want delete this Product?</h4>
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
<script type="text/javascript">
    jQuery(document).ready(function($) {

        function updateProductField(el){
            var product_id = $(el).data('id');
            var checked = $(el).is(":checked") ? 1 : 0;
            console.log(product_id, checked);

            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: "{{route('builder.ajaxupdatefields')}}",
                type: 'POST',
                data: {'id': product_id, 'color_autoupdate': checked},
                success: function(response){
                    console.log(response);
                    var td_class = (~~response.error == 0) ? 'alert-success' : 'alert-danger';
                    $('tr#product_'+response.id).find('td').remove().end().html('<td colspan="6" class="text-center '+td_class+'">'+response.message+'</td>');
                }
            });
        }

		/*$('#table').DataTable({
			"order": [[ 0, "asc" ]],
			"pageLength": 50,
			"lengthMenu": [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, "All"]]
		});

		$('input[name="color_autoupdate"]').on('change', function(e){
			updateProductField($(this));
		});
		*/

        $('#table').DataTable({
            "order": [[0, "asc"]],
            "pageLength": 20,
            "lengthMenu": [[10, 20, 50, 75, 100, -1], [10, 20, 50, 75, 100, "All"]],
            "processing": true,
            "serverSide": true,
            "ajax": "/builder/parts",
            "columnDefs": [
                { orderable: true, targets: [0], className: "text-nowrap"},
                { orderable: true, targets: [1], className: "text-nowrap"},
                { orderable: false, targets: '_all' }
            ],
            "columns": [
                {className: "text-center"},
                {className: "text-left"},
                {className: "text-center"},
                {className: "text-center", order: false},
                {className: "text-center", order: false},
            ]
        });

        $(document)
            .on('click', '.btn-remove', function(e){
                const action = $(this).data('action')
                    $target = $($(this).data('target')),
                    id = $(this).data('product-id'),
                    name = $(this).data('product-name');

                console.log(id, name);

                $target
                    .find('form').attr('action', action)
                    .end()
                    .find('#modal_content').text(id + ', ' + name);

            })
            .on('change', 'input[name="color_autoupdate"]', function(e){
                updateProductField($(this));
            });
	});
</script>
@endsection
