@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>Обр. связъ</h2> <br>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Имя</th>
            <th>Телефон</th>
            <th>профиль</th>
            <th>Чат</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $i)
            <tr>
                <th scope="row">{{$i->id}}</th>
                <th>{{$i->name}}</th>
                <th >{{$i->phone}}</th>
                @if ($i->role == 1)
                    <th><a href="{{route('Client',$i->id)}}">Отрыть профиль</a></th>
                @else
                    <th><a href="{{route('Driver',$i->id)}}">Отрыть профиль</a></th>
                @endif

                <th><a href="{{route('Feedback',[$role,$i->id])}}">Отрыть чат</a></th>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
