<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\ChatroomMessage;

class MessageResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    protected $chatroom_id;

    public function chatroom_id($value){
        $this->chatroom_id = $value;
        return $this;
    }

    public function toArray($request)
    {
        return parent::toArray($request);
    }
    public function with($request)
    {
        
        return [
            'meta' => [
                'sayhi' => ChatroomMessage::where('chatroom_id',$this->chatroom_id)->where('sender_id',auth()->user()->id)->count(),
            ],
        ];
    }
}
