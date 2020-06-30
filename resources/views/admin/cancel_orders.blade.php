@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>Отмененные заказы </h2> <br>
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
                                    <th>Отменивший</th>
                                    <th>Тип заказа</th>
                                    <th>Причина</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                    <th scope="row">{{$order['id']}}</th>
                                    <td>
                                        @if ($order['user_type'] == 1)
                                            <a href="{{route('Client',$order['user_id'])}}" id=""> {{$order['user']->name}}</a>
                                        @else
                                            <a href="{{route('Driver',$order['user_id'])}}" id=""> {{$order['user']->name}}</a>

                                        @endif
                                    </td>
                                    <td>
                                        {{$order['order_type']}}
                                    </td>
                                    <td>
                                        {{$order['text']}}
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
@endsection


