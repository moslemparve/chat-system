<?php

namespace App\Models;

use App\Models\ChatroomMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Chatroom extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];

    public function users() {
        return $this->belongsToMany('App\User');
    }

    public function messages() {
        return $this->hasMany(ChatroomMessage::class);
    }
    public function participants()
    {
        return $this->hasMany(ChatroomUser::class, 'chatroom_id', 'id');
    }

    public function lastmessage(){
        return $this->hasMany(ChatroomMessage::class, 'chatroom_id', 'id');
    }
}
