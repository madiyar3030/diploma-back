@extends('admin.layouts.admin')
@section('content')
    <form action="{{route('SaveMaterial')}}" method="post">
            {{csrf_field()}}
        <input type="hidden" name="id" value="{{$m->id}}">
        <label>Называние</label>
        <div class="form-group">
            <div class="form-line">
                <input type="text" class="form-control" placeholder="Назывние" value="{{$m->name}}" name="name">
            </div>
        </div>
        <button type="submit" class="btn btn-success waves-effect">Сохранить</button>
    </form>
@endsection