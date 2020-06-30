<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarMaterial;
use App\Models\CarTransport;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\City;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Group;
use App\Models\History;
use App\Models\Image;
use App\Models\ItemOrder;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Other;
use App\Models\Participant;
use App\Models\Rang;
use App\Models\Review;
use App\Models\ServiceOrder;
use App\Models\Shared;
use App\Models\ShippingOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\PG_Signature;
use App\Models\Feedback;
use App\Push;
use mysql_xdevapi\Result;

class DriverController extends Controller
{
    public function Register(Request $request){

        $rules = [
            'phone' => 'required|unique:drivers',
            'city_id' => 'required|exists:cities,id',
            'name' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = new Driver();
            $driver->phone = $request['phone'];
            $driver->email= $request['email'];
            $driver->name= $request['name'];
            $driver->city_id= $request['city_id'];
            $driver->description = $request['description'];
            if (isset($request['avatar'])){
                $driver->avatar = $this->uploadfile($request['avatar']);
            }

            $driver->token = str_random(60);
            $driver->save();

            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = $this->GetDriver($driver->id)['result'];
        }
        return $result;
    }
    public function CreateCar(Request $request){
        $rules = [
            'name' => 'required',
            'info' => 'required',
            'number' => 'required',
            'car_transports' => 'required|array|min:1',
            'car_materials' => 'array',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            $car = new Car();
            $car->name = $request['name'];
            $car->driver_id = $driver->id;
            $car->info = $request['info'];
            $car->number = $request['number'];
            $car->save();

            if (count($request['car_transports']) != 0){
                foreach ($request['car_transports'] as $id) {
                    $carTransport = new CarTransport();
                    $carTransport->car_id = $car->id;
                    $carTransport->transport_id = $id;
                    $carTransport->save();
                }
            }
            if (isset($request['car_materials'])){
                if (count($request['car_materials']) != 0){
                    foreach ($request['car_materials'] as $id) {
                        $carMaterial = new CarMaterial();
                        $carMaterial->car_id = $car->id;
                        $carMaterial->material_id = $id;
                        $carMaterial->save();
                    }
                }
            }
            if (isset($request['images'])){
                if (count($request['images']) != 0){
                    foreach ($request['images'] as $img) {
                        $image = new Image();
                        $image->parent_type = 'car';
                        $image->parent_id= $car->id;
                        $image->path = $this->uploadfile($img);
                        $image->save();
                    }
                }
            }




            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = $driver;
        }
        return $result;
    }
    public function EditCar(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'car_id' => 'required',
            'name' => 'required',
            'info' => 'required',
            'number' => 'required',
            'car_transports' => 'array',
            'car_materials' => 'array',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            $car = Car::find($request['car_id']);
            $car->name = $request['name'];
            $car->driver_id = $driver->id;
            $car->info = $request['info'];
            $car->number = $request['number'];
            $car->save();
            if (count($request['car_transports']) != 0){
                foreach (CarTransport::where('car_id',$car->id)->get() as $ct){
                    $ct->delete();
                }
                foreach ($request['car_transports'] as $id) {
                    $carTransport = new CarTransport();
                    $carTransport->car_id = $car->id;
                    $carTransport->transport_id = $id;
                    $carTransport->save();
                }
            }
            else{
                foreach (CarTransport::where('car_id',$car->id)->get() as $ct){
                    $ct->delete();
                }
            }
            if (isset($request['car_materials'])){
                if (count($request['car_materials']) != 0){
                    foreach (CarMaterial::where('car_id',$car->id)->get() as $ct){
                        $ct->delete();
                    }
                    foreach ($request['car_materials'] as $id) {
                        $carMaterial = new CarMaterial();
                        $carMaterial->car_id = $car->id;
                        $carMaterial->material_id = $id;
                        $carMaterial->save();
                    }
                }
            }
            else{
                foreach (CarMaterial::where('car_id',$car->id)->get() as $ct){
                    $ct->delete();
                }
            }
            if (isset($request['images'])){
                if (count($request['images']) != 0){
                    foreach (Image::where('parent_type','car')->where('parent_id',$car->id)->get() as $ct){
                        $this->deletefile($ct->path);
                        $ct->delete();
                    }
                    foreach ($request['images'] as $img) {
                        $image = new Image();
                        $image->parent_type = 'car';
                        $image->parent_id= $car->id;
                        $image->path = $this->uploadfile($img);
                        $image->save();
                    }
                }
            }




            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = $driver;
        }
        return $result;
    }
    public function DeleteCar(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'car_id' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            $car = Car::find($request['car_id']);

            foreach (CarTransport::where('car_id',$car->id)->get() as $ct){
                $ct->delete();
            }
            foreach (CarMaterial::where('car_id',$car->id)->get() as $ct){
                $ct->delete();
            }
            foreach (Image::where('parent_type','car')->where('parent_id',$car->id)->get() as $ct){
                $this->deletefile($ct->path);
                $ct->delete();
            }
            $car->delete();

            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = $driver;
        }
        return $result;
    }
    public function Verification(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'passport_images' => 'required|array|min:3',
            'ip' => 'required',
            'iin' => 'required',
            'passport' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            $driver->ip = $request['ip'];
            $driver->iin =$request['iin'];
            $driver->passport =$request['passport'];
            if (isset($request['passport_images'])){
                if (count($request['passport_images']) != 0){
                    foreach ($request['passport_images'] as $img) {
                        $image = new Image();
                        $image->parent_type = 'passport';
                        $image->parent_id= $driver->id;
                        $image->path = $this->uploadfile($img);
                        $image->save();
                    }
                }
            }
            $driver->save();


            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = $driver;
        }
        return $result;
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
            $client = Driver::where('phone',$request['phone'])->first();
            if  ($client){
                if ($client->access == 0){
                    $date = Carbon::parse($client->access_date);
                    if ($date->lte(Carbon::now())){
                        $client->access = 1;
                        $client->access_date = null;
                        $client->save();

                        $result['statusCode'] = 200;
                        $result['message'] = "success";
                        $result['result'] = $this->GetDriver($client->id)['result'];
                    }
                    else{
                        $result['statusCode'] = 405;
                        $result['message'] = "no access";
                        $result['result'] = $this->GetDriver($client->id)['result'];
                    }
                }
                else{
                    $result['statusCode'] = 200;
                    $result['message'] = "success";
                    $result['result'] = $this->GetDriver($client->id)['result'];
                }
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "driver not found";
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
            $client = Driver::where('token',$request['token'])->first();
            if  ($client){
                if ($client->access == 0){
                    $date = Carbon::parse($client->access_date);
                    if ($date->lte(Carbon::now())){
                        $client->access = 1;
                        $client->access_date = null;
                        $client->save();

                        $result['statusCode'] = 200;
                        $result['message'] = "success";
                        $result['result'] = $this->GetDriver($client->id)['result'];
                    }
                    else{
                        $result['statusCode'] = 405;
                        $result['message'] = "no access";
                        $result['result'] = $this->GetDriver($client->id)['result'];
                    }
                }
                else{
                    $result['statusCode'] = 200;
                    $result['message'] = "success";
                    $result['result'] = $this->GetDriver($client->id)['result'];
                }
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "driver not found";
                $result['result'] = null;
            }

        }
        return response()->json($result, $result['statusCode']);
    }
    public function Edit(Request $request){

        $rules = [
            'token' => 'required|exists:drivers,token',
            'phone' => 'required',
            'city_id' => 'required|exists:cities,id',
            'name' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            if  ($driver->phone != $request['phone']){
                $phone = Driver::where('phone',$request['phone'])->first();
                if  ($phone){
                    $result['statusCode'] = 401;
                    $result['message'] = "Номер занят";
                    $result['result'] = [];

                    return response()->json($result, $result['statusCode']);
                }
            }
            $driver->phone = $request['phone'];
            $driver->email= $request['email'];
            $driver->name= $request['name'];
            $driver->city_id= $request['city_id'];
            $driver->description = $request['description'];
            if (isset($request['avatar'])){
                $driver->avatar = $this->uploadfile($request['avatar']);
            }
            $driver->save();

            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = $this->GetDriver($driver->id)['result'];
        }
        return $result;
    }
    public function Position(Request $request){
        $rules = [
            'token' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();

            if ($driver){
                $driver->lat = $request['lat'];
                $driver->lng = $request['lng'];
                $driver->save();
                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $driver;
            }

            else{
                $result['statusCode'] = 404;
                $result['message'] = "user not found";
                $result['result'] = [];
            }


        }

        return response()->json($result, $result['statusCode']);
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
            $driver= Driver::where('token',$request['token'])->first();
            if  ($driver){

                $feedback = new Feedback();
                $feedback->user_id = $driver->id;
                $feedback->role = 2;
                $feedback->text = $request['text'];
                $feedback->save();



                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] = $feedback;
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "driver not found";
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
            $client = Driver::where('token',$request['token'])->first();
            if  ($client){

                $feedback = Feedback::where('user_id',$client->id)
                    ->whereIn('role',[2,3])
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

    public function SharedCreate(Request $request){

        $rules = [
            'token' => 'required|exists:drivers,token',
            'social' => 'required|in:vk,facebook,instagram',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {

            $driver = Driver::where('token',$request['token'])->first();
            if ($driver){

                $shareds = Shared::where('role','driver')
                    ->where('user_id',$driver->id)
                    ->where('social',$request['social'])
                    ->whereRaw('DATE(created_at) > (NOW() - INTERVAL 1 DAY)')
                    ->get();
                if (count($shareds) == 0){

                    $sh = new Shared();
                    $sh->role = 'driver';
                    $sh->social = $request['social'];
                    $sh->user_id =$driver->id;
                    $sh->save();

                    $driver->balance += 500;
                    $driver->save();

                    $result['statusCode'] = 200;
                    $result['message'] = "success";
                    $result['result'] =[];
                }else{
                    $result['statusCode'] = 403;
                    $result['message'] = "вы уже поделились";
                    $result['result'] =$shareds;
                }
            }else{
                $result['statusCode'] = 200;
                $result['message'] = "success";
                $result['result'] =[];
            }

        }
        return $result;
    }


    /**************************************Service Order***********************************************/
    public function ServiceOfferCreate(Request $request){

        $rules = [
            'token' => 'required|exists:drivers,token',
            'order_id' => 'required|exists:service_orders,id',
            'description' => 'required',
            'price' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            $order = ServiceOrder::find($request['order_id']);
            $client = Client::find($order->client_id);
            $offers = Offer::where('parent_id',$request['order_id'])->where('driver_id',$driver->id)->where('parent_type','service_orders')->first();
            $package = Carbon::parse($driver->package_end)->lt(Carbon::now()) ;
            if (!$offers OR !$package){
                $Commission = Other::find(1);
                $num = $driver->balance - (($order->price * $Commission->value) / 100);
                if  ($num <= 0){
                    $result['statusCode'] = 402;
                    $result['message'] = "Баланста акша жок,кабылдай алмайсын";
                    $result['result'] = $num;
                }
                else{
                    $offer = new Offer();
                    $offer->driver_id = $driver->id;
                    $offer->parent_id = $request['order_id'];
                    $offer->parent_type = 'service_orders';
                    $offer->description = $request['description'];
                    $offer->price = $request['price'];
                    $offer->save();

                    $result['statusCode'] = 200;
                    $result['message'] = "success";
                    $result['result'] = $offer;

                    $push = new Push();
                    $push->OfferCreate($client->token,$order->type,$order->id,$offer->id,$driver->id);
                }
            }
            else{

                $result['statusCode'] = 401;
                $result['message'] = "offer exist";
                $result['result'] = $offers;
            }


        }
        return $result;
    }
    public function Score(){
        $now_date = Carbon::now();
        foreach (City::all() as $city){
           $count = DB::select("SELECT drivers.ball FROM `drivers` WHERE ball <> 0 AND verification = 1 AND city_id = $city->id GROUP BY `ball`");
           $clients = Driver::where('ball','<>',0)->where('city_id',$city->id)->orderBy('ball')->get();

           if (count($count) == 1){
               DB::table('drivers')->where('ball','<>',0)->orderBy('ball','DESC')->update(['rang_id' => '7']);
           }
           else if (count($count) == 2) {
               $size_1 = round(count($clients) * 52.5 / 100);
               $size_2 = round(count($clients) * 47.5 / 100);
               foreach ($clients as $k=>$client) {
                   if  ($size_1 >= $k+1){
                       $client->rang_id = 7;
                   }
                   else{
                       $client->rang_id = 8;
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
                       $client->rang_id =7;
                   }
                   else if($size_2 >= $k+1){
                       $client->rang_id = 8;
                   }
                   else{
                       $client->rang_id = 9;
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
                       $client->rang_id = 7;
                   }
                   else if($size_2 >= $k+1){
                       $client->rang_id = 8;
                   }
                   else if($size_3 >= $k+1){
                       $client->rang_id = 9;
                   }
                   else{
                       $client->rang_id = 10;
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
    /**************************************Shipping Order***********************************************/
    public function ShippingOfferCreate(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'order_id' => 'required|exists:shipping_orders,id',
            'description' => 'required',
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
            $client = Client::find($order->client_id);
            $driver = Driver::where('token',$request['token'])->first();
            $offers = Offer::where('parent_id',$request['order_id'])->where('driver_id',$driver->id)->where('parent_type','shipping_orders')->first();
            $package = Carbon::parse($driver->package_end)->lt(Carbon::now()) ;
            if (!$offers OR !$package){
                $Commission = Other::find(1);
                $num = $driver->balance - (($order->price * $Commission->value) / 100);

                if  ($num <= 0){
                    $result['statusCode'] = 402;
                    $result['message'] = "Баланста акша жок,кабылдай алмайсын";
                    $result['result'] = $num;
                }
                else{
                    $offer = new Offer();
                    $offer->driver_id = $driver->id;
                    $offer->parent_id = $request['order_id'];
                    $offer->parent_type = 'shipping_orders';
                    $offer->description = $request['description'];
                    $offer->price = $request['price'];
                    $offer->save();

                    $result['statusCode'] = 200;
                    $result['message'] = "success";
                    $result['result'] = $offer;

                    $push = new Push();
                    $push->OfferCreate($client->token,$order->type,$order->id,$offer->id,$driver->id);
                }

            }
            else{
                $result['statusCode'] = 401;
                $result['message'] = "offer exist";
                $result['result'] = $offers;
            }


        }
        return $result;
    }
    /**************************************Item Order***********************************************/
    public function ItemOfferCreate(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'order_id' => 'required|exists:item_orders,id',
            'description' => 'required',
            'price' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            $order = ItemOrder::find($request['order_id']);
            if  ($order and $driver){
                $client = Client::find($order->client_id);
                $offers = Offer::where('parent_id',$request['order_id'])->where('driver_id',$driver->id)->where('parent_type','item_orders')->first();
                $package = Carbon::parse($driver->package_end)->lt(Carbon::now()) ;
                if (!$offers OR !$package){
                    $Commission = Other::find(1);
                    $num = $driver->balance - (($order->price * $Commission->value) / 100);

                    if  ($num <= 0){
                        $result['statusCode'] = 402;
                        $result['message'] = "Баланста акша жок,кабылдай алмайсын";
                        $result['result'] = $num;
                    }
                    else{
                        $offer = new Offer();
                        $offer->driver_id = $driver->id;
                        $offer->parent_id = $request['order_id'];
                        $offer->parent_type = 'item_orders';
                        $offer->description = $request['description'];
                        $offer->price = $request['price'];
                        $offer->save();

                        $result['statusCode'] = 200;
                        $result['message'] = "success";
                        $result['result'] = $offer;

                        $push = new Push();
                        $push->OfferCreate($client->token,$order->type,$order->id,$offer->id,$driver->id);

                    }

                }
                else{
                    $result['statusCode'] = 401;
                    $result['message'] = "offer exist";
                    $result['result'] = $offers;
                }
            }
            else{
                $result['statusCode'] = 404;
                $result['message'] = "order not found";
                $result['result'] = [];
            }
        }
        return $result;
    }
    ///
    public function OrderList(Request $request){
        $rules = [
            'transports' => 'array|min:1',
            'materials' => 'array|min:1',
            'page' => 'required',
//            'city_id' => 'required',
//            'region_id' => 'required',
            'token'=> 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $service_sql = DB::table('service_orders')->select('service_orders.id','service_orders.type','service_orders.created_at')
                ->join('clients','clients.id','=','service_orders.client_id')
                ->join('cities','cities.id','=','clients.city_id')
                ->where('service_orders.step',1);
            $shipping_sql = DB::table('shipping_orders')->select('shipping_orders.id','shipping_orders.type','shipping_orders.created_at')
                ->join('clients','clients.id','=','shipping_orders.client_id')
                ->join('cities','cities.id','=','shipping_orders.from_city_id')
                ->where('shipping_orders.step',1);
            $item_sql = DB::table('item_orders')->select('item_orders.id','item_orders.type','item_orders.created_at')
                ->join('clients','clients.id','=','item_orders.client_id')
                ->join('cities','cities.id','=','clients.city_id')
                ->where('item_orders.step',1);

            if  ($request['transports']){
                $service_sql  = $service_sql->whereIn('service_orders.transport_id',$request['transports']);
                $shipping_sql  = $shipping_sql->whereIn('shipping_orders.transport_id',$request['transports']);
            }
            if  ($request['materials']){
                $item_sql = $item_sql->whereIn('item_orders.material_id',$request['materials']);
            }

            if  ($request['region_id']){
                $service_sql = $service_sql->where('cities.region_id',$request['region_id']);
                $shipping_sql = $shipping_sql->where('cities.region_id',$request['region_id']);
                $item_sql = $item_sql->where('cities.region_id',$request['region_id']);
            }
            if  ($request['city_id']){
                $service_sql = $service_sql->where('cities.id',$request['city_id']);
                $shipping_sql = $shipping_sql->where('cities.id',$request['city_id']);
                $item_sql = $item_sql->where('cities.id',$request['city_id']);
            }




            $orders = $service_sql->unionAll($shipping_sql)->unionAll($item_sql)->orderBy("created_at","desc")->limit(10)->offset($request['page'])->get();
            $driver = Driver::where('token',$request['token'])->first();
            if (count($orders) > 0 and $driver) {
                $arr = [];
                $service_offers = Offer::where('driver_id',$driver->id)
                    ->where('parent_type','service_orders')
                    ->pluck('parent_id')->toArray();
                $shipping_offers = Offer::where('driver_id',$driver->id)
                    ->where('parent_type','shipping_orders')
                    ->pluck('parent_id')->toArray();
                $item_offers =  Offer::where('driver_id',$driver->id)
                    ->where('parent_type','item_orders')
                    ->pluck('parent_id')->toArray();
                foreach ($orders as $order) {
                    if ($order->type == 'service_orders') {
                        $temp = $this->GetServiceOrder($order->id);
                        if (!in_array($order->id,$service_offers)){
                            if ($temp['statusCode'] == 200) {
                                $arr[] = $temp['result'];
                            }
                        }
                    }
                    else if ($order->type == 'shipping_orders') {
                        if (!in_array($order->id,$shipping_offers)){
                           $temp = $this->GetShippingOrder($order->id);
                           if ($temp['statusCode'] == 200) {
                               $arr[] = $temp['result'];
                           }
                       }
                    }

                    else if ($order->type == 'item_orders') {
                        if (!in_array($order->id,$item_offers)){
                           $temp = $this->GetItemOrder($order->id);
                           if ($temp['statusCode'] == 200) {
                               $arr[] = $temp['result'];
                           }
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
        return $result;
    }
    ///
    public function HistoryOrder(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            if ($driver){
                $histories = History::where('user_id',$driver->id)->where('role',2)->get();
                if  (count($histories) != 0){
                    $arr = [];
                    foreach ($histories as $history) {
                        if ($history->parent_type == 'service_orders'){
                            $temp = $this->GetServiceOrder($history->parent_id);
                            if ($temp['statusCode'] == 200){
                                if ($temp['result']['step'] == 3){
                                    $arr[] = $temp['result'];
                                }
                            }
                        }
                        else if ($history->parent_type == 'shipping_orders'){
                            $temp = $this->GetShippingOrder($history->parent_id);
                            if ($temp['statusCode'] == 200){
                                if ($temp['result']['step'] == 3){
                                    $arr[] = $temp['result'];
                                }
                            }
                        }
                        else if ($history->parent_type == 'item_orders'){
                            $temp = $this->GetItemOrder($history->parent_id);
                            if ($temp['statusCode'] == 200){
                                if ($temp['result']['step'] == 3){
                                    $arr[] = $temp['result'];
                                }
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
    public function MyOffers(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            if ($driver){
                $offers= Offer::where('driver_id',$driver->id)->where('step',1)->orderBy('id','desc')->get();
                if  (count($offers) != 0){
                    $arr = [];
                    foreach ($offers as $offer) {
                        if ($offer->parent_type == 'service_orders'){
                            $temp = $this->GetServiceOrder($offer->parent_id);
                            if ($temp['statusCode'] == 200){
                                if ($temp['result']['step'] == 1){
                                    $arr[] = $temp['result'];
                                }
                            }
                        }
                        else if ($offer->parent_type == 'shipping_orders'){
                            $temp = $this->GetShippingOrder($offer->parent_id);
                            if ($temp['statusCode'] == 200){
                                if ($temp['result']['step'] == 1){
                                    $arr[] = $temp['result'];
                                }
                            }
                        }
                        else if ($offer->parent_type == 'item_orders'){
                            $temp = $this->GetItemOrder($offer->parent_id);
                            if ($temp['statusCode'] == 200){
                                if ($temp['result']['step'] == 1){
                                    $arr[] = $temp['result'];
                                }
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
            $driver = Driver::where('token',$request['token'])->first();
            if (!$driver){
                $result['statusCode']= 404;
                $result['message']= 'User not found';
                $result['result']= [];
            }

            else{
                $sql = <<<STR
(SELECT service_orders.id ,service_orders.type,service_orders.created_at
    FROM `service_orders`
    INNER JOIN service_drivers ON service_orders.id = service_drivers.service_order_id
    WHERE service_drivers.driver_id = $driver->id
    AND service_orders.step = 2
)
UNION ALL
(SELECT  item_orders.id ,item_orders.type,item_orders.created_at
    FROM  `item_orders`
    INNER JOIN item_drivers ON item_orders.id = item_drivers.item_order_id
    WHERE item_drivers.driver_id = $driver->id
    AND item_orders.step = 2
)
UNION ALL
(SELECT shipping_orders.id ,shipping_orders.type,shipping_orders.created_at
    FROM `shipping_orders`
    INNER JOIN shipping_drivers ON shipping_orders.id = shipping_drivers.shipping_order_id
    WHERE shipping_drivers.driver_id = $driver->id
    AND shipping_orders.step = 2
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
    /**************************************Group Chat***********************************************/
    public function GroupCreate(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'name' => 'required|string',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            $groups = Group::where('creator_id',$driver->id)->first();
            if ($groups != null){
                $result['statusCode'] = 1;
                $result['message']= 'У вас уже есть группа';
                $result['result'] = null;
            }
            else{
                $group = new Group();
                $group->name = $request['name'];
                $group->creator_id = $driver->id;
                $group->save();


                $result['statusCode'] = 200;
                $result['message']= 'Успешно!';
                $result['result'] = $group;
            }
        }

        return $result;
    }
    public function GroupGet($id){
        $group = Group::find($id);
        if ($group){
            $temp = [];

            $temp['id'] = $group->id;
            $temp['socket_on'] = "group_$group->id";
            $driver = Driver::find($group->creator_id);
            $temp['creator']['id'] = $driver->id;
            $temp['creator']['name'] = $driver->name;
            $temp['creator']['rang'] = Rang::find($driver->rang_id);
            $temp['creator']['avatar'] = $driver->avatar;
            $temp['participants'] = [];
            foreach (Participant::where('group_id',$group->id)->get() as $item) {
                $d = Driver::find($item->driver_id);
                if  ($d){
                    $temp2 = [];
                    $temp2['id'] = $d->id;
                    $temp2['name'] = $d->name;
                    $temp2['rang'] = Rang::find($d->rang_id);
                    $temp2['avatar'] = $d->avatar;

                    $temp['participants'][] = $temp2;
                }

            }

            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = $temp;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = "not found";
            $result['result'] = null;
        }



        return $result;
    }
    public function GroupAddDriver(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'phone' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $creator = Driver::where('token',$request['token'])->first();
            $group = Group::where('creator_id',$creator->id)->first();
            if ($group != null){
                $driver = Driver::where('phone',$request['phone'])->first();

                if ($driver ){
                    $driver_group = Group::where('creator_id',$driver->id)->first();
                    $participants = Participant::where('group_id',$group->id)->where('driver_id',$driver->id)->first();
                    if ($participants == null and !$driver_group){

                        $push= new Push();
                        $push->Invitation($driver->token,$group->id);

                        $result['statusCode'] = 200 ;
                        $result['message']= 'Успешно! Push Send!';
                        $result['result'] = [];
                    }
                    else{
                        $result['statusCode'] = 3 ;
                        $result['message']= 'Driver уже состоит в группе';
                        $result['result'] = [];
                    }
                }
                else{
                    $result['statusCode'] = 404 ;
                    $result['message']= 'Driver не найден';
                    $result['result'] = [];
                }

            }
            else{
                $result['statusCode'] = 404 ;
                $result['message']= 'Группа не найден';
                $result['result'] = [];
            }
        }

        return $result;
    }
    public function GroupSignInDriver(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'group_id' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $group = Group::find($request['group_id']);
            if ($group != null){
                $driver = Driver::where('token',$request['token'])->first();
                if ($driver){
                    $participants = Participant::where('group_id',$group->id)->where('driver_id',$driver->id)->first();
                    if ($participants == null){

                        $participant = new Participant();
                        $participant->group_id = $group->id;
                        $participant->driver_id = $driver->id;
                        $participant->save();
                        $result['statusCode'] = 200 ;
                        $result['message']= 'Успешно!';
                        $result['result'] = $participant;
                    }
                    else{
                        $result['statusCode'] = 3 ;
                        $result['message']= 'Driver уже состоит в группе';
                        $result['result'] = null;
                    }
                }
                else{
                    $result['statusCode'] = 404 ;
                    $result['message']= 'Driver не найден';
                    $result['result'] = null;
                }

            }
            else{
                $result['statusCode'] = 404 ;
                $result['message']= 'Группа не найден';
                $result['result'] = null;
            }
        }

        return $result;
    }
    public function GroupDeleteDriver(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'driver_id' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $creator = Driver::where('token',$request['token'])->first();
            if ($creator != null){
                $group = Group::where('creator_id',$creator->id)->first();
                if ($group != null){
                    $driver = Driver::where('token',$request['token'])->first();
                    if ($driver){
                        $participant = Participant::where('group_id',$group->id)->where('driver_id',$driver->id)->first();
                        if ($participant != null){
                            $participant->delete();
                            $result['statusCode'] = 200 ;
                            $result['message']= 'Успешно Удалено! ';
                            $result['result'] = null;
                        }
                        else{
                            $result['statusCode'] = 4 ;
                            $result['message']= 'Driver не состоит в группе';
                            $result['result'] = null;
                        }
                    }
                    else{
                        $result['statusCode'] = 404 ;
                        $result['message']= 'Driver не найден';
                        $result['result'] = null;
                    }

                }
                else{
                    $result['statusCode'] = 404 ;
                    $result['message']= 'Группа не найден';
                    $result['result'] = null;
                }
            }
            else{
                $result['statusCode'] = 404 ;
                $result['message']= 'Создатель не найден';
                $result['result'] = null;
            }
        }

        return $result;
    }

    public function SendMessage(Request $request){
        $rules = [
            'token' => 'required|exists:drivers,token',
            'group_id' => 'required',
            'text' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            $group = Group::find($request['group_id']);
            if ($group){

                $message = new Message();
                $message->driver_id = $driver->id;
                $message->group_id = $group->id;
                $message->text = $request['text'];
                $message->save();

                $result['statusCode'] = 200;
                $result['message']= 'Send';
                $result['result'] = $this->GetMessage(Message::where('group_id',$group->id)->orderBy('id','desc')->limit(1)->get());
            }
            else{
                $result['statusCode'] = 404;
                $result['message']= 'groups not found!';
                $result['result'] = [];
            }
        }

        return $result;
    }
    public function GetMessages(Request $request){
        $rules = [
            'group_id' => 'required',
            'page' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $messages = Message::where('group_id',$request['group_id'])
                ->orderBy('id','desc')
                ->limit(10)
                ->offset($request['page'])
                ->get();
            if (count($messages) != 0){
                $result['statusCode'] = 200;
                $result['message']= 'ok';
                $result['result'] = $this->GetMessage($messages);
            }
            else{
                $result['statusCode'] = 404;
                $result['message']= 'not found';
                $result['result'] = [];
            }
        }

        return $result;
    }
    public function GetMessage($messages){
        $arr = [];
        foreach ($messages as $message) {
            $driver = Driver::find($message->driver_id);
            if ($driver){
                $temp['id']= $message->id;
                $temp['text'] = $message->text;
                $temp['driver']['id'] = $driver->id;
                $temp['driver']['name'] = $driver->name;
                $temp['driver']['rang'] = Rang::find($driver->rang_id);
                $temp['driver']['avatar'] = $driver->avatar;
                $arr[] = $temp;
            }
        }

        return $arr;
    }
    public function PayBox($amount,$id)
    {
        $arrReq = [
            'pg_merchant_id' => '505529',
            'pg_amount' => $amount,
            'pg_description' => 'Easy payment',
            'pg_order_id' => $id,
            'pg_salt' => mt_rand(21, 43433),
            'pg_result_url' => url('PayBoxResult')
        ];
        $arrReq['pg_sig'] = PG_Signature::make('payment.php', $arrReq, 'SkWri2uyGC4eNerv');

        $query = http_build_query($arrReq);

        $result['statusCode'] = 200;
        $result['message']= 'success';
        $result['result'] = 'https://www.paybox.kz/payment.php?'.$query;

        return $result;
    }
    public function PayBoxResult(Request $request)
    {
        if($request['pg_result']) {
            $driver= Driver::find($request['pg_order_id']);
            $driver->balance += $request['pg_amount'];
            $driver->save();
            /*return responce*/
            $arrReq = [
                'pg_merchant_id' => 505529,
                'pg_salt' => mt_rand(21, 43433)
            ];
            $pg_sig = PG_Signature::make('payment.php', $arrReq, 'SkWri2uyGC4eNerv');
            $pg_salt = str_random(10);

            $xmlResponce = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<response>
<pg_salt>$pg_salt</pg_salt>
<pg_status>ok</pg_status>
<pg_description>Товар передан покупателю</pg_description>
<pg_sig>$pg_sig</pg_sig>
</response>
XML;

            return $xmlResponce;
        }
    }

    /*************************************************************************************/

    public function Packages(){
        $p = DB::table('packages')->get();
        if (count($p) > 0){
            $result['statusCode'] = 200;
            $result['message'] = "success";
            $result['result'] = $p;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = "not found";
            $result['result'] = null;
        }

        return $result;
    }
    public function BuyPackage(Request $request){
        $rules = [
            'token' => 'required',
            'id' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
           $driver = Driver::where('token',$request['token'])->first();
           if ($driver){
               $p = DB::table('packages')->where('id',$request['id'])->first();
               if ($p){
                   $driver->package_end = Carbon::parse($driver->package_end)->addDays($p->day);
                   $driver->bonus = $driver->bonus + $p->bonus ;
                   $driver->save();
                   $result['statusCode']= 200;
                   $result['message']= 'success';
                   $result['result']= Driver::where('token',$request['token'])->first();
               }else{
                   $result['statusCode']= 404;
                   $result['message']= 'package not found';
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

    /**********************************Chats ***************************************************/
    public function ChatSendMessage(Request $request){
        $rules = [
            'token' => 'required',
            'client_id' => 'required',
            'text' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
           $driver = Driver::where('token',$request['token'])->first();
           if ($driver){
               $client = Client::find($request['client_id']);
               if ($client){
                   $chat = Chat::where('driver_id',$driver->id)->where('client_id',$client->id)->first();
                   if  ($chat){
                       $msg = new ChatMessage();
                       $msg->role = 'driver';
                       $msg->user_id = $driver->id;
                       $msg->text = $request['text'];
                       $msg->chat_id = $chat->id;
                       $msg->save();

                       $msg->user = $driver;


                       $chat->updated_at = Carbon::now();
                       $chat->save();

                   } else{
                        $chat = new Chat();
                        $chat->client_id = $client->id;
                        $chat->driver_id = $driver->id;
                        $chat->save();

                       $msg = new ChatMessage();
                       $msg->role = 'driver';
                       $msg->user_id = $driver->id;
                       $msg->text = $request['text'];
                       $msg->chat_id = $chat->id;
                       $msg->save();

                       $msg->user = $driver;


                   }

                   if ($client->online == 0){
                       $push = new Push();
                       $push->NewMessage($client->token,$request['text'],$chat->id,$driver->id);
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
        if (isset($request['token'])){
            $driver = Driver::where('token',$request['token'])->first();
        }  else{
            $driver = Driver::find($request['id']);
        }
        if ($driver){
            $chats = Chat::where('driver_id',$driver->id)->orderBy('updated_at','desc')->get();
            if (count($chats) > 0){
                $arr = [];
                foreach ($chats as $chat) {
                    $msg = ChatMessage::where('chat_id',$chat->id)->orderBy("id",'desc')->first();
                    if ($msg){
                        $t['id'] = $chat->id;
                        $t['driver'] = Driver::find($chat->driver_id);
                        $t['client'] = Client::find($chat->client_id);
                        $t['last_message'] = ChatMessage::where('chat_id',$chat->id)->orderBy("id",'desc')->first();
                        $arr[] =$t;
                    }
                }

                $result['statusCode']= 200;
                $result['driver']= $driver;
                $result['message']= 'success';
                $result['result']= $arr;
            }else{
                $result['statusCode']= 404;
                $result['message']= 'chats not found';
                $result['result']= [];
            }
        }else{
            $result['statusCode']= 404;
            $result['message']= 'driver not found';
            $result['result']= [];
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
            $client = Driver::where('token',$request['token'])->first();
            if ($client){
                $client->online = $request['online'];
                $client->save();
                $result['statusCode']= 200;
                $result['message']= 'success not found';
                $result['result']=$client;
            }else{
                $result['statusCode']= 404;
                $result['message']= 'driver not found';
                $result['result']= [];
            }
        }
        return response()->json($result, $result['statusCode']);
    }
    public function CreateChat(Request $request){
        $rules = [
            'token' => 'required',
            'client_id' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {
            $driver = Driver::where('token',$request['token'])->first();
            if ($driver){
                $client = Client::find($request['client_id']);
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


/*
1 = У вас уже есть группа
2 = Нехватка ранга
3 = Driver  состоит в группе
3 = Driver не состоит в группе


200 = ok
404 = not found
400 = bad request
401 = error request
 */