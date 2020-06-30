@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>Перевозка грузов</h2> <br>
    </div>
    <div class="body">
        <div>
            @if (count($order['images']) != 0)
                <img src="{{$order['images'][0]['path']}}">
            @endif
        </div>
        <table>
            <tr>
                <td>id</td>
                <td>{{$order['id']}}</td>
            </tr>
            <tr>
                <td>Материал</td>
                <td>{{$order['material']->name}}</td>
            </tr>
            <tr>
                <td>Размер</td>
                <td>{{$order['material_type']->name}}</td>
            </tr>
            <tr>
                <td>Количество</td>
                <td>{{$order['count']}} {{$order['count_type']['name']}}</td>
            </tr>
            <tr>
                <td>Дата</td>
                <td>{{$order['date']}}</td>
            </tr>
            <tr>
                <td>Цена</td>
                <td>{{$order['price']}}</td>
            </tr>
            <tr>
                <td>Куда</td>
                <td>{{$order['to_address']->text}}</td>
            </tr>

            <tr>
                <td>Клиент</td>
                <td><a href="{{route('Client',$order['client_id'])}}">Открыть Профиль</a></td>
            </tr>
            @if ($order['step'] > 1)
                <tr>
                    <td>Мастер</td>
                    <td><a href="{{route('Driver',$order['driver_id'])}}">Открыть Профиль</a></td>
                </tr>
            @endif
            <tr>
                <td>Статус</td>
                @if ($order['step']== 1)
                    <td >Активный</td>
                @elseif($order['step']== 2)
                    <td >у Мастера</td>
                @else
                    <td> Закончен</td>
                @endif
            </tr>
        </table>
        <div style="padding:  35px 0">
            <a href="{{route('ItemOrderEdit',$order['id'])}}"  class="btn btn-warning waves-effect">Изменить</a>
            <a  href="{{route('ItemOrderDelete',$order['id'])}}"  class="btn btn-danger waves-effect">удалить без возврата </a>
        </div>
    </div>

    <style>
        table{
            width: 100%;
        }
        table tr{
            border-bottom: 1px solid;
        }
        table td{
            padding: 15px;
        }
    </style>
@endsection


