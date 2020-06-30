@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>Статистика</h2> <br>
    </div>

    <h2>за все время</h2>
    <table>
        <tr class="title">
            <td>Заявки</td>
            <td>Общее Количество</td>
            <td>Активные</td>
            <td>У мастера</td>
            <td>Законченные</td>
        </tr>
        <tr>
            <td>Услуги спец техники</td>
            <td>{{$order['service']['all']['all']}}</td>
            <td>{{$order['service']['all']['1']}}</td>
            <td>{{$order['service']['all']['2']}}</td>
            <td>{{$order['service']['all']['3']}}</td>
        </tr>
        <tr>
            <td>Перевозка грузов</td>
            <td>{{$order['shipping']['all']['all']}}</td>
            <td>{{$order['shipping']['all']['1']}}</td>
            <td>{{$order['shipping']['all']['2']}}</td>
            <td>{{$order['shipping']['all']['3']}}</td>
        </tr>
        <tr>
            <td>Материалы с доставкой</td>
            <td>{{$order['item']['all']['all']}}</td>
            <td>{{$order['item']['all']['1']}}</td>
            <td>{{$order['item']['all']['2']}}</td>
            <td>{{$order['item']['all']['3']}}</td>
        </tr>
    </table>

    <h2>за 30 дней</h2>
    <table>
        <tr class="title">
            <td>Заявки</td>
            <td>Общее Количество</td>
            <td>Активные</td>
            <td>У мастера</td>
            <td>Законченные</td>
        </tr>
        <tr>
            <td>Услуги спец техники</td>
            <td>{{$order['service']['month']['all']}}</td>
            <td>{{$order['service']['month']['1']}}</td>
            <td>{{$order['service']['month']['2']}}</td>
            <td>{{$order['service']['month']['3']}}</td>
        </tr>
        <tr>
            <td>Перевозка грузов</td>
            <td>{{$order['shipping']['month']['all']}}</td>
            <td>{{$order['shipping']['month']['1']}}</td>
            <td>{{$order['shipping']['month']['2']}}</td>
            <td>{{$order['shipping']['month']['3']}}</td>
        </tr>
        <tr>
            <td>Материалы с доставкой</td>
            <td>{{$order['item']['month']['all']}}</td>
            <td>{{$order['item']['month']['1']}}</td>
            <td>{{$order['item']['month']['2']}}</td>
            <td>{{$order['item']['month']['3']}}</td>
        </tr>
    </table>


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
        .title{
            color: #00A6C7;
            font-weight: bold;
        }
    </style>
@endsection
