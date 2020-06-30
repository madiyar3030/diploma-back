@extends('admin.layouts.layout')
@section('class','login-page')
@section('body')
    <div class="login-box">
        <div class="logo">
            <a href="javascript:void(0);">Easy<b> Admin</b></a>
            <small>Thousand Company</small>
        </div>
        <div class="card">
            <div class="body">
                <form id="sign_in" method="POST" action="{{route('SignInPost')}}">
                    {{csrf_field()}}
                    <div class="msg">Введите данные чтобы войти</div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">person</i>
                        </span>
                        <div class="form-line">
                            <input type="text" class="form-control" name="login" placeholder="Логин" required autofocus>
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">lock</i>
                        </span>
                        <div class="form-line">
                            <input type="password" class="form-control" name="password" placeholder="Пароль" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <button class="btn btn-block bg-pink waves-effect" type="submit">SIGN IN</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
