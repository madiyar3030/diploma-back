@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>Модераторы</h2> <br>
        <form action="{{route('ModeratorCreate')}}" method="post">
            {{csrf_field()}}
            <div class="row clearfix">
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <div class="form-line">
                            <input required type="text" name="login" class="form-control" placeholder="Логин" style="padding-left:15px ">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <div class="form-line">
                            <input required type="text" name="password" class="form-control" placeholder="Пароль" style="padding-left:15px ">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <div class="form-line">
                            <input type="submit"  class="form-control" value="Создать"  style="padding-left:15px ">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="body table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Логин</th>
                <th>Пароль</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach(\App\Models\Moderator::orderBy('id','DESC')->get() as $item)
                <tr>
                    <th scope="row">{{$item->id}}</th>
                    <td>{{$item->login}}</td>
                    <td>{{$item->password}}</td>
                    <td><a href="{{route('ModeratorDelete',$item->id)}}">Удалить</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection