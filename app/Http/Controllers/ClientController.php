<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Car;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\City;
use App\Models\Client;
use App\Models\Driver;
use App\Models\History;
use App\Models\Image;
use App\Models\Option;
use App\Models\ShippingOption;
use App\Models\ItemOrder;
use App\Models\Offer;
use App\Models\Review;
use App\Models\ServiceOrder;
use App\Models\ShippingOrder;
use App\Models\ShippingTransport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use App\Models\Feedback;
use App\Push;
use App\Models\Other;
class ClientController extends Controller
{
    public function Register(Request $request){

        $rules = [
            'phone' => 'required|unique:clients',
            'name' => 'required',
            'city_id' => 'required|exists:cities,id',
            'description' => 'required',
            'avatar' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = new Client();
            $client->phone = $request['phone'];
            $client->email= $request['email'];
            $client->name= $request['name'];
            $client->city_id = $request['city_id'];
            $client->iin = $request['iin'];
            $client->description = $request['description'];
            if (isset($request['avatar'])){
                $client->avatar = $this->uploadfile($request['avatar']);
            }
            $client->token = str_random(60);
            $client->save();

            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = $this->GetClient($client->id)['result'];
        }

        return response()->json($result, $result['statusCode']);
    }
    public function Auth(Request $request){
        $rules = [
            'phone' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('phone',$request['phone'])->first();
            if  ($client){
                if ($client->access == 0){
                    $date = Carbon::parse($client->access_date);
                    if ($date->lte(Carbon::now())){
                        $client->access = 1;
                        $client->access_date = null;
                        $client->save();

                        $result['statusCode'] = 200;
                        $result['message'] = "success";
                        $result['result'] = $this->GetClient($client->id)['result'];
                    }
                    else{
                        $result['statusCode'] = 405;
                        $result['message'] = "no access";
                        $result['result'] =  $this->GetClient($client->id)['result'];
                    }
                }
                else{
                    $result['statusCode'] = 200;
                    $result['message'] = "success";
                    $result['result'] = $this->GetClient($client->id)['result'];
                }
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "no access";
                $result['result'] = null;
            }

        }
        return response()->json($result, $result['statusCode']);
    }
    public function Login(Request $request){
        $rules = [
            'token' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token',$request['token'])->first();
            if  ($client){
                if ($client->access == 0){
                    $date = Carbon::parse($client->access_date);
                    if ($date->lte(Carbon::now())){
                        $client->access = 1;
                        $client->access_date = null;
                        $client->save();

                        $result['statusCode'] = 200;
                        $result['message'] = "success";
                        $result['result'] = $this->GetClient($client->id)['result'];
                    }
                    else{
                        $result['statusCode'] = 405;
                        $result['message'] = "no access";
                        $result['result'] =  $this->GetClient($client->id)['result'];
                    }
                }
                else{
                    $result['statusCode'] = 200;
                    $result['message'] = "success";
                    $result['result'] = $this->GetClient($client->id)['result'];
                }
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "no access";
                $result['result'] = null;
            }

        }
        return response()->json($result, $result['statusCode']);
    }
    public function DeleteClient(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token',$request['token'])->first();
            if  ($client){
                $orders = ServiceOrder::where('client_id',$client->id)->get();
                foreach ($orders as $order) {
                    $images = Image::where('parent_type','service_orders')->where('parent_id',$order->id)->get();

                    if (count($images) != 0){
                        foreach ($images as $image) {
                            $this->deletefile($image->path);
                            $image->delete();
                        }
                    }
                    $offers = Offer::where('parent_type',$order->type)->where('parent_id',$order->id)->get();
                    if (count($offers) != 0){
                        foreach ($offers as $offer) {
                            $offer->delete();
                        }
                    }

                    $order->delete();
                }
                $orders = ShippingOrder::where('client_id',$client->id)->get();
                foreach ($orders as $order) {
                    $images = Image::where('parent_type','shipping_orders')->where('parent_id',$order->id)->get();

                    if (count($images) != 0){
                        foreach ($images as $image) {
                            $this->deletefile($image->path);
                            $image->delete();
                        }
                    }
                    $offers = Offer::where('parent_type',$order->type)->where('parent_id',$order->id)->get();
                    if (count($offers) != 0){
                        foreach ($offers as $offer) {
                            $offer->delete();
                        }
                    }
                    $addr = Address::find($order->to_address_id);
                    if ($addr){
                        $addr->delete();
                    }
                    $addr = Address::find($order->from_address_id);
                    if ($addr){
                        $addr->delete();
                    }
                    $order->delete();
                }
                $orders =  ItemOrder::where('client_id',$client->id)->get();
                foreach ($orders as $order) {
                    $images = Image::where('parent_type','service_orders')->where('parent_id',$order->id)->get();

                    if (count($images) != 0){
                        foreach ($images as $image) {
                            $this->deletefile($image->path);
                            $image->delete();
                        }
                    }
                    $offers = Offer::where('parent_type',$order->type)->where('parent_id',$order->id)->get();
                    if (count($offers) != 0){
                        foreach ($offers as $offer) {
                            $offer->delete();
                        }
                    }
                    $addr = Address::find($order->to_address_id);
                    if ($addr){
                        $addr->delete();
                    }

                    $order->delete();
                }

                $client->delete();

                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = Client::where('token',$request['token'])->first();
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "not found";
                $result['result'] = null;
            }




        }
        return response()->json($result, $result['statusCode']);
    }
    public function Edit(Request $request){
        $rules = [
            'token' => 'required',
            'phone' => 'required',
            'name' => 'required',
            'city_id' => 'required|exists:cities,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token',$request['token'])->first();
            if  ($client->phone != $request['phone']){
                $phone = Client::where('phone',$request['phone'])->first();
                if  ($phone){
                    $result['statusCode'] = 401;
                    $result['message'] = "Номер занят";
                    $result['result'] = [];

                    return response()->json($result, $result['statusCode']);
                }
            }
            if ($client){
                $client->phone = $request['phone'];
                $client->email= $request['email'];
                $client->name= $request['name'];
                $client->city_id = $request['city_id'];
                $client->iin = $request['iin'];
                $client->description = $request['description'];
                if (isset($request['avatar'])){
                    $client->avatar = $this->uploadfile($request['avatar']);
                }
                $client->save();

                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $this->GetClient($client->id)['result'];
            }

            else{
                $result['statusCode'] = 404;
                $result['message'] = "user not found";
                $result['result'] = [];
            }


        }

        return response()->json($result, $result['statusCode']);
    }
    public function Score(){
        $now_date = Carbon::now();

        foreach (City::all() as $city){
            $count = DB::select("SELECT clients.ball FROM `clients` WHERE ball <> 0 AND city_id = $city->id GROUP BY `ball`");
            $clients = Client::where('ball','<>',0)->where('city_id',$city->idй)->orderBy('ball')->get();

            if (count($count) == 1){
                DB::table('clients')->where('ball','<>',0)->orderBy('ball','DESC')->update(['rang_id' => '2']);
            }
            else if (count($count) == 2) {
                $size_1 = round(count($clients) * 52.5 / 100);
                $size_2 = round(count($clients) * 47.5 / 100);
                foreach ($clients as $k=>$client) {
                    if  ($size_1 >= $k+1){
                        $client->rang_id = 2;
                    }
                    else{
                        $client->rang_id = 3;
                    }

                    $online_date = Carbon::parse($client->online_at);
                    $days = $now_date->diffInDays($online_date);
                    if ($days != 0){
                        for ($i = 0;$i < $days;$i++){
                            if ($client->ball >= 10){
                                $client->ball -=10;
                            }
                        }

                    }
                    $client->online_at = Carbon::now();
                    $client->save();

                }
            }
            else if (count($count) == 3) {
                $size_1 = round(count($clients) * 38.3 / 100);
                $size_2 = round(count($clients) * 33.3 / 100);
                $size_3 = round(count($clients) * 28.4 / 100);
                $size_2 = $size_2 + $size_1;
                $size_3 = $size_2 + $size_3;

                foreach ($clients as $k=>$client) {
                    if  ($size_1 >= $k+1){
                        $client->rang_id = 2;
                    }
                    else if($size_2 >= $k+1){
                        $client->rang_id = 3;
                    }
                    else{
                        $client->rang_id = 4;
                    }
                    $online_date = Carbon::parse($client->online_at);
                    $days = $now_date->diffInDays($online_date);
                    if ($days != 0){
                        for ($i = 0;$i < $days;$i++){
                            if ($client->ball >= 10){
                                $client->ball -=10;
                            }
                        }

                    }
                    $client->online_at = Carbon::now();
                    $client->save();

                }

            }
            else if(count($count) >=4) {
                $size_1 = round(count($clients) * 32.5/ 100);
                $size_2 = round(count($clients) * 27.5 / 100);
                $size_3 = round(count($clients) * 22.5 / 100);
                $size_4 = round(count($clients) * 17.5/ 100);

                $size_2 = $size_2 + $size_1;
                $size_3 = $size_2 + $size_3;
                foreach ($clients as $k=>$client) {
                    if  ($size_1 >= $k+1){
                        $client->rang_id = 2;
                    }
                    else if($size_2 >= $k+1){
                        $client->rang_id = 3;
                    }
                    else if($size_3 >= $k+1){
                        $client->rang_id = 4;
                    }
                    else{
                        $client->rang_id = 5;
                    }
                    $online_date = Carbon::parse($client->online_at);
                    $days = $now_date->diffInDays($online_date);
                    if ($days != 0){
                        for ($i = 0;$i < $days;$i++){
                            if ($client->ball >= 10){
                                $client->ball -=10;
                            }
                        }

                    }
                    $client->online_at = Carbon::now();
                    $client->save();

                }

            }
        }

        return 1;
    }

    public function FeedbackCreate(Request $request){
        $rules = [
            'token' => 'required',
            'text' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token',$request['token'])->first();
            if  ($client){

                $feedback = new Feedback();
                $feedback->user_id = $client->id;
                $feedback->role = 1;
                $feedback->text = $request['text'];
                $feedback->save();



                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $feedback;
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "client not found";
                $result['result'] = null;
            }

        }
        return response()->json($result, $result['statusCode']);
    }
    public function FeedbackList(Request $request){
        $rules = [
            'token' => 'required'
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token',$request['token'])->first();
            if  ($client){
                $feedback = Feedback::where('user_id',$client->id)
                ->whereIn('role',[1,3])
                ->get();

                if(count($feedback) != 0){
                    $result['statusCode'] = 200;
                    $result['message'] = "success";
                    $result['result'] = $feedback;
                }
                else{
                    $result['statusCode'] = 404;
                    $result['message'] = "not found";
                    $result['result'] = [];
                }
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "not found";
                $result['result'] = [];
            }

        }
        return response()->json($result, $result['statusCode']);
    }

    /**************************************Service Order***********************************************/
    public function CreateServiceOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'transport_id' => 'required|exists:transports,id',
            'transport_type_id' => 'required|exists:transport_types,id',
            'date_1' => 'required',
            'hour' => 'required',
            'price' => 'required',
            'to_lat' => 'required',
            'to_lng' => 'required',
            'to_text' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $order = new ServiceOrder();
            $order->transport_id = $request['transport_id'];
            $order->transport_type_id = $request['transport_type_id'];
            $order->date_1 = Carbon::parse($request['date_1']);
            $order->hour = $request['hour'];
            $order->price = $request['price'];
            $order->description = $request['description'];
            $order->type = 'service_orders';
            $order->client_id = Client::where('token',$request['token'])->first()->id;


            $to_addr = new Address();
            $to_addr->text = $request['to_text'];
            $to_addr->lat = $request['to_lat'];
            $to_addr->lng = $request['to_lng'];
            $to_addr->save();
            $order->to_address_id = $to_addr->id;

            $order->save();



            if (isset($request['images'])){
                foreach ($request['images'] as $img){
                    $image = new Image();
                    $image->parent_id = $order->id;
                    $image->parent_type = 'service_orders';
                    $image->path = $this->uploadfile($img);
                    $image->save();
                }
            }

            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = [];

            /* Push*/
//                $arr['id'] = $order->id;
//                $arr['transport_id'] = $order->transport_id;
//                $arr['price'] = $order->price;
//                $arr['description'] = $order->description;
//                $arr['type'] = $order->type;
//                $arr['client_id'] = $order->client_id;
//
//                $push = new Push();
//                $push->List($arr);
            /* Push*/
        }
        return response()->json($result, $result['statusCode']);
    }
    public function PriceServiceOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'order_id' => 'required',
            'price' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {

            $order = ServiceOrder::find($request['order_id']);

            if ($order){
                $order->price = $request['price'];
                $order->save();
                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = [];
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "order not found";
                $result['result'] = [];
            }

        }
        return response()->json($result, $result['statusCode']);
    }
    public function AcceptServiceOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'offer_id' => 'required|exists:offers,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $offer = Offer::find($request['offer_id']);
            $order = ServiceOrder::find($offer->parent_id);
            $driver = Driver::find($offer->driver_id);
            if ($order and $driver){

                Offer::where('parent_type',$offer->parent_type)
                    ->where('parent_id',$offer->parent_id)
                    ->where('id','!=',$offer->id)
                    ->where('step',1)
                    ->delete();

                Offer::where('driver_id',$offer->driver_id)
                    ->where('id','!=',$offer->id)
                    ->where('step',1)
                    ->delete();



                $Commission = Other::find(1);


                if (Carbon::parse($driver->package_end)->lt(Carbon::now()) ){
                    $driver->balance -= (($order->price * $Commission->value) / 100);
                    $driver->save();
                }

                $order->step = 2;
                $order->work_start = Carbon::now();
                $order->save();

                $offer->step = 2;
                $offer->save();

                $drivers =  DB::table('service_drivers')->where('service_order_id',$order->id)->where('driver_id',$driver->id)->first();

                if (!$drivers){
                    DB::table('service_drivers')->insert(['service_order_id'=> $order->id,'driver_id'=>$driver->id]);
                }



                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $order;
                $push = new Push();

                $driver = Driver::find($offer->driver_id);
                $push->Access(
                    $driver->token,
                    $order->type,
                    $order->id,
                    $offer->id,
                    $order->client_id
                );
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "Order not found";
                $result['result'] = null;
            }
        }
        return response()->json($result, $result['statusCode']);
    }
    public function DeleteServiceOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'order_id' => 'required|exists:service_orders,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $order = ServiceOrder::find($request['order_id']);
            $client = Client::where('token',$request['token'])->first();
            if ($order->client_id == $client->id){
                $images = Image::where('parent_type','service_orders')->where('parent_id',$order->id)->get();

                if (count($images) != 0){
                    foreach ($images as $image) {
                        $this->deletefile($image->path);
                        $image->delete();
                    }
                }

                

                $offers = Offer::where('parent_type',$order->type)->where('parent_id',$order->id)->get();
                if (count($offers) != 0){
                    foreach ($offers as $offer) {
                        $offer->delete();
                    }
                }
                $addr = Address::find($order->to_address_id);
                if ($addr){
                    $addr->delete();
                }
                $addr = Address::find($order->from_address_id);
                if ($addr){
                    $addr->delete();
                }
                $order->delete();

                $result['statusCode'] = 200;
                $result['message'] = 'Order delete';
                $result['result'] = [];
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = 'Order not found';
                $result['result'] = null;
            }
        }

        return response()->json($result, $result['statusCode']);
    }
    public function CancelServiceOffer(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'offer_id' => 'required|exists:offers,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $offer = Offer::find($request['offer_id']);
            $order = ServiceOrder::find($offer->parent_id);
            $driver = Driver::find($offer->driver_id);
            if ($order and $driver){

                $push = new Push();
                $push->OfferCancel(
                    $driver->token,
                    $order->type,
                    $order->id,
                    $offer->id,
                    $order->client_id
                );
                $offer->delete();
                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $order;
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "Order not found";
                $result['result'] = null;
            }
        }
        return response()->json($result, $result['statusCode']);
    }
    public function EndServiceOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'order_id' => 'required|exists:service_orders,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $order = ServiceOrder::find($request['order_id']);
            if ($order){
                $order->step = 3;
                $order->work_end = Carbon::now();
                $order->save();

                $service_drivers = DB::table('service_drivers')
                    ->where('service_drivers.service_order_id',$order->id)
                    ->get();
                foreach ($service_drivers as $service_driver) {
                    $driver = Driver::find($service_driver->driver_id);

                    if ($driver){
                        $push = new Push();
                        $push->End(
                            $driver->token,
                            $order->type,
                            $order->id,
                            $order->client_id
                        );

                        $start = Carbon::parse($order->work_start);
                        $end = Carbon::parse($order->work_end);
                        $l = $start->diffInHours($end);
                        $driver->worked_hour += $l;
                        $driver->save();


                        $h = new History();
                        $h->parent_id = $order->id;
                        $h->parent_type = $order->type;
                        $h->user_id = $driver->id;
                        $h->role = 2;
                        $h->save();



                    }
                }





                $h = new History();
                $h->parent_id = $order->id;
                $h->parent_type = $order->type;
                $h->user_id = $order->client_id;
                $h->role = 1;
                $h->save();

                $push = new Push();
                $push->End(
                    $driver->token,
                    $order->type,
                    $order->id,
                    $order->client_id
                );

                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $order;
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "Order not found";
                $result['result'] = null;
            }
        }
        return $result;
    }

    /************************************** Shipping Order***********************************************/
    public function CreateShippingOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'transport_id' => 'required|exists:transports,id',
            'date' => 'required',
            'price' => 'required',
            'size' => 'required',
            'weight' => 'required',

            'to' => 'required|min:0|array',
            'from' => 'required',
            'from_city_id' => 'required',
            'options' => 'array',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $order = new ShippingOrder();
            $order->date = Carbon::parse($request['date']);
            $order->price = $request['price'];
            $order->description = $request['description'];
            $order->transport_id = $request['transport_id'];
            $order->type = 'shipping_orders';
            $order->client_id = Client::where('token',$request['token'])->first()->id;
            $order->size = $request['size'];
            $order->weight = $request['weight'];
            $order->from = $request['from'];
            $order->from_city_id = $request['from_city_id'];
            $order->save();

            $insr = [];
            foreach ($request['to'] as $to){
                $t['shipping_order_id'] = $order->id;
                $t['to'] = $to['to'];
                $t['city_id'] = $to['city_id'];
                $insr[]= $t;
            }
            DB::table('shipping_order_to')->insert($insr);


            if (isset($request['images'])){
                foreach ($request['images'] as $img){
                    $image = new Image();
                    $image->parent_id = $order->id;
                    $image->parent_type = 'shipping_orders';
                    $image->path = $this->uploadfile($img);
                    $image->save();
                }
            }
            if (isset($request['options'])){
                foreach ($request['options'] as $id) {
                    if ($id){
                        $option = new ShippingOption();
                        $option->shipping_order_id = $order->id;
                        $option->option_id = $id;
                        $option->save();
                    }
                }
            }

            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = [];

            /* Push*/
//            $arr['id'] = $order->id;
//            $arr['transport_id'] = $order->transport_id;
//            $arr['price'] = $order->price;
//            $arr['description'] = $order->description;
//            $arr['type'] = $order->type;
//            $arr['client_id'] = $order->client_id;
//            $arr['from_city_id'] = $order->from_city_id;
//
//            $push = new Push();
//            $push->List($arr);
            /* Push*/
        }
        return $result;
    }
    public function PriceShippingOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'order_id' => 'required',
            'price' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {

            $order = ShippingOrder::find($request['order_id']);

            if ($order){
                $order->price = $request['price'];
                $order->save();
                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = [];
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "order not found";
                $result['result'] = [];
            }

        }
        return response()->json($result, $result['statusCode']);
    }
    public function AcceptShippingOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'offer_id' => 'required|exists:offers,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $offer = Offer::find($request['offer_id']);
            $order = ShippingOrder::find($offer->parent_id);
            $driver = Driver::find($offer->driver_id);
            if ($order and $driver){
                Offer::where('parent_type',$offer->parent_type)
                    ->where('parent_id',$offer->parent_id)
                    ->where('id','!=',$offer->id)
                    ->where('step',1)
                    ->delete();

                Offer::where('driver_id',$offer->driver_id)
                    ->where('id','!=',$offer->id)
                    ->where('step',1)
                    ->delete();




                $Commission = Other::find(1);
                if (Carbon::parse($driver->package_end)->lt(Carbon::now()) ){
                    $driver->balance -= (($order->price * $Commission->value) / 100);
                    $driver->save();
                }
                if ($order->step == 1){
                     $order->step = 2;
                     $order->work_start = Carbon::now();
                     $order->save();
                 }

                 $offer->step = 2;
                 $offer->save();
                $drivers =  DB::table('shipping_drivers')->where('shipping_order_id',$order->id)->where('driver_id',$driver->id)->first();

                if (!$drivers){
                    DB::table('shipping_drivers')->insert(['shipping_order_id'=> $order->id,'driver_id'=>$driver->id]);
                }



                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $order;

                $push = new Push();

                $driver = Driver::find($offer->driver_id);
                $push->Access(
                    $driver->token,
                    $order->type,
                    $order->id,
                    $offer->id,
                    $order->client_id
                );
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "Order not found";
                $result['result'] = null;
            }
        }
        return $result;
    }
    public function DeleteShippingOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'order_id' => 'required|exists:shipping_orders,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $order = ShippingOrder::find($request['order_id']);
            $client = Client::where('token',$request['token'])->first();
            if ($order->client_id == $client->id){
                $images = Image::where('parent_type','shipping_orders')->where('parent_id',$order->id)->get();

                if (count($images) != 0){
                    foreach ($images as $image) {
                        $this->deletefile($image->path);
                        $image->delete();
                    }
                }
                $offers = Offer::where('parent_type',$order->type)->where('parent_id',$order->id)->get();
                if (count($offers) != 0){
                    foreach ($offers as $offer) {
                        $offer->delete();
                    }
                }

                $order->delete();

                $result['statusCode'] = 200;
                $result['message'] = 'Order delete';
                $result['result'] = [];
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = 'Order not found';
                $result['result'] = null;
            }
        }

        return $result;
    }
    public function CancelShippingOffer(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'offer_id' => 'required|exists:offers,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $offer = Offer::find($request['offer_id']);
            if ($offer){
                $order = ShippingOrder::find($offer->parent_id);
                if ($order){
                    $driver = Driver::find($offer->driver_id);
                    if($driver){
                        $push = new Push();
                        $push->OfferCancel(
                            $driver->token,
                            $order->type,
                            $order->id,
                            $offer->id,
                            $order->client_id
                        );
                        $offer->delete();
                        $result['statusCode'] = 200;
                        $result['message'] = "success";
                        $result['result'] = $order;
                    }
                    else{
                        $result['statusCode'] = 404;
                        $result['message'] = "Driver not found";
                        $result['result'] = [];
                    }
                }
                else{
                    $result['statusCode'] = 404;
                    $result['message'] = "Order not found";
                    $result['result'] = [];
                }
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "offer not found";
                $result['result'] = [];
            }
        }
        return response()->json($result, $result['statusCode']);
    }
    public function EndShippingOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'order_id' => 'required|exists:shipping_orders,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $order = ShippingOrder::find($request['order_id']);
            if ($order){
                $order->step = 3;
                $order->work_end = Carbon::now();
                $order->save();

                $shipping_drivers = DB::table('shipping_drivers')
                    ->where('shipping_drivers.shipping_order_id',$order->id)
                    ->get();
                foreach ($shipping_drivers as $shipping_driver) {
                    $driver = Driver::find($shipping_driver->driver_id);

                    if ($driver){
                        $push = new Push();
                        $push->End(
                            $driver->token,
                            $order->type,
                            $order->id,
                            $order->client_id
                        );

                        $start = Carbon::parse($order->work_start);
                        $end = Carbon::parse($order->work_end);
                        $l = $start->diffInHours($end);
                        $driver->worked_hour += $l;
                        $driver->save();


                        $h = new History();
                        $h->parent_id = $order->id;
                        $h->parent_type = $order->type;
                        $h->user_id = $driver->id;
                        $h->role = 2;
                        $h->save();



                    }
                }


                $h = new History();
                $h->parent_id = $order->id;
                $h->parent_type = $order->type;
                $h->user_id = $order->client_id;
                $h->role = 1;
                $h->save();

                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $order;


            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "Order not found";
                $result['result'] = null;
            }
        }
        return $result;
    }

    /**************************************Item Order***********************************************/
    public function CreateItemOrder(Request $request){

        $rules = [
            'token' => 'required|exists:clients,token',
            'material_id' => 'required|exists:materials,id',
            'material_type_id' => 'required|exists:material_types,id',
            'count_type_id' => 'required|exists:count_types,id',
            'date' => 'required',
            'count' => 'required',
            'price' => 'required',
            'to_lat' => 'required',
            'to_lng' => 'required',
            'to_text' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $order = new ItemOrder();
            $order->material_id = $request['material_id'];
            $order->material_type_id = $request['material_type_id'];
            $order->count_type_id = $request['count_type_id'];
            $order->count = $request['count'];
            $order->date = Carbon::parse($request['date']);
            $order->price = $request['price'];
            $order->description = $request['description'];
            $order->type = 'item_orders';
            $order->client_id = Client::where('token',$request['token'])->first()->id;


            $to_addr = new Address();
            $to_addr->text = $request['to_text'];
            $to_addr->lat = $request['to_lat'];
            $to_addr->lng = $request['to_lng'];
            $to_addr->save();
            $order->to_address_id = $to_addr->id;


            $order->save();
            if (isset($request['images'])){
                foreach ($request['images'] as $img){
                    $image = new Image();
                    $image->parent_id = $order->id;
                    $image->parent_type = 'item_orders';
                    $image->path = $this->uploadfile($img);
                    $image->save();
                }
            }


            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = [];

            /* Push*/
//            $arr['id'] = $order->id;
//            $arr['material_id'] = $order->material_id;
//            $arr['price'] = $order->price;
//            $arr['description'] = $order->description;
//            $arr['type'] = $order->type;
//            $arr['client_id'] = $order->client_id;
//
//            $push = new Push();
//            $push->List($arr);
            /* Push*/
        }
        return $result;
    }
    public function PriceItemOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'order_id' => 'required',
            'price' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {

            $order = ItemOrder::find($request['order_id']);

            if ($order){
                $order->price = $request['price'];
                $order->save();
                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = [];
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "order not found";
                $result['result'] = [];
            }

        }
        return response()->json($result, $result['statusCode']);
    }
    public function AcceptItemOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'offer_id' => 'required|exists:offers,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $offer = Offer::find($request['offer_id']);
            $order = ItemOrder::find($offer->parent_id);
            $driver = Driver::find($offer->driver_id);
            if ($order and $driver){
                Offer::where('parent_type',$offer->parent_type)
                    ->where('parent_id',$offer->parent_id)
                    ->where('id','!=',$offer->id)
                    ->where('step',1)
                    ->delete();

                Offer::where('driver_id',$offer->driver_id)
                    ->where('id','!=',$offer->id)
                    ->where('step',1)
                    ->delete();


                $Commission = Other::find(1);

                if (Carbon::parse($driver->package_end)->lt(Carbon::now()) ){
                    $driver->balance -= (($order->price * $Commission->value) / 100);
                    $driver->save();
                }
                $order->step = 2;
                $order->work_start = Carbon::now();
                $order->save();


                $offer->step = 2;
                $offer->save();

                $drivers =  DB::table('item_drivers')->where('item_order_id',$order->id)->where('driver_id',$driver->id)->first();

                if (!$drivers){
                    DB::table('item_drivers')->insert(['item_order_id'=> $order->id,'driver_id'=>$driver->id]);
                }


                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $order;

                $push = new Push();

                $driver = Driver::find($offer->driver_id);
                $push->Access(
                    $driver->token,
                    $order->type,
                    $order->id,
                    $offer->id,
                    $order->client_id
                );
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "Order not found";
                $result['result'] = null;
            }
        }
        return $result;
    }
    public function DeleteItemOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'order_id' => 'required|exists:item_orders,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $order = ItemOrder::find($request['order_id']);
            $client = Client::where('token',$request['token'])->first();
            if ($order->client_id == $client->id){
                $images = Image::where('parent_type','service_orders')->where('parent_id',$order->id)->get();

                if (count($images) != 0){
                    foreach ($images as $image) {
                        $this->deletefile($image->path);
                        $image->delete();
                    }
                }
                $offers = Offer::where('parent_type',$order->type)->where('parent_id',$order->id)->get();
                if (count($offers) != 0){
                    foreach ($offers as $offer) {
                        $offer->delete();
                    }
                }
                $addr = Address::find($order->to_address_id);
                if ($addr){
                    $addr->delete();
                }

                $order->delete();

                $result['statusCode'] = 200;
                $result['message'] = 'Order delete';
                $result['result'] = [];
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = 'Order not found';
                $result['result'] = null;
            }
        }

        return $result;
    }
    public function CancelItemOffer(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'offer_id' => 'required|exists:offers,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $offer = Offer::find($request['offer_id']);
            $order = ItemOrder::find($offer->parent_id);
            $driver = Driver::find($offer->driver_id);
            if ($order and $driver){

                $push = new Push();
                $push->OfferCancel(
                    $driver->token,
                    $order->type,
                    $order->id,
                    $offer->id,
                    $order->client_id
                );
                $offer->delete();
                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $order;
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "Order not found";
                $result['result'] = null;
            }
        }
        return response()->json($result, $result['statusCode']);
    }
    public function EndItemOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'order_id' => 'exists:item_orders,id',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $order = ItemOrder::find($request['order_id']);
            if ($order){
                $order->step = 3;
                $order->work_end = Carbon::now();
                $order->save();




                $item_drivers = DB::table('item_drivers')
                    ->where('item_drivers.item_order_id',$order->id)
                    ->get();
               if (count($item_drivers) > 0){
                   foreach ($item_drivers  as $item_driver) {
                       $driver = Driver::find($item_driver->driver_id);

                       if ($driver){
                           $push = new Push();
                           $push->End(
                               $driver->token,
                               $order->type,
                               $order->id,
                               $order->client_id
                           );

                           $start = Carbon::parse($order->work_start);
                           $end = Carbon::parse($order->work_end);
                           $l = $start->diffInHours($end);
                           $driver->worked_hour += $l;
                           $driver->save();

                           $h = new History();
                           $h->parent_id = $order->id;
                           $h->parent_type = $order->type;
                           $h->user_id = $driver->id;
                           $h->role = 2;
                           $h->save();




                           $push = new Push();
                           $push->End(
                               $driver->token,
                               $order->type,
                               $order->id,
                               $order->client_id
                           );
                       }
                   }
               }


                $h = new History();
                $h->parent_id = $order->id;
                $h->parent_type = $order->type;
                $h->user_id = $order->client_id;
                $h->role = 1;
                $h->save();



                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $order;


            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "Order not found";
                $result['result'] = null;
            }
        }
        return $result;
    }

    public function ActiveOrder(Request $request){
        $rules = [
            'page' => 'required',
            'token' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token',$request['token'])->first();
            if (!$client){
                $result['statusCode']= 404;
                $result['message']= 'User not found';
                $result['result']= [];
            }
            else{
                $sql = <<<STR
(SELECT service_orders.id ,service_orders.type,service_orders.created_at
    FROM `service_orders`
    WHERE service_orders.client_id = $client->id
    AND service_orders.step = 1 
    OR  service_orders.step = 2
)
UNION ALL
(SELECT  item_orders.id ,item_orders.type,item_orders.created_at
    FROM  `item_orders`
    WHERE item_orders.client_id = $client->id
    AND item_orders.step = 1 OR  item_orders.step = 2
)
UNION ALL
(SELECT shipping_orders.id ,shipping_orders.type,shipping_orders.created_at
    FROM `shipping_orders`
    WHERE shipping_orders.client_id = $client->id
    AND shipping_orders.step = 1 OR  shipping_orders.step = 2
)
Order by created_at DESC
LIMIT 10
OFFSET $request->page
STR;

                $orders = DB::select($sql);
                $count = count($orders);
                if ($count != 0) {
                    $arr = [];

                    foreach ($orders as $order) {
                        if ($order->type == 'service_orders') {
                            $temp = $this->GetServiceOrder($order->id);
                            if ($temp['statusCode'] == 200) {
                                $arr[] = $temp['result'];
                            }
                        } else if ($order->type == 'shipping_orders') {
                            $temp = $this->GetShippingOrder($order->id);
                            if ($temp['statusCode'] == 200) {
                                $arr[] = $temp['result'];
                            }
                        } else if ($order->type == 'item_orders') {
                            $temp = $this->GetItemOrder($order->id);
                            if ($temp['statusCode'] == 200) {
                                $arr[] = $temp['result'];
                            }
                        }
                    }

                    $result['statusCode'] = 200;
                    $result['message'] = 'success';
                    $result['page'] = $request->page;
                    $result['result'] = $arr;
                }
                else{
                    $result['statusCode'] = 404;
                    $result['message'] = 'not found';
                    $result['page'] = $request->page;
                    $result['result'] = [];
                }
            }

        }
        return $result;
    }


    public function HistoryOrder(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token',$request['token'])->first();
            if ($client){
                $histories = History::where('user_id',$client->id)->where('role',1)->get();
                if  (count($histories) != 0){
                    $arr = [];
                    foreach ($histories as $history) {
                        if ($history->parent_type == 'service_orders'){
                            $temp = $this->GetServiceOrder($history->parent_id);
                            if ($temp['statusCode'] == 200){
                                $arr[] = $temp['result'];
                            }
                        }
                        else if ($history->parent_type == 'shipping_orders'){
                            $temp = $this->GetShippingOrder($history->parent_id);
                            if ($temp['statusCode'] == 200){
                                $arr[] = $temp['result'];
                            }
                        }
                        else if ($history->parent_type == 'item_orders'){
                            $temp = $this->GetItemOrder($history->parent_id);
                            if ($temp['statusCode'] == 200){
                                $arr[] = $temp['result'];
                            }
                        }
                    }


                    if (count($arr) != 0){
                        $result['statusCode'] = 200;
                        $result['message'] = 'success';
                        $result['result'] = $arr;
                    }
                    else{
                        $result['statusCode'] = 404;
                        $result['message'] = 'histories not found';
                        $result['result'] = [];
                    }
                }
                else{
                    $result['statusCode'] = 404;
                    $result['message'] = 'histories not found';
                    $result['result'] = [];
                }
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = 'user not found';
                $result['result'] = [];
            }
        }

        return $result;
    }
    public function ReviewCreate(Request $request){
        $rules = [
            'token' => 'required|exists:clients,token',
            'driver_id' => 'required|exists:drivers,id',
            'speed' => 'required|integer|min:1|max:5',
            'punctuality' => 'required|integer|min:1|max:5',
            'quality' => 'required|integer|min:1|max:5',
            'price' => 'required|integer|min:1|max:5',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token', $request['token'])->first();
            $reviews = Review::where('client_id', $client->id)->where('driver_id', $request['driver_id'])->first();
            if (true) {
                $review = new Review();
                $review->driver_id = $request['driver_id'];
                $review->speed = $request['speed'];
                $review->punctuality = $request['punctuality'];
                $review->quality = $request['quality'];
                $review->price = $request['price'];
                $review->client_id = $client->id;
                $review->description = $request['description'];
                $review->save();

                $client->ball += 50;
                if  (isset( $request['description'])){
                    $client->ball += 50;
                }

                $result['statusCode'] = 200;
                $result['message'] = 'success';
                $result['result'] = $review;
            }
            else {
                $result['statusCode'] = 401;
                $result['message'] = 'review exist';
                $result['result'] = $reviews;
            }
        }

        return $result;
    }


    /**************************************Chat***********************************************/
    public function ChatSendMessage(Request $request){
        $rules = [
            'token' => 'required',
            'driver_id' => 'required',
            'text' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token',$request['token'])->first();
            if ($client){
                $driver = Driver::find($request['driver_id']);
                if ($driver){
                    $chat = Chat::where('driver_id',$driver->id)->where('client_id',$client->id)->first();

                    if  ($chat){
                        $msg = new ChatMessage();
                        $msg->role = 'client';
                        $msg->user_id = $client->id;
                        $msg->text = $request['text'];
                        $msg->chat_id = $chat->id;
                        $msg->save();

                        $msg->user = $client;

                        $chat->updated_at = Carbon::now();
                        $chat->save();
                    } else{
                        $chat = new Chat();
                        $chat->client_id = $client->id;
                        $chat->driver_id = $client->driver_id;
                        $chat->save();

                        $msg = new ChatMessage();
                        $msg->role = 'client';
                        $msg->user_id = $client->id;
                        $msg->text = $request['text'];
                        $msg->chat_id = $chat->id;
                        $msg->save();

                        $msg->user = $client;
                    }

                    if ($driver->online == 0){
                        $push = new Push();
                        $push->NewMessage($driver->token,$request['text'],$chat->id,$client->id);
                    }


                    $result['statusCode']= 200;
                    $result['message']= 'success';
                    $result['result']= $msg;
                }else{
                    $result['statusCode']= 404;
                    $result['message']= 'client not found';
                    $result['result']= [];
                }
            }else{
                $result['statusCode']= 404;
                $result['message']= 'driver not found';
                $result['result']= [];
            }
        }
        return response()->json($result, $result['statusCode']);
    }
    public function Chats(Request $request){
        $rules = [
            'token' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            if (isset($request['token'])){
                $client = Client::where('token',$request['token'])->first();
            }  else{
                $client = Client::find($request['id']);
            }
            if ($client){
                $chats = Chat::where('client_id',$client->id)->orderBy('updated_at','desc')->get();
                if (count($chats) > 0){
                    $arr = [];
                    foreach ($chats as $chat) {
                        $t['id'] = $chat->id;
                        $t['driver'] = Driver::find($chat->driver_id);
                        $t['client'] = Client::find($chat->client_id);
                        $t['last_message'] = ChatMessage::where('chat_id',$chat->id)->orderBy("id",'desc')->first();
                        $arr[] =$t;
                    }

                    $result['statusCode']= 200;
                    $result['client']= $client;
                    $result['message']= 'success';
                    $result['result']= $arr;
                }else{
                    $result['statusCode']= 404;
                    $result['message']= 'chats not found';
                    $result['result']= [];
                }
            }else{
                $result['statusCode']= 404;
                $result['message']= '$client not found';
                $result['result']= [];
            }
        }
        return response()->json($result, $result['statusCode']);
    }
    public function Chat(Request $request){
        $rules = [
            'chat_id' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $chat = Chat::find($request['chat_id']);
            if ($chat){
                $client  = Client::find($chat->client_id);
                $driver = Driver::find($chat->driver_id);

                $limit = 20;
                $offset = ($request['page'] == 1 ) ? 0: $limit * ($request['page'] - 1);
                $count = ChatMessage::where('chat_id',$chat->id)->count();

                $msg =  ChatMessage::where('chat_id',$chat->id)->limit($limit)->offset($offset)->orderBy('id','desc')->get();

                $result['statusCode']= 200;
                $result['message'] = 'success';
                $result['result']['offset'] =  $offset;
                $result['result']['limit'] = $limit;
                $result['result']['count_products'] = $count;
                $result['result']['current_page'] = (int)$request['page'];
                $result['result']['count_pages'] = (int)ceil($count/$limit);
                $result['result']['data']  =  [];

                foreach ($msg as $m) {
                    $t['role'] = $m->role;
                    $t['text'] = $m->text;
                    if ($m->role == 'client'){
                        $t['client'] = $client;
                    }else{
                        $t['driver'] = $driver;
                    }
                    $result['result']['data'] [] = $t;
                }

            }else{
                $result['statusCode']= 404;
                $result['message']= 'chat not found';
                $result['result']= [];
            }
        }
        return response()->json($result, $result['statusCode']);
    }
    public function Online(Request $request){
        $rules = [
            'token' => 'required',
            'online' => 'required|in:1,0',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $client = Client::where('token',$request['token'])->first();
            if ($client){
                $client->online = $request['online'];
                $client->save();
                $result['statusCode']= 200;
                $result['message']= 'success';
                $result['result']=$client;
            }else{
                $result['statusCode']= 404;
                $result['message']= '$client not found';
                $result['result']= [];
            }
        }
        return response()->json($result, $result['statusCode']);
    }

    public function CreateChat(Request $request){
        $rules = [
            'token' => 'required',
            'driver_id' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::find($request['driver_id']);
            if ($driver){
                $client = Client::where('token',$request['token'])->first();
                if ($client){
                    $chat = Chat::where('driver_id',$driver->id)->where('client_id',$client->id)->first();
                    if  (!$chat){
                        $chat = new Chat();
                        $chat->client_id = $client->id;
                        $chat->driver_id = $driver->id;
                        $chat->save();
                    }
                    $result['statusCode']= 200;
                    $result['message']= 'success';
                    $result['result']= $chat;
                }else{
                    $result['statusCode']= 404;
                    $result['message']= 'client not found';
                    $result['result']= [];
                }
            }else{
                $result['statusCode']= 404;
                $result['message']= 'driver not found';
                $result['result']= [];
            }
        }
        return response()->json($result, $result['statusCode']);
    }
}
