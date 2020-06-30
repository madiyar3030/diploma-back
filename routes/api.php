<?php

use Illuminate\Http\Request;




/**************************************Client***********************************************/
Route::prefix('client')->group(function () {
    Route::post('register','ClientController@Register');
    Route::post('edit','ClientController@Edit');
    Route::post('auth','ClientController@Auth');
    Route::post('login','ClientController@Login');
    Route::post('delete','ClientController@DeleteClient');
    Route::post('feedback','ClientController@FeedbackCreate');
    Route::post('feedback/list','ClientController@FeedbackList');


    Route::post('chat/send','ClientController@ChatSendMessage');
    Route::post('chats','ClientController@Chats');
    Route::post('chat','ClientController@Chat');
    Route::post('online','ClientController@Online');
    Route::post('create/chat','ClientController@CreateChat');
});
/**************************************Driver***********************************************/
Route::prefix('driver')->group(function () {
    Route::post('register','DriverController@Register');
    Route::post('edit','DriverController@Edit');
    Route::post('car/create','DriverController@CreateCar');
    Route::post('car/edit','DriverController@EditCar');
    Route::post('car/delete','DriverController@DeleteCar');
    Route::post('auth','DriverController@Auth');
    Route::post('login','DriverController@Login');
    Route::post('verification','DriverController@Verification');
    Route::post('feedback','DriverController@FeedbackCreate');
    Route::post('feedback/list','DriverController@Feedbacklist');
    Route::post('position','DriverController@Position');
    Route::post('shared','DriverController@SharedCreate');
    Route::post('package/buy', 'DriverController@BuyPackage');

    Route::prefix('group')->group(function () {
        Route::post('create','DriverController@GroupCreate');
        Route::post('add/driver','DriverController@GroupAddDriver');
        Route::post('signin/driver','DriverController@GroupSignInDriver');
        Route::post('delete/driver','DriverController@GroupDeleteDriver');

        Route::post('send','DriverController@SendMessage');
        Route::get('gets','DriverController@GetMessages');

        Route::get('get/{id}','DriverController@GroupGet');

    });

    Route::post('chat/send','DriverController@ChatSendMessage');
    Route::post('chats','DriverController@Chats');
    Route::post('chat','DriverController@Chat');
    Route::post('online','DriverController@Online');
    Route::post('create/chat','DriverController@CreateChat');
});
/**************************************Order***********************************************/
Route::prefix('order')->group(function (){
    Route::post('history/client','ClientController@HistoryOrder');
    Route::post('history/driver','DriverController@HistoryOrder');
    Route::post('my_offers','DriverController@MyOffers');
    Route::post('cancel','AdminController@OrderCancel');

    Route::post('active/client','ClientController@ActiveOrder');
    Route::post('active/driver','DriverController@ActiveOrder');
    Route::prefix('service')->group(function (){
        Route::get('get/{id}','Controller@GetServiceOrder');
        Route::post('accept','ClientController@AcceptServiceOrder');
        Route::post('create','ClientController@CreateServiceOrder');
        Route::post('price','ClientController@PriceServiceOrder');
        Route::post('delete','ClientController@DeleteServiceOrder');
        Route::post('end','ClientController@EndServiceOrder');

        //Offers
        Route::post('offer/create','DriverController@ServiceOfferCreate');
        Route::get('offers/{order_id}/{step?}','Controller@GetServiceOffers');
        Route::post('offers/cancel','ClientController@CancelServiceOffer');
    });
    Route::prefix('shipping')->group(function (){
        Route::post('create','ClientController@CreateShippingOrder');
        Route::post('price','ClientController@PriceShippingOrder');
        Route::get('get/{id}','Controller@GetShippingOrder');
        Route::post('accept','ClientController@AcceptShippingOrder');
        Route::post('end','ClientController@EndShippingOrder');
        Route::post('delete','ClientController@DeleteShippingOrder');
        //Offers
        Route::post('offer/create','DriverController@ShippingOfferCreate');
        Route::get('offers/{order_id}/{step?}','Controller@GetShippingOffers');
        Route::post('offers/cancel','ClientController@CancelShippingOffer');
    });
    Route::prefix('item')->group(function (){
        Route::post('create','ClientController@CreateItemOrder');
        Route::post('price','ClientController@PriceItemOrder');
        Route::get('get/{id}','Controller@GetItemOrder');
        Route::post('accept','ClientController@AcceptItemOrder');

        Route::post('end','ClientController@EndItemOrder');
        Route::post('delete','ClientController@DeleteItemOrder');
        //Offers
        Route::post('offer/create','DriverController@ItemOfferCreate');
        Route::get('offers/{order_id}/{step?}','Controller@GetItemOffers');
        Route::post('offers/cancel','ClientController@CancelItemOffer');
    });

    Route::post('list','DriverController@OrderList');

    Route::fallback(function(){
        $result['statusCode'] = 404;
        $result['message'] = 'api not found';
        $result['result'] = null;

        return response()->json($result, 404);
    })->name('fallback');;
});
/**************************************Getters***********************************************/
Route::prefix('get')->group(function () {
    Route::get('client/{id}', 'Controller@GetClient');
    Route::get('driver/{id}', 'Controller@GetDriver');
    Route::get('transports/{type?}', 'Controller@GetTransports');
    Route::get('transport/{id}', 'Controller@GetTransportTypes');
    Route::get('materials', 'Controller@GetMaterials');
    Route::get('count_types', 'Controller@GetCountTypes');
    Route::get('material/{id}', 'Controller@GetMaterialTypes');
    Route::get('cities/{id}', 'Controller@GetCities');
    Route::get('regions', 'Controller@GetRegions');
    Route::get('offer/{id}', 'Controller@GetOffer');
    Route::get('options', 'Controller@GetOptions');
    Route::get('packages', 'DriverController@Packages');
});
/**************************************Others***********************************************/
Route::post('review/create', 'ClientController@ReviewCreate');
Route::get('score/clients', 'ClientController@Score');
Route::get('score/drivers', 'DriverController@Score');

Route::get('paybox/{amount}/{id}', 'DriverController@PayBox');
Route::post('paybox/result', 'DriverController@PayBoxResult')->name('PayBoxResult');


Route::fallback(function(){
    $result['statusCode'] = 404;
    $result['message'] = 'api not found';
    $result['result'] = null;

    return response()->json($result, 404);
});