@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>Материялы</h2> <br>
    </div>
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div style="padding: 20px;" >
                    <div class="body table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Статус</th>
                                <th>Материал</th>
                                <th>Размер</th>
                                <th>Количество</th>
                                <th>Дата</th>
                                <th>Цена</th>
                                <th>Куда</th>
                                <th>Клиент</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <th scope="row">{{$order['id']}}</th>
                                    <td>
                                        @if ($order['step'] == 1)
                                            <span class="label label-success">Активный</span>
                                        @elseif($order['step'] == 2)
                                            <span class="label label-info">У Мастера</span>
                                        @else
                                            <span class="label label-warning">Закончен</span>
                                        @endif
                                    </td>
                                    <td>{{$order['material']->name}}</td>
                                    <td>{{$order['material_type']->name}}</td>
                                    <td>{{$order['count']}} {{$order['count_type']['name']}}</td>
                                    <td>{{$order['date']}}</td>
                                    <td>{{$order['price']}}</td>
                                    <td>{{$order['to_address']->text}}</td>
                                    <td><a href="{{route('Client',$order['client_id'])}}">Профиль</a></td>

                                    <td>
                                        <a href="{{route('ItemOrderEdit',$order['id'])}}"  class="btn btn-warning waves-effect">Изменить</a>
                                    </td>
                                    <td>
                                        <a  href="{{route('ItemOrderDelete',$order['id'])}}"  class="btn btn-danger waves-effect">удалить </a>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{ $ordersDB->links() }}
@endsection




