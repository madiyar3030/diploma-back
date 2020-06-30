@extends('admin.layouts.admin')
@section('content')
    <form action="{{route('SaveTransportType')}}" method="post">
            {{csrf_field()}}
        <input type="hidden" name="id" value="{{$t->id}}">
        <label>Называние</label>
        <div class="form-group">
            <div class="form-line">
                <input type="text" class="form-control" placeholder="Назывние" value="{{$t->name}}" name="name">
            </div>
        </div>
        <button type="submit" class="btn btn-success waves-effect">Сохранить</button>
    </form>
@endsection