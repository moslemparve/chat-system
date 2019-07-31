<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
class ChatroomMessage extends Model implements HasMedia
{
    use SoftDeletes, HasMediaTrait;

    protected $guarded = [];

    public function registerMediaCollections()
    {
        $this
            ->addMediaCollection('connect_files')
            ->useDisk('do_chat')
            ->singleFile();
    }

    public function chatroom() {
        return $this->belongsTo('App\Models\Chatroom');
    }

    public function user() {
        return $this->belongsTo('App\User', 'sender_id', 'id');
    }

    public function file(){
        return $this->media();
    }
}
