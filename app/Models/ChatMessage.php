<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
   protected $hidden = ['created_at','updated_at'];
}
