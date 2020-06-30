@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>Материялы</h2> <br>
        <button type="button" class="btn btn-primary waves-effect m-r-20" data-toggle="modal" data-target="#defaultModal">+</button>
        <div class="body table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Называние</th>
                </tr>
                </thead>
                <tbody>
                @foreach($materials as $m)
                    <tr>
                        <th scope="row">{{$m->id}}</th>
                        <td><a href="{{route('MaterialTypes',$m->id)}}">{{$m->name}}</a></td>
                        <td>
                            <a href="{{route('EditMaterial',$m->id)}}"  class="btn btn-warning waves-effect">
                                <i class="material-icons">mode_edit</i>
                            </a>

                            <a href="{{route('DeleteMaterial',$m->id)}}"  class="btn btn-danger waves-effect">
                                <i class="material-icons">delete</i>
                            </a>
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="defaultModal" tabindex="-1" role="dialog" style="display: none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="defaultModalLabel"></h4>
                </div>
                <form action="{{route('CreateMaterial')}}" method="post">
                    <div class="modal-body">
                        {{csrf_field()}}
                        <label>Называние</label>
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Назывние" name="name">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success waves-effect">Создать</button>
                        <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection