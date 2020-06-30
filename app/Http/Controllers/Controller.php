<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Car;
use App\Models\CarMaterial;
use App\Models\CarTransport;
use App\Models\City;
use App\Models\CountType;
use App\Models\Group;
use App\Models\ItemOrder;
use App\Models\Material;
use App\Models\MaterialType;
use App\Models\Offer;
use App\Models\Participant;
use App\Models\Rang;
use App\Models\Review;
use App\Models\ServiceOrder;
use App\Models\ShippingOrder;
use App\Models\ShippingTransport;
use App\Models\Transport;
use App\Models\TransportType;
use Carbon\Carbon;
use http\Env\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Image;
use Illuminate\Support\Str;
use Validator;
use File;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Option;
use DB;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function GetClient($id){
        $c = Client::find($id);
        if ($c){
            $client['id'] = $c->id;
            $client['name'] = $c->name;
            $client['phone'] = $c->phone;
            $client['email'] = $c->email;
            $client['avatar'] = $c->avatar;
            $client['rang'] = Rang::find($c->rang_id);
            $client['cach_back'] = $c->cach_back;
            $client['iin'] = $c->iin;
            $client['city'] = City::find($c->city_id);
            $client['description'] = $c->description;
            $client['verification'] = $c->verification;
            $client['token'] = $c->token;
            $client['online_at'] = $c->online_at;
            $client['ball'] = $c->ball;
            $client['access'] = $c->access;
            $client['access_date'] = $c->access_date;
            $client['created_at'] = Carbon::parse($c->created_at)->format('d.m.Y');

            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $client;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = null;
        }

        return $result;

    }
    public function GetDriver($id){
        $c = Driver::find($id);
        if ($c){
            $d['id'] = $c->id;
            $d['name'] = $c->name;
            $d['phone'] = $c->phone;
            $d['email'] = $c->email;
            $d['avatar'] = $c->avatar;
            $d['rang'] = Rang::find($c->rang_id);
            $d['balance'] = $c->balance;
            $d['bonus'] = $c->bonus;
            $d['ball'] = $c->ball;
            $d['description'] = $c->description;
            $d['city'] = City::find($c->city_id);
            $d['token'] = $c->token;
            $d['verification'] = $c->verification;
            $d['access'] = $c->access;
            $d['access_date'] = $c->access_date;
            $d['lat'] = $c->lat;
            $d['lng'] = $c->lng;
            $d['created_at'] = Carbon::parse($c->created_at)->format('d.m.Y');
            $d['ip'] = $c->ip;
            $d['iin'] = $c->iin;
            $d['passport'] = $c->passport;
            $d['passport_images'] = Image::where('parent_type','passport')->where('parent_id',$id)->get();

            $d['cars'] = [];
            $d['group'] = null;

            $participant = Participant::where('driver_id',$id)->orderBy('id','DESC')->first();
            $creator = Group::where('creator_id',$id)->orderBy('id','DESC')->first();

            if  ($participant){
                $group = Group::find($participant->group_id);
                if ($group){
                    $d['group']['id']  = $group->id;
                    $d['group']['socket_on']  = "group_$group->id";
                }
            }
            if ($creator){
                $d['group']['id']  = $creator->id;
                $d['group']['socket_on']  = "group_$creator->id";
            }



            $cars = Car::where('driver_id',$id)->get();


            if  (count($cars) != 0){
                foreach ($cars as $car) {
                    $temp['id'] = $car->id;
                    $temp['driver_id'] = $car->driver_id;
                    $temp['name'] = $car->name;
                    $temp['info'] = $car->info;
                    $temp['number'] = $car->number;
                    $temp['car_transports'] = DB::table('car_transports')
                        ->where('car_transports.car_id','=',$car->id)
                        ->join('transports','transports.id','=','car_transports.transport_id')
                        ->select('transports.id','transports.name','transports.type')
                        ->get();;
                    $temp['car_shipping_order'] = DB::table('car_transports')
                        ->join('transports','transports.id','=','car_transports.transport_id')
                        ->where('transports.type','=','shipping_orders')
                        ->where('car_transports.car_id','=',$car->id)
                        ->select('transports.id','transports.name','transports.type')
                        ->get();
                    $temp['car_service_order'] = DB::table('car_transports')
                        ->join('transports','transports.id','=','car_transports.transport_id')
                        ->where('transports.type','=','service_orders')
                        ->where('car_transports.car_id','=',$car->id)
                        ->select('transports.id','transports.name','transports.type')
                        ->get();;

                    $temp['car_materials'] = DB::table('car_materials')
                        ->join('materials','materials.id','=','car_materials.material_id')
                        ->where('car_materials.car_id','=',$car->id)
                        ->select('materials.id','materials.name')
                        ->get();
                    $temp['images'] = Image::where('parent_type','car')->where('parent_id',$car->id)->get();

                    $d['cars'][] = $temp;
                }
            }


            $d['created_at'] = Carbon::parse($c->created_at)->format('d.m.Y');
            $d['worked'] = $c->worked_hour;

            $reviews = Review::where('driver_id',$id)->get();
            $d['reviews'] = [];
            if (count($reviews) != 0){

                $speed = 0;
                $punctuality = 0;
                $quality = 0;
                $price = 0;

                foreach ($reviews as $review) {
                    $client = Client::find($review->client_id);
                    if ($client){

                        $temp['id'] = $review->id;
                        $temp['driver_id'] = $review->id;
                        $temp['client'] = $client;

                        $temp['speed'] = $review->speed;
                        $temp['punctuality'] = $review->punctuality;
                        $temp['quality'] = $review->quality;
                        $temp['price'] = $review->price;
                        $temp['description'] = $review->description;
                        $temp['created_at'] = $review->created_at;

                        $d['reviews'][] = $temp;

                        $speed += $review->speed;
                        $punctuality += $review->punctuality;
                        $quality += $review->quality;
                        $price += $review->price;


                    }
                }


                $d['reviews_all']['speed'] = round($speed/count($reviews));
                $d['reviews_all']['punctuality'] = round($punctuality/count($reviews));
                $d['reviews_all']['quality'] = round($quality/count($reviews));
                $d['reviews_all']['price'] = round($price/count($reviews));
            }
            else{
               $d['reviews_all']['speed'] = 0;
               $d['reviews_all']['punctuality'] = 0;
               $d['reviews_all']['quality'] = 0;
               $d['reviews_all']['price'] = 0;
            }



            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $d;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = null;
        }

        return $result;

    }
    public function GetOffer($id){
        $offer = Offer::find($id);
        if ($offer){
            $user = $this->GetDriver($offer->driver_id);
            $user['result']['price'] = $offer->price;
            $user['result']['offer_id'] = $offer->id;
            $result['statusCode'] =200;
            $result['message'] = 'success';
            $result['result'] =$user['result'];
        }
        else{
            $result['statusCode'] =404;
            $result['message'] = 'not found';
            $result['result'] = [];
        }

        return $result;
    }
    public function GetTransports($type = 'shipping_orders'){
        $transports =  Transport::where('type',$type)->orderBy('name')->get();
        if  (count($transports) != 0){
            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] =$transports;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'success';
            $result['result'] = [];
        }

        return $result;
    }
    public function GetTransportTypes($id){
        $transportTypes = TransportType::where('transport_id',$id)->orderBy('name')->get();
        if (count($transportTypes) != 0) {
            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $transportTypes;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = [];
        }

        return $result;
    }
    public function GetMaterials(){

        $result['statusCode'] = 200;
        $result['message'] = 'success';
        $result['result'] = Material::orderBy('name')->get();

        return $result;
    }
    public function GetMaterialTypes($id){
        $Types = MaterialType::where('material_id',$id)->orderBy('name')->get();
        if (count($Types) != 0) {
            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $Types;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = [];
        }

        return $result;
    }
    public function GetCountTypes(){
        $Types = CountType::all();
        if (count($Types) != 0) {
            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $Types;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = [];
        }

        return $result;
    }
    public function GetOptions(){
        $result['statusCode'] = 200;
        $result['message'] = 'success';
        $result['result'] = Option::all();

        return $result;
    }

    public function GetServiceOrder($id){
        $item = ServiceOrder::find($id);
        if ($item){
            $temp['id'] = $item->id;
            $temp['transport'] = Transport::find($item->transport_id);
            $temp['transport_type'] = TransportType::find($item->transport_type_id);
            $temp['date_1'] = $item->date_1;
            $temp['hour'] = $item->hour;
            $temp['to_address'] = Address::find($item->to_address_id);
            $temp['price'] = $item->price;
            $temp['description'] = $item->description;
            $temp['client_id'] = $item->client_id;
            $temp['drivers'] = DB::table('service_drivers')
                ->select('drivers.*')
                ->join('drivers','drivers.id','service_drivers.driver_id')
                ->where('service_drivers.service_order_id',$id)
                ->get();
            $temp['type'] = $item->type;
            $temp['step'] = $item->step;
            $temp['created_at'] = Carbon::parse($item->created_at)->format('d.m.y');
            $temp['images'] = $this->GetImages($item->id,$item->type);
            $temp['count_offers'] = Offer::where('parent_type','service_orders')->where('step',1)->where('parent_id',$id)->count();

            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $temp;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = null;
        }

        return $result;

    }
    public function GetShippingOrder($id){
        $item = ShippingOrder::find($id);
        if ($item){
            $temp['id'] = $item->id;
            $temp['transport'] = Transport::find($item->transport_id);
            $temp['date'] = $item->date;
            $temp['size'] = $item->size;
            $temp['weight'] = $item->weight;
            $temp['from_city'] = City::find($item->from_city_id);
            $temp['from'] = $item->from;
            $temp['to'] = DB::table("shipping_order_to")
                ->select('to','city_id','cities.name','cities.region_id')
                ->join('cities','cities.id','shipping_order_to.city_id')
                ->where('shipping_order_id',$id)->get();
            $temp['price'] = $item->price;
            $temp['description'] = $item->description;
            $temp['client_id'] = $item->client_id;
            $temp['drivers'] = DB::table('shipping_drivers')
                ->select('drivers.*')
                ->join('drivers','drivers.id','shipping_drivers.driver_id')
                ->where('shipping_drivers.shipping_order_id',$id)
                ->get();
            $temp['type'] = $item->type;
            $temp['step'] = $item->step;
            $temp['created_at'] = Carbon::parse($item->created_at)->format('d.m.y');
            $temp['images'] = $this->GetImages($item->id,$item->type);
            $temp['count_offers'] = Offer::where('parent_type','shipping_orders')->where('step',1)->where('parent_id',$id)->count();

            $temp['options'] = DB::table('shipping_orders')
                ->join('shipping_options','shipping_orders.id','=','shipping_options.shipping_order_id')
                ->join('options','shipping_options.option_id','=','options.id')
                ->where('shipping_options.shipping_order_id','=',$item->id)
                ->select('options.id','options.name')
                ->get();


            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $temp;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = null;
        }

        return $result;

    }
    public function GetItemOrder($id){
        $item = ItemOrder::find($id);
        if ($item){
            $temp['id'] = $item->id;
            $temp['material'] = Material::find($item->material_id);
            $temp['material_type'] = MaterialType::find($item->material_type_id);
            $temp['count'] = $item->count;
            $temp['count_type'] = CountType::find($item->count_type_id);
            $temp['date'] = $item->date;
            $temp['to_address'] = Address::find($item->to_address_id);
            $temp['price'] = $item->price;
            $temp['description'] = $item->description;
            $temp['client_id'] = $item->client_id;
            $temp['drivers'] = DB::table('item_drivers')
                ->select('drivers.*')
                ->join('drivers','drivers.id','item_drivers.driver_id')
                ->where('item_drivers.item_order_id',$id)
                ->get();
            $temp['type'] = $item->type;
            $temp['step'] = $item->step;
            $temp['created_at'] = Carbon::parse($item->created_at)->format('d.m.y');
            $temp['images'] = $this->GetImages($item->id,$item->type);
            $temp['count_offers'] = Offer::where('parent_type','item_orders')->where('step',1)->where('parent_id',$id)->count();

            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $temp;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = null;
        }

        return $result;

    }

    //Get Offers
    public function GetServiceOffers($id,$step = 1){
        $offers= Offer::where('parent_type','service_orders')->where('step',$step)->where('parent_id',$id)->get();
        if (count($offers) != 0){
            $temp = [];
            foreach ($offers as $offer) {
                $user = $this->GetDriver($offer->driver_id);
                $user['result']['price'] = $offer->price;
                $user['result']['offer_id'] = $offer->id;
                if ($user['statusCode'] == 200){
                    $temp[] = $user['result'];
                }
            }


            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $temp;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = [];
        }

        return $result;

    }
    public function GetShippingOffers($id,$step = 1 ){
        $offers= Offer::where('parent_type','shipping_orders')->where('step',$step)->where('parent_id',$id)->get();
        if (count($offers) != 0){
            $temp = [];
            foreach ($offers as $offer) {
                $user = $this->GetDriver($offer->driver_id);
                $user['result']['price'] = $offer->price;
                $user['result']['offer_id'] = $offer->id;
                if ($user['statusCode'] == 200){
                    $temp[] = $user['result'];
                }
            }


            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $temp;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = [];
        }

        return $result;

    }
    public function GetItemOffers($id,$step = 1){
        $offers= Offer::where('parent_type','item_orders')->where('step',$step)->where('parent_id',$id)->get();
        if (count($offers) != 0){
            $temp = [];
            foreach ($offers as $offer) {
                $user = $this->GetDriver($offer->driver_id);
                $user['result']['price'] = $offer->price;
                $user['result']['offer_id'] = $offer->id;
                if ($user['statusCode'] == 200){
                    $temp[] = $user['result'];
                }
            }


            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $temp;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = [];
        }

        return $result;

    }

    public function GetCities($id){
        $cities= City::where('region_id',$id)->get();
        if (count($cities) != 0){
            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $cities;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = [];
        }

        return $result;

    }
    public function GetRegions(){
        $cities= DB::table('regions')->get();
        if (count($cities) != 0){
            $result['statusCode'] = 200;
            $result['message'] = 'success';
            $result['result'] = $cities;
        }
        else{
            $result['statusCode'] = 404;
            $result['message'] = 'not found';
            $result['result'] = [];
        }

        return $result;

    }

    public function GetImages($parent_id,$parent_type){
        $images = Image::where('parent_type',$parent_type)->where('parent_id',$parent_id)->orderBy('id','DESC')->get();
        if (count($images) != 0){
            $imgs = [];
            foreach ($images as $k=>$image) {
                $imgs[$k]['id'] = $image->id;
                $imgs[$k]['path'] = asset($image->path);
            }
            return $imgs;
        }
        else{
            $img[0]['id'] = 0;
            $img[0]['path'] = 'http://bigriverequipment.com/wp-content/uploads/2017/10/no-photo-available.png';
            return $img;
        }
    }
    public function uploadfile($file,$dir = 'uploads'){
        File::isDirectory($dir) or File::makeDirectory($dir, 0777, true, true);

        $randomString = Str::random(5);

        $file_type = File::extension($file->getClientOriginalName());
        $file_name = time().$randomString.'.'.$file_type;
        $file->move($dir, $file_name);
        return 'http://78.24.220.197:778/'.$dir.'/'.$file_name;
    }
    public function deletefile($path){
        if (File::exists($path)) {
            File::delete($path);
            return true;
        }
        else{
            return false;
        }
    }


    protected function validator($errors,$rules) {
        return Validator::make($errors,$rules);
    }
}
