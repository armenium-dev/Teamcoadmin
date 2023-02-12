@extends('layouts.app',['title' => 'Edit Ship Engine Jersey Type Options'])
@section('content')
    <div class="card-body">
        <div class="row">
            <div class="col-md-8 text-left">
                <h4>Roster form files:</h4>
            </div>
            <div class="col-md-4 text-right">
                <h4>Edit mode</h4>
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
        <form action="{{ route('settings.update',$settings->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input name="_method" type="hidden" value="PATCH">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" name="name" placeholder="Option name" value="{{$settings->name}}">
            </div>
            <div class="form-group">
                <label for="name">Value:</label>
                <table id="js_multi_data_table" class="table table-striped">
                        <tr>
                            <th>Title</th>
                            <th>Current</th>
                            <th>Upload new one</th>
                        </tr>
                    @foreach($json_data as $k => $v)
                        <tr>
                            <td>{{$v['title']}}</td>
                            <td><a href="{!! asset('storage/'.$v['file']) !!}" target="_blank">{{basename($v['file'])}}</a></td>
                            <td>
                                <input type="hidden" class="form-control" name="value[{{$k}}][id]" value="{{$v['id']}}">
                                <input type="hidden" class="form-control" name="value[{{$k}}][title]" value="{{$v['title']}}">
                                <input type="hidden" class="form-control" name="value[{{$k}}][old_file]" value="{{$v['file']}}">
                                <input type="file" class="form-control" name="value[{{$k}}][file]">
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <button class="btn btn-primary" type="submit">Submit</button>
            <a href="{{route('settings.index')}}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
