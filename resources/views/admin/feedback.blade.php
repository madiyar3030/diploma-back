@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>Обр. связъ</h2> <br>
    </div>
    <div class="dialogs">
     @foreach($feedbacks as $f)
         @if ($f->role == 1 or $f->role ==2)
            <li>
                <span>{{$user->name}}</span>
                <p>{{$f->text}}</p>
            </li>
         @else
            <li class="admin">
                <span>Админ</span>
                <p>{{$f->text}}</p>
            </li>
         @endif
     @endforeach


    </div>

    <form class="send" action="{{route('ResFeedback')}}" method="post">
        {{csrf_field()}}
        <textarea name="text">

        </textarea>
        <input type="hidden" name="user_id" value="{{$user->id}}">
        <input type="submit" value="Отправить" class=" btn btn-default">
    </form>

    <style>
        .dialogs{
            display: flex;
            flex-direction: column;
        }
        .dialogs li{
            list-style: none;
            display: flex;
            flex-direction: column;
        }
        .admin{
            align-items: flex-end;
        }
        .dialogs span{
            color: #a7a5a5;
        }
        .send{
            margin-top: 25px;
        }
        .send textarea{
            width: 100%;
            min-height: 80px;
            border-radius: 5px;
        }
    </style>
@endsection
