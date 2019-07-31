<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatroomUser extends Model
{
    protected $table = 'chatroom_user';

    public function users(){
    	return $this->hasOne('App\User','id','user_id');
    }

    public function chatroom(){
        return $this->hasOne('App\Models\Chatroom','id','chatroom_id');
    }
}
