<?php

namespace App\Http\Resources;

use App\ChatRoomFeed;
use App\Models\ChatroomMessage;
use App\Models\UnreadMessage;
use Illuminate\Http\Resources\Json\JsonResource;

class Participant extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user_avatar="";
        $user_name="";
        $user_id="";
        // $totalMesg =0;
        if($this->type=='private'){            
            foreach($this->participants as $user){
                if($user->user_id!=auth()->user()->id && $user->user_id!=0 && $user->user_id!=NULL){
                    $user_name = $user->users->name;
                    $user_id   = $user->users->id;
                }
            }
        }else{
            $user_name = $this->title;
        }
        $lastMessage = ChatroomMessage::where('chatroom_id',$this->id)->orderBy('id','desc')->first();
        $unreadcount = UnreadMessage::where('chatroom_id',$this->id)->where('user_id',auth()->user()->id)->count();
        return [
            'room_id'         => $this->id,
            'user_id'         => $user_id,
            'room_type'       => $this->type,
            'roomname'        => $user_name,
            'initiator_id'    => $this->initiator_id,
            'user_avatar'     => $this->icon,
            'unreadcount'     => $unreadcount,
            'lastmessage'     => ($lastMessage['message']) ? $lastMessage['message'] : "",
            'lastmessagetime' => ($lastMessage['created_at']) ? $lastMessage['created_at']->format('Y-m-d H:i:s') : $this->created_at->format('Y-m-d H:i:s'),
            'displaylastmessagetime' => ($lastMessage['created_at']) ? $lastMessage['created_at']->format('y-m-d h:i:s a') : $this->created_at->format('Y-m-d H:i:s'),
        ];

    }
}
