<?php

namespace App;
use App\Models\Offer;
use GuzzleHttp\Client;
use DB;
use GuzzleHttp\Psr7\Request;

class Push{

    protected $key = 'key=AAAAJUdt4sI:APA91bHicddiXsZ7vwVlC-Kbe_5OhFZ2MBFhFoGoW9nPBSEKGWbBGwaQa53ZuZqsV-PkpiPAku3OWlzNkYuYI-0mW7HsS3__8SSMEAT7yc2DoJ9J6g1t50Mr-8FRGHrMux_W7dVsLjpr';

    public function OfferCreate($client_token,$order_type,$order_id,$offer_id,$driver_id){
        $order_type = str_replace('_orders','',$order_type);
        $client = new Client;
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$client_token"."a",
                    "data" => [
                        "body" => "у вас новый отклик",
                        "title" => "Easy",
                        'order_type'=> $order_type,
                        'order_id'=> $order_id,
                        'offer_id'=> $offer_id,
                        'driver_id'=> $driver_id,
                        'step'=> 1,
                    ],
                ]]
        );
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$client_token",
                    "notification" => [
                        "body" => "у вас новый отклик",
                        "title" => "Easy",
                        'order_type'=> $order_type,
                        'order_id'=> $order_id,
                        'offer_id'=> $offer_id,
                        'driver_id'=> $driver_id,
                        'step'=> 1,
                        "sound" => "default"
                    ]
                ]]
        );
    }

    public function OfferCancel($driver_token,$order_type,$order_id,$offer_id,$client_id){
        $order_type = str_replace('_orders','',$order_type);
        $offer = Offer::find($offer_id);
        $client = new Client;
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$driver_token"."a",
                    "data" => [
                        "body" => "Вашу цену $offer->price тенге Отказали, попробуйте еще раз",
                        "title" => "Easy",
                        'order_type'=> $order_type,
                        'order_id'=> $order_id,
                        'offer_id'=> $offer_id,
                        'client_id'=> $client_id,
                        'step'=> 4
                    ],
                ]]
        );
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$driver_token",
                    "notification" => [
                        "body" => "Вашу цену $offer->price тенге Отказали, попробуйте еще раз",
                        "title" => "Easy",
                        'order_type'=> $order_type,
                        'order_id'=> $order_id,
                        'offer_id'=> $offer_id,
                        'client_id'=> $client_id,
                        'step'=> 4,
                        "sound" => "default"
                    ]
                ]]
        );

    }
    //Клиент принимает отклик
    public function Access($driver_token,$order_type,$order_id,$offer_id,$client_id){
        $order_type = str_replace('_orders','',$order_type);
        $client = new Client;
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$driver_token"."a",
                    "data" => [
                        "body" => "Клиент принял ваше предложение",
                        "title" => "Easy",
                        'order_type'=> $order_type,
                        'order_id'=> $order_id,
                        'offer_id'=> $offer_id,
                        'client_id'=> $client_id,
                        'step'=> 2
                    ],
                ]]
        );
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$driver_token",
                    "notification" => [
                        "body" => "Клиент принял ваше предложение",
                        "title" => "Easy",
                        'order_type'=> $order_type,
                        'order_id'=> $order_id,
                        'offer_id'=> $offer_id,
                        'client_id'=> $client_id,
                        'step'=> 2,
                        "sound" => "default"
                    ]
                ]]
        );
    }

    public function OrderCancel($token,$order){
        $client = new Client;
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$token"."a",
                    "data" => [
                        "body" => "Текущий заказ на сумму $order->price отменен",
                        "title" => "Easy",
                        'order_type'=> $order->type,
                        'order_id'=> $order->id,
                        'offer_id'=> 0,
                        'step'=> 5
                    ],
                ]]
        );
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$token",
                    "notification" => [
                        "body" => "Текущий заказ на сумму $order->price отменен",
                        "title" => "Easy",
                        'order_type'=> $order->type,
                        'order_id'=> $order->id,
                        'offer_id'=> 0,
                        'step'=> 5,
                        "sound" => "default"
                    ]
                ]]
        );
    }

    public function End($client_token,$order_type,$order_id,$client_id){
        $order_type = str_replace('_orders','',$order_type);
        $client = new Client;
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$client_token"."a",
                    "data" => [
                        "body" => "Клиент закончил заявку",
                        "title" => "Easy",
                        'order_type'=> $order_type,
                        'order_id'=> $order_id,
                        'client_id'=> $client_id,
                        'step'=> 3
                    ],
                ]]
        );
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$client_token",
                    "notification" => [
                        "body" => "Клиент закончил заявку",
                        "title" => "Easy",
                        'order_type'=> $order_type,
                        'order_id'=> $order_id,
                        'client_id'=> $client_id,
                        'step'=> 3,
                        "sound" => "default"
                    ]
                ]]
        );
    }

    public function Invitation($client_token,$group_id){
        $client = new Client;
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$client_token"."a",
                    "data" => [
                        "body" => "Приглашения",
                        "title" => "Easy",
                        'order_type'=> "invite",
                        'group_id'=> $group_id,
                    ],
                ]]
        );
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$client_token",
                    "notification" => [
                        "body" => "Приглашения",
                        "title" => "Easy",
                        'order_type'=> "invite",
                        'group_id'=> $group_id,
                        "sound" => "default"
                    ]
                ]]
        );
    }

    public  function List(array $arr){
        $tokens = [];
        $client = \App\Models\Client::find($arr['client_id']);
        if ($arr['type'] == 'service_orders'){
            $tokens = DB::table('drivers')
                ->where('drivers.city_id','=',$client->city_id)

                ->join('cars','cars.driver_id','=','drivers.id')
                ->join('car_transports','car_transports.car_id','=','cars.id')
                ->join('transports','transports.id','=','car_transports.transport_id')
                ->where('transports.id','=',$arr['transport_id'])
                ->pluck('token')
                ->toArray();
        }
        elseif ($arr['type'] == 'shipping_orders'){
            $tokens = DB::table('drivers')

                ->join('cars','cars.driver_id','=','drivers.id')
                ->join('car_transports','car_transports.car_id','=','cars.id')
                ->join('transports','transports.id','=','car_transports.transport_id')
                ->where('drivers.city_id','=',$arr['from_city_id'])
                ->where('transports.id','=',$arr['transport_id'])
                ->pluck('drivers.token')
                ->toArray();
        }
        elseif ($arr['type'] == 'item_orders'){
            $tokens = DB::table('drivers')
                ->join('cars','cars.driver_id','=','drivers.id')
                ->join('car_materials','car_materials.car_id','=','cars.id')
                ->join('materials','materials.id','=','car_materials.material_id')
                ->where('drivers.city_id','=',$client->city_id)
                ->where('materials.id','=',$arr['material_id'])
                ->pluck('drivers.token')
                ->toArray();
        }


        $url = 'https://fcm.googleapis.com/fcm/send';
        $clientGuzzle = new Client();
        foreach ($tokens as $token) {
            $dataA= [
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$token".'a',
                    "data" => [
                        "body" => "Новый заказ!",
                        "title" => "Easy",
                        'order_type'=> mb_substr($arr['type'], 0, -7),
                        'order_id'=> $arr['id']
                    ]
                ]
            ];
            $dataI= [
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$token",
                    "notification" => [
                        "body" => "Новый заказ!",
                        "title" => "Easy",
                        'order_type'=> mb_substr($arr['type'], 0, -7),
                        'order_id'=> $arr['id'],
                        "sound" => "default"
                    ]
                ]
            ];
            $requestA = $clientGuzzle->postAsync( $url, $dataA);
            $requestI = $clientGuzzle->postAsync( $url, $dataI);
        }
        if (count($tokens) != 0){
            $requestA->wait();
            $requestI->wait();
        }


    }

    public function NewMessage($token,$message,$chat_id,$user_id){
        $client = new Client;
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$token"."a",
                    "data" => [
                        "body" => $message,
                        "title" => "Easy",
                        'chat_id'=> $chat_id,
                        'user_id'=> $user_id,
                    ],
                ]]
        );
        $client->request('POST','https://fcm.googleapis.com/fcm/send',[
                'headers' => [
                    'Authorization' => $this->key,
                    'Content-Type'     => 'application/json',
                ],
                'json' =>[
                    "to" => "/topics/$token",
                    "notification" => [
                        "body" => $message,
                        "title" => "Easy",
                        'chat_id'=> $chat_id,
                        'user_id'=> $user_id,
                        "sound" => "default"
                    ]
                ]]
        );
    }
}