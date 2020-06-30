@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>{{$client['name']}}</h2> <br>
    </div>
    <div class="body">

       <table>
           <tr>
               <td>Телефон номер</td>
               <td>{{$client['phone']}}</td>
           </tr>
           <tr>
               <td>Email</td>
               <td>{{$client['email']}}</td>
           </tr>
           <tr>
               <td>Ранг</td>
               <td>{{$client['rang']['name']}}</td>
           </tr>
           <tr>
               <td>Балл</td>
               <td>{{$client['ball']}}</td>
           </tr>
           <tr>
               <td>ИИН</td>
               <td>{{$client['iin']}}</td>
           </tr>
           <tr>
               <td>Город</td>
               <td>{{$client['city']['name']}}</td>
           </tr>
           <tr>
               <td>о себе</td>
               <td>{{$client['description']}}</td>
           </tr>

       </table>
       <div style="padding:  35px 0">
           @if ($client['access'] == 1)
               <button type="button" class="btn btn-default waves-effect m-r-20" data-toggle="modal" data-target="#defaultModal">Блокировать</button>
           @else
               <a href="{{route('ClientAccessTrue',$client['id'])}}" class="btn btn-default waves-effect m-r-20"  >Разблокировать</a>
           @endif
           <a href="{{route('ClientEdit',$client['id'])}}"  class="btn btn-warning waves-effect">Изменить</a>
           <a  href="{{route('ClientDelete',$client['id'])}}"  class="btn btn-danger waves-effect">удалить без возврата </a>
       </div>
    </div>
    <div class="modal fade" id="defaultModal" tabindex="-1" role="dialog" style="display: none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="defaultModalLabel"></h4>
                </div>
                <form action="{{route('ClientAccess')}}" method="post">
                    {{csrf_field()}}
                    <input type="hidden" name="id" value="{{$client['id']}}">
                    <div class="modal-body">
                        <input type="date" name="access_date" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-link waves-effect">Блокировать</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
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

