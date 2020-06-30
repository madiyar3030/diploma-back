<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\CancelOrder;
use App\Models\City;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Feedback;
use App\Models\ItemOrder;
use App\Models\Material;
use App\Models\Moderator;
use App\Models\Option;
use App\Models\Other;
use App\Models\ServiceOrder;
use App\Models\ShippingOrder;
use App\Models\Image;
use App\Models\Transport;
use App\Push;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use App\Models\MaterialType;
use App\Models\History;
use App\Models\TransportType;
use App\Models\Offer;
class AdminController extends Controller
{
    public function SignIn(){
        if (session()->has('access')){
            return redirect()->route('MainPage');
        }
        else{
            return view('admin.index');

        }
    }
    public function Out(Request $request){
        foreach (session()->all() as $k=>$item) {
            session()->forget($k);
            session()->save();
        }
        return redirect()->route('SignIn');
    }
    public function SignInPost(Request $request){
        if ($request['login'] == '1' and $request['password'] == '1'){
            session()->put('access',1);
            session()->put('login','admin');
            session()->save();
            return redirect()->route('MainPage');
        }
        else{
            $moderator = Moderator::where('login',$request['login'])->where('password',$request['password'])->first();
            if ($moderator){
                session()->put('access',2);
                session()->put('login',$moderator->login);
                session()->save();
            }
            else{
                return redirect()->route('MainPage');
            }

        }


        return view('admin.index');
    }
    public function MainPage(){
        return view('admin.main');
    }
    /*---------------------------Clients-------------------------------------------------------*/
    public function Clients(){
        $clients = Client::orderBy('id','DESC')->paginate(15);
        return view('admin.clients.clients',compact('clients'));
    }
    public function Client($id){
        $client = $this->GetClient($id)['result'];
        return view('admin.clients.client',compact('client'));
    }
    public function ClientsSearch(Request $request){
        $clients = DB::table('clients')
            ->where('name', 'like', "%$request->text%")
            ->orWhere('phone', 'like', "%$request->text%")
            ->orWhere('iin', 'like', "%$request->text%")
            ->orWhere('email', 'like', "%$request->text%")
            ->orderBy('id','DESC')
            ->paginate(15);
        return view('admin.clients.clients',compact('clients'));
    }
    public function ClientDelete($id){
        $client = Client::find($id);
        $service_orders = ServiceOrder::where('client_id',$id)->get();
        foreach ($service_orders as $order) {
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
        }
        $shipping_orders = ShippingOrder::where('client_id',$id)->get();
        foreach ($shipping_orders as $order) {
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
        $item_orders = ItemOrder::where('client_id',$id)->get();
        foreach ($item_orders as $order) {
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

        return redirect()->route('Clients');
    }
    public function ClientEdit($id){
        $client = Client::find($id);

        return view('admin.clients.edit',compact('client'));
    }
    public function ClientSave(Request $request){
        $client = Client::find($request['id']);
        $client->name = $request['name'];
        $client->phone = $request['phone'];
        $client->email = $request['email'];
        $client->ball = $request['ball'];
        $client->iin = $request['iin'];
        $client->description = $request['description'];
        $client->city_id = $request['city_id'];
        $client->save();
        return redirect()->route('Client',$request['id']);
    }

    public function ClientAccess(Request $request){
        $client = Client::find($request['id']);
        $client->access = 0;
        if ($request['access_date']){
            $client->access_date = Carbon::parse($request['access_date']);
        }
        $client->save();
        return redirect()->back();
    }
    public function ClientAccessTrue($id){
        $client = Client::find($id);
        $client->access = 1;

        $client->save();
        return redirect()->back();
    }
    public function ClientHistories($id){
        $his = History::where('role',1)->where('user_id',$id)->get();

        $arr = [];
        foreach ($his as $hi) {
            if ($hi->parent_type == 'service_orders') {
                $temp = $this->GetServiceOrder($hi->parent_id);
                if ($temp['statusCode'] == 200) {
                    $arr[] = $temp['result'];
                }
            } else if ($hi->parent_type == 'shipping_orders') {
                $temp = $this->GetShippingOrder($hi->parent_id);
                if ($temp['statusCode'] == 200) {
                    $arr[] = $temp['result'];
                }
            } else if ($hi->parent_type == 'item_orders') {
                $temp = $this->GetItemOrder($hi->parent_id);
                if ($temp['statusCode'] == 200) {
                    $arr[] = $temp['result'];
                }
            }
        }
        return view('admin.clients.histories',compact('arr'));
    }
    public function DriverHistories($id){
        $his = History::where('role',2)->where('user_id',$id)->get();
        $arr =  [];
        foreach ($his as $hi) {
            if ($hi->parent_type == 'service_orders') {
                $temp = $this->GetServiceOrder($hi->parent_id);
                if ($temp['statusCode'] == 200) {
                    $arr[] = $temp['result'];
                }
            } else if ($hi->parent_type == 'shipping_orders') {
                $temp = $this->GetShippingOrder($hi->parent_id);
                if ($temp['statusCode'] == 200) {
                    $arr[] = $temp['result'];
                }
            } else if ($hi->parent_type == 'item_orders') {
                $temp = $this->GetItemOrder($hi->parent_id);
                if ($temp['statusCode'] == 200) {
                    $arr[] = $temp['result'];
                }
            }
        }

        return view('admin.drivers.histories',compact('arr'));
    }
    /*---------------------------Clients-------------------------------------------------------*/
    public function Drivers(){
        $drivers = DB::table('drivers')->orderBy('id','DESC')->paginate(15);
        return view('admin.drivers.drivers',compact('drivers'));
    }
    public function Driver($id){
        $driver = $this->GetDriver($id)['result'];
        return view('admin.drivers.driver',compact('driver'));
    }
    public function DriverVerification($id){
        $driver = Driver::find($id);
        $driver->verification = !$driver->verification;
        $driver->save();
        return redirect()->route('Driver',$id);
    }
    public function DriversSearch(Request $request){
        $drivers = DB::table('drivers')
            ->where('name', 'like', "%$request->text%")
            ->orWhere('phone', 'like', "%$request->text%")
            ->orWhere('email', 'like', "%$request->text%")
            ->orderBy('id','DESC')
            ->paginate(15);
        return view('admin.drivers.drivers',compact('drivers'));
    }
    public function DriverDelete($id){
        $client = Driver::find($id);
        $service_orders = ServiceOrder::where('driver_id',$id)->get();
        foreach ($service_orders as $order) {
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
        }
        $shipping_orders = ShippingOrder::where('driver_id',$id)->get();
        foreach ($shipping_orders as $order) {
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
        $item_orders = ItemOrder::where('driver_id',$id)->get();
        foreach ($item_orders as $order) {
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

        return redirect()->route('Drivers');
    }
    public function DriverEdit($id){
        $driver = Driver::find($id);
        return view('admin.drivers.edit',compact('driver'));
    }
    public function DriverSave(Request $request){
        $client = Driver::find($request['id']);
        $client->name = $request['name'];
        $client->phone = $request['phone'];
        $client->email = $request['email'];
        $client->ball = $request['ball'];
        $client->bonus = $request['bonus'];
        $client->iin = $request['iin'];
        $client->description = $request['description'];
        $client->city_id = $request['city_id'];
        $client->save();
        return redirect()->route('Driver',$request['id']);
    }

    public function Commission(Request $request){
        $c = Other::find(1);
        return view('admin.settings.commission',compact('c'));
    }
    public function CommissionSave(Request $request){
        $c = Other::find(1);
        $c->value = $request['value'];
        $c->save();

        return redirect()->back();
    }
    public function DriverAccess(Request $request){
        $driver = Driver::find($request['id']);
        $driver->access = 0;
        if ($request['access_date']){
            $driver->access_date = Carbon::parse($request['access_date']);
        }
        $driver->save();
        return redirect()->back();
    }
    public function DriverAccessTrue($id){
        $driver = Driver::find($id);
        $driver->access = 1;

        $driver->save();
        return redirect()->back();
    }

//    Moderators

    public function Moderators(){
       return view('admin.moderators.moderators');
    }
    public function ModeratorCreate(Request $request){
        $moderator = new Moderator();
        $moderator->login = $request['login'];
        $moderator->password= $request['password'];
        $moderator->save();

        return redirect()->route('Moderators');
    }
    public function ModeratorDelete($id){
        $moderator =  Moderator::find($id);
        $moderator->delete();

        return redirect()->route('Moderators');
    }


//  Orders

    public function ServiceOrders(){
        $ordersDB = DB::table('service_orders')->orderBy('id','DESC')->paginate(15);
        $orders = [];

        foreach ($ordersDB as $item) {
            $temp = $this->GetServiceOrder($item->id);
            if ($temp['statusCode'] == 200){
                $orders[] = $temp['result'];
            }
        }
        return view('admin.service_order.orders',compact(['orders','ordersDB']));

    }
    public function ServiceOrderShow($id){
        $order = $this->GetServiceOrder($id)['result'];
        return view('admin.service_order.show',compact('order'));
    }
    public function ServiceOrderEdit($id){
        $order = $this->GetServiceOrder($id)['result'];
        return view('admin.service_order.edit',compact('order'));
    }
    public function ServiceOrderSave(Request $request){
        $order = ServiceOrder::find($request['id']);
        $order->price = $request['price'];
        $order->description = $request['description'];
        $order->date_1= Carbon::parse($request['date_1']);
        $order->hour = Carbon::parse($request['hour']);
        $order->save();

        return redirect()->route('ServiceOrderShow',$request['id']);
    }
    public function ServiceOrderDelete($id){
        $order = ServiceOrder::find($id);
        $address = Address::find($order->to_address_id);
        foreach (Image::where('parent_type','service_orders')->where('parent_id',$id)->get() as $item) {
            $this->deletefile($item->path);
            $item->delete();
        }

        $address->delete();
        $order->delete();

        return redirect()->route('ServiceOrders');
    }

    public function ShippingOrders(){

        $ordersDB =DB::table('shipping_orders')->orderBy('id','DESC')->paginate(15);
        $orders = [];

        foreach ($ordersDB as $item) {
            $temp = $this->GetShippingOrder($item->id);
            if ($temp['statusCode'] == 200){
                $orders[] = $temp['result'];
            }
        }
        return view('admin.shipping_order.orders',compact(['orders','ordersDB']));

    }
    public function ShippingOrderShow($id){
        $order = $this->GetShippingOrder($id)['result'];
        return view('admin.shipping_order.show',compact('order'));
    }
    public function ShippingOrderEdit($id){
        $order = $this->GetShippingOrder($id)['result'];
        return view('admin.shipping_order.edit',compact('order'));
    }
    public function ShippingOrderSave(Request $request){
        $order = ShippingOrder::find($request['id']);
        $order->price = $request['price'];
        $order->description = $request['description'];
        $order->date= Carbon::parse($request['date']);
        $order->save();

        return redirect()->route('ShippingOrderShow',$request['id']);
    }
    public function ShippingOrderDelete($id){
        $order = ShippingOrder::find($id);
        foreach (Image::where('parent_type','shipping_orders')->where('parent_id',$id)->get() as $item) {
            $this->deletefile($item->path);
            $item->delete();
        }

        $order->delete();

        return redirect()->route('ShippingOrders');
    }

    public function ItemOrders(){
        $ordersDB =DB::table('item_orders')->orderBy('id','DESC')->paginate(15);
        $orders = [];

        foreach ($ordersDB as $item) {
            $temp = $this->GetItemOrder($item->id);
            if ($temp['statusCode'] == 200){
                $orders[] = $temp['result'];
            }
        }
        return view('admin.item_order.orders',compact(['orders','ordersDB']));

    }
    public function ItemOrderShow($id){
        $order = $this->GetItemOrder($id)['result'];
        return view('admin.item_order.show',compact('order'));
    }
    public function ItemOrderEdit($id){
        $order = $this->GetItemOrder($id)['result'];
        return view('admin.item_order.edit',compact('order'));
    }
    public function ItemOrderSave(Request $request){
        $order = ItemOrder::find($request['id']);
        $order->price = $request['price'];
        $order->description = $request['description'];
        $order->date= Carbon::parse($request['date']);
        $order->save();

        return redirect()->route('ItemOrderShow',$request['id']);
    }
    public function ItemOrderDelete($id){
        $order = ItemOrder::find($id);
        $to_address = Address::find($order->to_address_id);
        foreach (Image::where('parent_type','item_orders')->where('parent_id',$id)->get() as $item) {
            $this->deletefile($item->path);
            $item->delete();
        }

        $to_address->delete();
        $order->delete();

        return redirect()->route('ItemOrders');
    }

    public function Statistics(){
        $order['service']['all']['all'] = ServiceOrder::all()->count();
        $order['service']['all']['1'] = ServiceOrder::where('step',1)->count();
        $order['service']['all']['2'] = ServiceOrder::where('step',2)->count();
        $order['service']['all']['3'] = ServiceOrder::where('step',3)->count();


        $order['service']['month']['all'] = ServiceOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->count();
        $order['service']['month']['1'] = ServiceOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->where('step',1)->count();
        $order['service']['month']['2'] = ServiceOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->where('step',2)->count();
        $order['service']['month']['3'] = ServiceOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->where('step',3)->count();




        $order['shipping']['all']['all'] = ShippingOrder::all()->count();
        $order['shipping']['all']['1'] = ShippingOrder::where('step',1)->count();
        $order['shipping']['all']['2'] = ShippingOrder::where('step',2)->count();
        $order['shipping']['all']['3'] = ShippingOrder::where('step',3)->count();


        $order['shipping']['month']['all'] = ShippingOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->count();
        $order['shipping']['month']['1'] = ShippingOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->where('step',1)->count();
        $order['shipping']['month']['2'] = ShippingOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->where('step',2)->count();
        $order['shipping']['month']['3'] = ShippingOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->where('step',3)->count();

        $order['item']['all']['all'] = ItemOrder::all()->count();
        $order['item']['all']['1'] = ItemOrder::where('step',1)->count();
        $order['item']['all']['2'] = ItemOrder::where('step',2)->count();
        $order['item']['all']['3'] = ItemOrder::where('step',3)->count();

        $order['item']['month']['all'] = ItemOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->count();
        $order['item']['month']['1'] = ItemOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->where('step',1)->count();
        $order['item']['month']['2'] = ItemOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->where('step',2)->count();
        $order['item']['month']['3'] = ItemOrder::where('created_at' ,'>=',Carbon::now()->subMonth())->where('step',3)->count();


        return view('admin.statistics',compact('order'));
    }

    public function Feedbacks($role){
        $feedbacks = Feedback::
        where('role',$role)
            ->select('user_id')
            ->groupBy('user_id')
            ->orderBy('user_id','DESC')
            ->get();
        $users = [];

        if ($role == 1){
           foreach ($feedbacks as $feedback) {
               $client = Client::find($feedback->user_id);

               if ($client){
                   $users[] =$client;
               }
           }
       }
       else{
           foreach ($feedbacks as $feedback) {
               $client = Driver::find($feedback->user_id);
               if ($client){
                   $users[] =$client;
               }
           }
       }


        return view('admin.feedbacks',compact(['users','role']));
    }
    public function Feedback($role,$user_id){

       if ($role == 1){
           $user = Client::find($user_id);
           $feedbacks = Feedback::where('user_id',$user_id)->whereIn('role',[1,3])->get();

       }
       else{
           $user = Driver::find($user_id);
           $feedbacks = Feedback::where('user_id',$user_id)->whereIn('role',[2,3])->get();
       }

        return view('admin.feedback',compact(['user','role','feedbacks']));
    }
    public function ResFeedback(Request $request){
        $f = new Feedback();

        $f->user_id = $request['user_id'];
        $f->text = $request['text'];
        $f->role = 3;
        $f->save();

        return redirect()->back();
    }

    public function push($cat_id,$post_id,$title){
        $client = new Client;
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => 'key=AAAAsYqeQ4U:APA91bFmifTj_UD14mfTzC-QUGV4g3snFFRWG7wU8NacZvw_NYGgwUAvzhopdVys4cX7Wea902eVlZEcBckAU3ysRRrav3TeayguzSkeiaGt8i3Y2lvRDhd2KVhTxjWHGEbMHHocaacc',
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/cat_".$cat_id."_a",
                    "data" => [
                        "body" =>$title,
                        "title" => $title,
                        'post_id'=>$post_id,
                    ],
                ]]
        );
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => 'key=AAAAsYqeQ4U:APA91bFmifTj_UD14mfTzC-QUGV4g3snFFRWG7wU8NacZvw_NYGgwUAvzhopdVys4cX7Wea902eVlZEcBckAU3ysRRrav3TeayguzSkeiaGt8i3Y2lvRDhd2KVhTxjWHGEbMHHocaacc',
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/cat_$cat_id",
                    "notification" => [
                        "body" =>$title,
                        'post_id'=>$post_id,
                        "sound" => "default"
                    ]
                ]]
        );
    }

    public function Materials(){
        $materials = Material::all();

        return view('admin.settings.materials',compact('materials'));
    }
    public function CreateMaterial(Request $request){
        $m = new Material();
        $m->name = $request['name'];
        $m->save();
        return redirect()->back();
    }
    public function EditMaterial($id){
        $m = Material::find($id);

        return view('admin.settings.material_edit',compact('m'));
    }
    public function SaveMaterial(Request $request){
        $m = Material::find($request['id']);
        $m->name = $request['name'];
        $m->save();
        return redirect()->route('Materials');
    }
    public function DeleteMaterial($id){
        $m = Material::find($id);
        foreach (MaterialType::where('material_id',$id) as $t) {
            foreach (ItemOrder::where('material_type_id',$t->id)->get() as $order) {
                $to_address = Address::find($order->to_address_id);
                foreach (Image::where('parent_type','item_orders')->where('parent_id',$id)->get() as $item) {
                    $this->deletefile($item->path);
                    $item->delete();
                }

                $to_address->delete();
                $order->delete();
            }
            $t->delete();
        }
        $m->delete();
        return redirect()->back();
    }
    public function Options(){
        return view('admin.settings.options');
    }
    public function CreateOption(Request $request){
        $m =new Option();
        $m->name = $request['name'];
        $m->save();
        return redirect()->route('Options');
    }
    public function EditOption($id){
        $m = Option::find($id);

        return view('admin.settings.option_edit',compact('m'));
    }
    public function SaveOption(Request $request){
        $m = Option::find($request['id']);
        $m->name = $request['name'];
        $m->save();
        return redirect()->route('Options');
    }
    public function DeleteOption($id){
        $m = Option::find($id);
        $m->delete();
        return redirect()->back();
    }


    public function Cities(){
        return view('admin.settings.cities');
    }
    public function CreateCity(Request $request){
        $m =new City();
        $m->name = $request['name'];
        $m->save();
        return redirect()->back();
    }
    public function EditCity($id){
        $m = City::find($id);

        return view('admin.settings.city_edit',compact('m'));
    }
    public function SaveCity(Request $request){
        $m = City::find($request['id']);
        $m->name = $request['name'];
        $m->save();
        return redirect()->route('Cities');
    }
    public function DeleteCity($id){
        $m = City::find($id);
        $m->delete();
        return redirect()->back();
    }





    public function CreateMaterialType(Request $request){
        $m = new MaterialType();
        $m->name = $request['name'];
        $m->material_id = $request['material_id'];
        $m->save();
        return redirect()->back();
    }
    public function MaterialTypes($id){
        $materials = MaterialType::where('material_id',$id)->get();
        return view('admin.settings.material_types',compact(['materials','id']));
    }
    public function EditMaterialType($id){
        $m = MaterialType::find($id);
        return view('admin.settings.material_type_edit',compact('m'));
    }
    public function SaveMaterialType(Request $request){
        $m = MaterialType::find($request['id']);
        $m->name = $request['name'];
        $m->save();
        return redirect()->route('MaterialTypes',$m->material_id);
    }
    public function DeleteMaterialType($id){
        $m = MaterialType::find($id);
        foreach (ItemOrder::where('material_type_id',$id)->get() as $order) {
            $to_address = Address::find($order->to_address_id);
            foreach (Image::where('parent_type','item_orders')->where('parent_id',$id)->get() as $item) {
                $this->deletefile($item->path);
                $item->delete();
            }

            $to_address->delete();
            $order->delete();
        }
        $m->delete();

        return redirect()->back();
    }


    public function Transports($type){
        $transports = Transport::where('type',$type)->get();

        return view('admin.settings.transports',compact(['transports','type']));
    }
    public function CreateTransport(Request $request){
        $m = new Transport();
        $m->name = $request['name'];
        $m->type = $request['type'];
        $m->save();
        return redirect()->back();
    }
    public function EditTransport($id){
        $t = Transport::find($id);

        return view('admin.settings.transport_edit',compact('t'));
    }
    public function SaveTransport(Request $request){
        $m = Transport::find($request['id']);
        $m->name = $request['name'];
        $m->save();
        return redirect()->route('Transports');
    }
    public function DeleteTransport($id){
        $t = Transport::find($id);
        foreach (TransportType::where('transport_id',$id)->get() as $type){
            foreach (ServiceOrder::where('transport_type_id',$type->id)->get() as $order) {
                $address = Address::find($order->to_address_id);
                foreach (Image::where('parent_type','service_orders')->where('parent_id',$id)->get() as $item) {
                    $this->deletefile($item->path);
                    $item->delete();
                }

                $address->delete();
                $order->delete();
            }
            $type->delete();
        }

        foreach (ServiceOrder::where('transport_id',$id)->get() as $order) {
            $address = Address::find($order->to_address_id);
            foreach (Image::where('parent_type','service_orders')->where('parent_id',$id)->get() as $item) {
                $this->deletefile($item->path);
                $item->delete();
            }

            $address->delete();
            $order->delete();
        }
        foreach (ShippingOrder::where('transport_id',$id)->get() as $order) {
            $to_address = Address::find($order->to_address_id);
            $from_address = Address::find($order->from_address_id);
            foreach (Image::where('parent_type','shipping_orders')->where('parent_id',$id)->get() as $item) {
                $this->deletefile($item->path);
                $item->delete();
            }

            $to_address->delete();
            $from_address->delete();
            $order->delete();
        }

        $t->delete();
        return redirect()->back();
    }

    public function CreateTransportType(Request $request){
        $m = new TransportType();
        $m->name = $request['name'];
        $m->transport_id = $request['transport_id'];
        $m->save();
        return redirect()->back();
    }
    public function TransportTypes($id){
        $transport_types = TransportType::where('transport_id',$id)->get();
        return view('admin.settings.transport_types',compact(['transport_types','id']));
    }
    public function EditTransportType($id){
        $t = TransportType::find($id);
        return view('admin.settings.transport_type_edit',compact('t'));
    }
    public function SaveTransportType(Request $request){
        $m = TransportType::find($request['id']);
        $m->name = $request['name'];
        $m->save();
        return redirect()->route('TransportTypes',$m->transport_id);
    }
    public function DeleteTransportType($id){
        $t = TransportType::find($id);
        foreach (ServiceOrder::where('transport_type_id',$id)->get() as $order) {
            $address = Address::find($order->to_address_id);
            foreach (Image::where('parent_type','service_orders')->where('parent_id',$id)->get() as $item) {
                $this->deletefile($item->path);
                $item->delete();
            }

            $address->delete();
            $order->delete();
        }
        $t->delete();
        return redirect()->back();
    }




    public function OrderCancel(Request $request){
        $rules = [
            'order_type' => 'required',
            'order_id' => 'required',
            'user_id' => 'required',
            'user_type' => 'required',
        ];
        $validator = $this->validator($request->all(),$rules);
        if($validator->fails()) {
            $result['statusCode']= 400;
            $result['message']= $validator->errors();
            $result['result']= [];
        }
        else {

            $c= new CancelOrder();
                $c->order_type = $request['order_type'];
                $c->order_id = $request['order_id'];
                $c->user_id = $request['user_id'];
                $c->user_type= $request['user_type'];
                $c->text = $request['text'];
            $c->save();

            switch ($request['order_type']){
                case 'service_orders':
                    $order = ServiceOrder::find($request['order_id']);
                    if ($order){
                        $push = new Push();
                        if ($request['user_type'] == 1){
                            $user = Driver::find($order->driver_id);
                            if  ($user){
                                $push->OrderCancel($user->token,$order);
                            }
                        }
                        else{
                            $user = Client::find($order->client_id);
                            if  ($user){
                                $push->OrderCancel($user->token,$order);
                            }
                        }






                        $h = new History();
                        $h->parent_id =$request['order_id'];
                        $h->parent_type = $request['order_type'];
                        $h->user_id = $order->client_id;
                        $h->role = 1;
                        $h->save();

                        $h = new History();
                        $h->parent_id = $order->id;
                        $h->parent_type = $order->type;
                        $h->user_id = $order->driver_id;
                        $h->role = 2;

                        $result['statusCode'] = 200;
                        $result['message'] = "success";
                        $result['result'] = [];
                        $h->save();


                        $order->step = 1;
                        $order->driver_id = null;
                        $order->save();

                    }
                    else{
                        $result['statusCode'] = 404;
                        $result['message'] = "order not found";
                        $result['result'] = [];
                    }
                    break;
                case 'shipping_orders':
                    $order = ShippingOrder::find($request['order_id']);

                    if ($order){


                        $push = new Push();
                        if ($request['user_type'] == 1){
                            //Это клиент
                            $drivers =  DB::table('shipping_drivers')->where('shipping_order_id',$order->id)->get();
                            foreach ($drivers as $u) {
                                $user = Driver::find($u->driver_id);
                                if  ($user){
                                    $push->OrderCancel($user->token,$order);
                                }
                            }

                            $order->step = 1;
                            $order->driver_id = null;
                            $order->save();
                        }
                        else{
                            //driver
                            $user = Client::find($order->client_id);
                            if  ($user){
                                $push->OrderCancel($user->token,$order);
                            }
                            DB::table('shipping_drivers')
                                ->where('shipping_order_id',$order->id)
                                ->where('driver_id',$request['user_id'])
                                ->delete();
                            $drivers =  DB::table('shipping_drivers')->where('shipping_order_id',$order->id)->get();

                            if (count($drivers) == 0) {
                                $order->step = 1;
                                $order->driver_id = null;
                                $order->save();
                            }

                        }





                        $result['statusCode'] = 200;
                        $result['message'] = "success";
                        $result['result'] = [];
                        break;
                    }
                    else{
                        $result['statusCode'] = 404;
                        $result['message'] = "order not found";
                        $result['result'] = [];
                        break;
                    }
                case 'item_orders':

                    $order = ItemOrder::find($request['order_id']);
                    if ($order){

                        $push = new Push();
                        if ($request['user_type'] == 1){
                            $user = Driver::find($order->driver_id);
                            if  ($user){
                                $push->OrderCancel($user->token,$order);
                            }
                        }
                        else{
                            $user = Client::find($order->client_id);
                            if  ($user){
                                $push->OrderCancel($user->token,$order);
                            }
                        }


                        $h = new History();
                        $h->parent_id =$request['order_id'];
                        $h->parent_type = $request['order_type'];
                        $h->user_id = $order->client_id;
                        $h->role = 1;
                        $h->save();

                        $h = new History();
                        $h->parent_id = $order->id;
                        $h->parent_type = $order->type;
                        $h->user_id = $order->driver_id;
                        $h->role = 2;
                        $h->save();


                        $order->step = 1;
                        $order->driver_id = null;
                        $order->save();



                        $result['statusCode'] = 200;
                        $result['message'] = "success";
                        $result['result'] = [];
                        break;
                    }
                    else{
                        $result['statusCode'] = 404;
                        $result['message'] = "order not found";
                        $result['result'] = [];
                        break;
                    }
                    break;
                default:
                    $result['statusCode'] = 400;
                    $result['message'] = "error";
                    $result['result'] = [];
            }

            $offers = Offer::where('parent_type',$request['order_type'])->where('parent_id',$request['order_id'])->get();
            foreach ($offers as $item) {
                $item->delete();
            }


        }

        return $result;
    }
    public function OrderCancels(Request $request){
        $items = \App\Models\CancelOrder::all();
        $orders = [];

        foreach ($items as $item) {
            $err = 0;
            $data['id'] = $item->id;
            $data['user_id'] = $item->user_id;
            $data['user_type'] = $item->user_type;
            $data['text'] = $item->text;
            if ($item->user_type == 1){
              $user = Client::find($item->user_id);
               if  ($user){
                    $data['user'] = $user;
               }
              else{
                 $err= 1;
              }
            }
            else{
                $user = Driver::find($item->user_id);
                if  ($user){
                    $data['user'] = $user;
                }
                else{
                    $err= 1;;
                }
            }


            if ($item->order_type == 'service_orders'){
               $order = ServiceOrder::find($item->order_id);
               if  ($order){
                   $data['order_type'] = 'Услуги спец техники';
                   $data['order'] = $order;
               }
               else{
                   $err= 1;
               }
           }
            elseif  ($item->order_type == 'shipping_orders'){
               $order = ShippingOrder::find($item->order_id);

                if  ($order){
                    $data['order_type'] = 'Перевозка грузов';
                    $data['order'] = $order;
               }
               else{
                   $err= 1;;
               }
           }
            elseif ($item->order_type == 'item_orders'){
               $order = ItemOrder::find($item->order_id);
               if  ($order){
                   $data['order_type'] = 'Материялы';
                   $data['order'] = $order;
               }
               else{
                   $err= 1;;
               }
           }
           else{
                $err =1;
           }



            if ( $err != 1) {
                $orders[] = $data;
            }

        }
        return view('admin.cancel_orders',compact('orders'));
    }
}
