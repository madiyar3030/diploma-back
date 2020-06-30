@extends('admin.layouts.layout')
@section('class','theme-red')
@section('body')
    <!-- Page Loader -->
    <div class="page-loader-wrapper">
        <div class="loader">
            <div class="preloader">
                <div class="spinner-layer pl-red">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
            <p>Подождите...</p>
        </div>
    </div>
    <!-- #END# Page Loader -->
    <!-- Overlay For Sidebars -->
    <div class="overlay"></div>
    <!-- #END# Overlay For Sidebars -->

    <!-- Top Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                <a href="javascript:void(0);" class="bars"></a>
                <a class="navbar-brand" href="../../index.html">EASY</a>
            </div>
        </div>
    </nav>
    <!-- #Top Bar -->
    <section>
        <!-- Left Sidebar -->
        <aside id="leftsidebar" class="sidebar">
            <!-- User Info -->
            <div class="user-info">
                <div class="image">
                    <img src="{{asset('uploads/avatar.png')}}" width="48" height="48" alt="User" />
                </div>
                <div class="info-container">
                    <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> {{session()->get('login')}}</div>
                    <div class="email">
                        @if (session()->get('access') == 1)
                            Доступ Админ
                        @else
                            Доступ Модератор
                        @endif
                    </div>
                    <div class="btn-group user-helper-dropdown">
                        <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                        <ul class="dropdown-menu pull-right">

                            <li><a href="{{route('Out')}}"><i class="material-icons">input</i>Выйти</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- #User Info -->
            <!-- Menu -->
            <div class="menu">
                <ul class="list">
                    <li class="header">Навигация</li>
                    <li class="active">
                        <a href="{{route('MainPage')}}">
                            <i class="material-icons">home</i>
                            <span>Главная</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('Clients')}}">
                            <i class="material-icons">home</i>
                            <span>Клиенты</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('Drivers')}}">
                            <i class="material-icons">home</i>
                            <span>Мастера</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('Moderators')}}">
                            <i class="material-icons">home</i>
                            <span>Модераторы</span>
                        </a>
                    </li>
                    <li>
                        <a  href="javascript:void(0);"  class="menu-toggle waves-effect waves-block toggled">
                            <i class="material-icons">home</i>
                            <span>Обр. связъ</span>
                        </a>
                        <ul class="ml-menu" style="display: block;">
                            <li>
                                <a href="{{route('Feedbacks',1)}}"  class=" waves-effect waves-block">
                                    <span>Клиенты</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('Feedbacks',2)}}"  class=" waves-effect waves-block">
                                    <span>Мастера</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle waves-effect waves-block toggled">
                            <i class="material-icons">trending_down</i>
                            <span>Заявки</span>
                        </a>
                        <ul class="ml-menu" style="display: block;">
                            <li>
                                <a href="{{route('ServiceOrders')}}" class=" waves-effect waves-block">
                                    <span>Услуги спец техники</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('ShippingOrders')}}" class=" waves-effect waves-block">
                                    <span>Перевозка грузов</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('ItemOrders')}}" class=" waves-effect waves-block">
                                    <span>Материалы с доставкой</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('Statistics')}}" class=" waves-effect waves-block">
                                    <span>Статистика</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('OrderCancels')}}" class=" waves-effect waves-block">
                                    <span>Отмененные заказы</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle waves-effect waves-block toggled">
                            <i class="material-icons">settings</i>
                            <span>Настройки</span>
                        </a>
                        <ul class="ml-menu" style="display: block;">
                            <li>
                                <a href="{{route('Cities')}}" class=" waves-effect waves-block">
                                    <span>Города</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('Commission')}}" class=" waves-effect waves-block">
                                    <span>комиссия</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('Materials')}}" class=" waves-effect waves-block">
                                    <span>Материялы</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('Transports','service_orders')}}" class=" waves-effect waves-block">
                                    <span>Транспорт (Услуги спецтехники)</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('Transports','shipping_orders')}}" class=" waves-effect waves-block">
                                    <span>Транспорт Перевозка грузов</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('Options')}}" class=" waves-effect waves-block">
                                    <span>Опции</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <!-- #Menu -->

        </aside>
        <!-- #END# Left Sidebar -->

    </section>

    <section class="content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </section>
@endsection