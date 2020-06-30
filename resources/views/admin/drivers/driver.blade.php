@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>{{$driver['name']}}</h2> <br>
    </div>
    <div class="body">

       <table>
           <tr>
               <td>Телефон номер</td>
               <td>{{$driver['phone']}}</td>
           </tr>
           <tr>
               <td>Email</td>
               <td>{{$driver['email']}}</td>
           </tr>
           <tr>
               <td>Ранг</td>
               <td>{{$driver['rang']['name']}}</td>
           </tr>
           <tr>
               <td>Балл</td>
               <td>{{$driver['ball']}}</td>
           </tr>
           <tr>
               <td>Счет(Бонус).</td>
               <td>{{$driver['bonus']}}</td>
           </tr>
           <tr>
               <td>О себе</td>
               <td>{{$driver['description']}}</td>
           </tr>
           <tr>
               <td>ИП</td>
               <td>{{$driver['ip']}}</td>
           </tr>
           <tr>
               <td>ИИН</td>
               <td>{{$driver['iin']}}</td>
           </tr>

           <tr>
               <td>Паспорт</td>
               <td>{{$driver['passport']}}</td>
           </tr>

           <tr>
               <td>Город</td>
               <td>{{$driver['city']['name']}}</td>
           </tr>
           <tr>
               <td>Даты регистраци</td>
               <td>{{$driver['created_at']}}</td>
           </tr>
           <tr>
               <td>Работал</td>
               <td>{{$driver['worked']}} часа</td>
           </tr>
           <tr>
               <td>Оценка за Скорсть работы</td>
               <td>{{$driver['reviews_all']['speed']}}</td>
           </tr>
           <tr>
               <td>Оценка за Пунктуальность</td>
               <td>{{$driver['reviews_all']['punctuality']}}</td>
           </tr>
           <tr>
               <td>Оценка за Качество работы</td>
               <td>{{$driver['reviews_all']['quality']}}</td>
           </tr>
           <tr>
               <td>Оценка за цену</td>
               <td>{{$driver['reviews_all']['price']}}</td>
           </tr>
       </table>
        <h3>Данные Верификации</h3>
        <div class="passportImages">

            @foreach($driver['passport_images'] as $image)
                <div class="col-md-4">
                    <img src="{{asset($image->path)}}">
                </div>
            @endforeach
        </div>
       <div style="padding:  35px 0">
           @if ($driver['access'] == 1)
               <button type="button" class="btn btn-default waves-effect m-r-20" data-toggle="modal" data-target="#defaultModal">Блокировать</button>
           @else
               <a href="{{route('DriverAccessTrue',$driver['id'])}}" class="btn btn-default waves-effect m-r-20"  >Разблокировать</a>
           @endif
           @if ($driver['verification'] == 0)
               <a href="{{route('DriverVerification',$driver['id'])}}" class="btn btn-default">Верифицировать</a>
           @endif
           <a href="{{route('DriverEdit',$driver['id'])}}"  class="btn btn-warning waves-effect">Изменить</a>
           <a  href="{{route('DriverDelete',$driver['id'])}}"  class="btn btn-danger waves-effect">удалить без возврата </a>
       </div>
    </div>


    <div class="modal fade" id="defaultModal" tabindex="-1" role="dialog" style="display: none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                </div>
                <form action="{{route('DriverAccess')}}" method="post">
                    {{csrf_field()}}
                    <input type="hidden" name="id" value="{{$driver['id']}}">
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
        img{
            display: block;
            max-width: 100%;
        }
        .passportImages{
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 45px 0;
            flex-wrap: wrap;
        }
    </style>
@endsection

