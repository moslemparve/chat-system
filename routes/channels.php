<?php
use App\Models\ChatroomUser;

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat', function ($user) {
    return $user;
});

Broadcast::channel('newMessage.{id}', function ($user, $rooms) {
    $chatrooms = ChatroomUser::where('user_id',$user->id)->pluck('chatroom_id')->toArray();
    return in_array($rooms, $chatrooms); 
});

Broadcast::channel('EditMessage.{id}', function ($user, $rooms) {
    $chatrooms = ChatroomUser::where('user_id',$user->id)->pluck('chatroom_id')->toArray();
    return in_array($rooms, $chatrooms); 
});

Broadcast::channel('DeleteMessage.{id}', function ($user, $rooms) {
    $chatrooms = ChatroomUser::where('user_id',$user->id)->pluck('chatroom_id')->toArray();
    return in_array($rooms, $chatrooms); 
});

Broadcast::channel('GroupDelete.{id}', function ($user, $rooms) {
    $chatrooms = ChatroomUser::where('user_id',$user->id)->pluck('chatroom_id')->toArray();
    return in_array($rooms, $chatrooms); 
});

Broadcast::channel('newroomcreated.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});