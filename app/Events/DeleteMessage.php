<?php

namespace App\Events;

use App\Models\ChatroomMessage;
use App\Http\Resources\MessageResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DeleteMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $message;
    public $index;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ChatroomMessage $message,$index)
    {
        $this->message = $message;
        $this->index = $index;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('DeleteMessage.' . $this->message->chatroom_id);
    }

    public function broadcastWith()
    {
        // (new UserResource($user))->foo('bar');
        // (new MessageResource($this->message))->messageIndex($this->index)
        return ["deletedmessage" => (new MessageResource($this->message))->messageIndex($this->index)];
    }
}
