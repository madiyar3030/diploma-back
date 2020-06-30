@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>История</h2> <br>

    </div>
    @foreach($arr as $item)
       @if ($item['type'] == 'service_orders')
           <div class="row clearfix">
               <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                   <div class="card">
                       <div class="header">
                           <h2>
                               Услуги спец техники
                               {{--<small>{{$client->phone}}</small>--}}
                               {{--<small>{{App\Models\Rang::find($client->rang_id)->name}}</small>--}}
                           </h2>
                       </div>
                       <div >
                           <p><b>Дата: </b>{{$item['created_at']}}</p>
                           <p><b>Цена: </b>{{$item['price']}}</p>
                           <a href="{{route('ServiceOrderShow',$item['id'])}}" class="btn btn-link">Открыт</a>

                       </div>
                   </div>
               </div>
           </div>
       @elseif($item['type'] == 'shipping_orders')
           <div class="row clearfix">
               <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                   <div class="card">
                       <div class="header">
                           <h2>
                              Перевозка грузов
                           </h2>
                       </div>
                       <div >

                           <p><b>Дата: </b>{{$item['created_at']}}</p>
                           <p><b>Цена: </b>{{$item['price']}}</p>
                           <a href="{{route('ShippingOrderShow',$item['id'])}}" class="btn btn-link">Открыт</a>
                       </div>
                   </div>
               </div>
           </div>
       @elseif($item['type'] == 'item_orders')
           <div class="row clearfix">
               <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                   <div class="card">
                       <div class="header">
                           <h2>
                               Материалы с доставкой
                           </h2>
                       </div>
                       <div >
                           <p><b>Дата: </b>{{$item['created_at']}}</p>
                           <p><b>Цена: </b>{{$item['price']}}</p>
                           <a href="{{route('ItemOrderShow',$item['id'])}}" class="btn btn-link">Открыт</a>

                       </div>
                   </div>
               </div>
           </div>
       @endif
    @endforeach

@endsection