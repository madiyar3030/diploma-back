<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



/**************************************Master***********************************************/

Route::prefix('system')->group(function () {
    Route::get('/','AdminController@SignIn')->name('SignIn');
    Route::post('/','AdminController@SignInPost')->name('SignInPost');

    Route::group(['middleware' => ['auth']], function () {
        Route::get('/out','AdminController@Out')->name('Out');
        Route::get('main','AdminController@MainPage')->name('MainPage');
        Route::get('moderators','AdminController@Moderators')->name('Moderators');
        Route::post('moderator/create','AdminController@ModeratorCreate')->name('ModeratorCreate');
        Route::get('moderator/delete/{id}','AdminController@ModeratorDelete')->name('ModeratorDelete');
        Route::get('feedbacks/{role}','AdminController@Feedbacks')->name('Feedbacks');
        Route::get('feedback/{role}/{user_id}','AdminController@Feedback')->name('Feedback');
        Route::post('res_feed','AdminController@ResFeedback')->name('ResFeedback');

        Route::prefix('clients')->group(function () {
            Route::get('/','AdminController@Clients')->name('Clients');
            Route::get('/info/{id}','AdminController@Client')->name('Client');
            Route::get('/search','AdminController@ClientsSearch')->name('ClientsSearch');
            Route::get('/edit/{id}','AdminController@ClientEdit')->name('ClientEdit');
            Route::post('/save','AdminController@ClientSave')->name('ClientSave');
            Route::get('/delete/{id}','AdminController@ClientDelete')->name('ClientDelete');
            Route::post('/access','AdminController@ClientAccess')->name('ClientAccess');
            Route::get('/access/{id}','AdminController@ClientAccessTrue')->name('ClientAccessTrue');
            Route::get('/history/{id}','AdminController@ClientHistories')->name('ClientHistories');

        });
        Route::prefix('drivers')->group(function () {
            Route::get('/','AdminController@Drivers')->name('Drivers');
            Route::get('/info/{id}','AdminController@Driver')->name('Driver');
            Route::get('/search','AdminController@DriversSearch')->name('DriversSearch');
            Route::get('/edit/{id}','AdminController@DriverEdit')->name('DriverEdit');
            Route::post('/save','AdminController@DriverSave')->name('DriverSave');
            Route::get('/delete/{id}','AdminController@DriverDelete')->name('DriverDelete');
            Route::post('/access','AdminController@DriverAccess')->name('DriverAccess');
            Route::get('/access/{id}','AdminController@DriverAccessTrue')->name('DriverAccessTrue');
            Route::get('/history/{id}','AdminController@DriverHistories')->name('DriverHistories');
            Route::get('/verification/{id}','AdminController@DriverVerification')->name('DriverVerification');
        });
        Route::prefix('orders')->group(function () {
            Route::get('/statistics','AdminController@Statistics')->name('Statistics');
            Route::get('/cancels','AdminController@OrderCancels')->name('OrderCancels');
            Route::prefix('services')->group(function () {
                Route::get('/','AdminController@ServiceOrders')->name('ServiceOrders');
                Route::get('/show/{id}','AdminController@ServiceOrderShow')->name('ServiceOrderShow');
                Route::get('/edit/{id}','AdminController@ServiceOrderEdit')->name('ServiceOrderEdit');
                Route::post('/save','AdminController@ServiceOrderSave')->name('ServiceOrderSave');
                Route::get('/delete/{id}','AdminController@ServiceOrderDelete')->name('ServiceOrderDelete');
            });
            Route::prefix('shipping')->group(function () {
                Route::get('/','AdminController@ShippingOrders')->name('ShippingOrders');
                Route::get('/show/{id}','AdminController@ShippingOrderShow')->name('ShippingOrderShow');
                Route::get('/edit/{id}','AdminController@ShippingOrderEdit')->name('ShippingOrderEdit');
                Route::post('/save','AdminController@ShippingOrderSave')->name('ShippingOrderSave');
                Route::get('/delete/{id}','AdminController@ShippingOrderDelete')->name('ShippingOrderDelete');
            });
            Route::prefix('item')->group(function () {
                Route::get('/','AdminController@ItemOrders')->name('ItemOrders');
                Route::get('/show/{id}','AdminController@ItemOrderShow')->name('ItemOrderShow');
                Route::get('/edit/{id}','AdminController@ItemOrderEdit')->name('ItemOrderEdit');
                Route::post('/save','AdminController@ItemOrderSave')->name('ItemOrderSave');
                Route::get('/delete/{id}','AdminController@ItemOrderDelete')->name('ItemOrderDelete');
            });
        });

        Route::get('commission','AdminController@Commission')->name('Commission');
        Route::post('commission/save','AdminController@CommissionSave')->name('CommissionSave');
        Route::get('cities','AdminController@Cities')->name('Cities');
        Route::post('city/create','AdminController@CreateCity')->name('CreateCity');
        Route::get('city/edit/{id}','AdminController@EditCity')->name('EditCity');
        Route::post('city/save/','AdminController@SaveCity')->name('SaveCity');
        Route::get('city/delete/{id}','AdminController@DeleteCity')->name('DeleteCity');

        Route::get('options','AdminController@Options')->name('Options');
        Route::post('option/create','AdminController@CreateOption')->name('CreateOption');
        Route::get('option/edit/{id}','AdminController@EditOption')->name('EditOption');
        Route::post('option/save/','AdminController@SaveOption')->name('SaveOption');
        Route::get('option/delete/{id}','AdminController@DeleteOption')->name('DeleteOption');

        Route::get('materials','AdminController@Materials')->name('Materials');
        Route::post('material/create','AdminController@CreateMaterial')->name('CreateMaterial');
        Route::get('material/edit/{id}','AdminController@EditMaterial')->name('EditMaterial');
        Route::post('material/save/','AdminController@SaveMaterial')->name('SaveMaterial');
        Route::get('material/delete/{id}','AdminController@DeleteMaterial')->name('DeleteMaterial');


        Route::post('material_type/create','AdminController@CreateMaterialType')->name('CreateMaterialType');
        Route::get('material_type/{id}','AdminController@MaterialTypes')->name('MaterialTypes');
        Route::get('material_type/edit/{id}','AdminController@EditMaterialType')->name('EditMaterialType');
        Route::post('material_type/save/','AdminController@SaveMaterialType')->name('SaveMaterialType');
        Route::get('material_type/delete/{id}','AdminController@DeleteMaterialType')->name('DeleteMaterialType');

        Route::get('transports/{type}','AdminController@Transports')->name('Transports');
        Route::post('transport/create','AdminController@CreateTransport')->name('CreateTransport');
        Route::get('transport/edit/{id}','AdminController@EditTransport')->name('EditTransport');
        Route::post('transport/save/','AdminController@SaveTransport')->name('SaveTransport');
        Route::get('transport/delete/{id}','AdminController@DeleteTransport')->name('DeleteTransport');

        Route::post('transport_type/create','AdminController@CreateTransportType')->name('CreateTransportType');
        Route::get('transport_type/{id}','AdminController@TransportTypes')->name('TransportTypes');
        Route::get('transport_type/edit/{id}','AdminController@EditTransportType')->name('EditTransportType');
        Route::post('transport_type/save/','AdminController@SaveTransportType')->name('SaveTransportType');
        Route::get('transport_type/delete/{id}','AdminController@DeleteTransportType')->name('DeleteTransportType');

    });
});




//Route::fallback(function(){
//    $result['statusCode'] = 404;
//    $result['message'] = 'Страница не найдено';
//    $result['result'] = null;
//
//    return response()->json($result, 404);
//});