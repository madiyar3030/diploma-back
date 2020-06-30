@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>Клиенты</h2> <br>
        <form action="{{route('ClientsSearch')}}" method="get">
            {{csrf_field()}}
            <div class="row clearfix">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <div class="form-line">
                            <input type="search" name="text" class="form-control" placeholder="Поиск..." style="padding-left:15px ">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @foreach($clients as $client)
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="header">
                        <h2>
                            {{$client->name}}
                            <small>{{$client->phone}}</small>
                            <small>{{App\Models\Rang::find($client->rang_id)->name}}</small>
                        </h2>
                    </div>
                    <div >
                        <div class="clearfix" style="padding: 20px;">
                            <a class="btn btn-link" href="{{route('ClientHistories',$client->id)}}">История заявок</a>
                            <a class="btn btn-link" href="{{route('Client',$client->id)}}">Полная информация</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{ $clients->links() }}
@endsection