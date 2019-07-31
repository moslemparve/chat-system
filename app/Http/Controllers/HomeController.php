<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chatroom;
use App\User;
use App\Models\ChatroomUser;
use App\Models\ChatroomMessage;
use App\Models\UnreadMessage;
use App\Events\NewMessage;
use App\Events\EditMessage;
use App\Events\DeleteMessage;
use App\Events\GroupDelete;
use App\Events\NewRoom;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\GroupMemberCollection;
use App\Http\Resources\MessageResource;
use App\Http\Resources\MessageResourceCollection;
use App\Http\Resources\Participant;
use App\Http\Resources\ParticipantCollection;
use Auth;
use Storage;
use File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\MediaLibrary\Models\Media;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //Load Chat View with User Data
    public function index(){
        $auth_user_id = Auth::user()->id;
        $rooms = ChatroomUser::where('user_id', $auth_user_id)->pluck('chatroom_id');
        $crooms = Chatroom::whereIn('id',$rooms)->where('type','private')->pluck('id');
        $added_users_id = ChatroomUser::whereIn('chatroom_id', $crooms)->pluck('user_id');
        $allusers = User::whereNotIn('id',$added_users_id)->where('id','!=',$auth_user_id)->get();
        $allcontacts = User::where('id','!=',$auth_user_id)->get();
        return view('home',compact('allusers','rooms','allcontacts'));
    }

    // Create Chat Rooms Function Starts
    public function newprivatechat(Request $request){
        $auth_user_id = Auth::user()->id;
        $chatrooms = ChatRoom::where('type', 'private')->whereHas('participants', function ($query) use ($auth_user_id) {
            $query->where('user_id', '=', $auth_user_id);
        })
            ->with('participants')
            ->get();

        $count = 0;
        foreach ($chatrooms as $rooms) {
            foreach ($rooms->participants as $value) {
                if ($value->user_id == $request->user_id) {
                    $count++;
                }
            }
        }

        if ($count <= 0) {
            //create new chat room
            $chatroom = new Chatroom;
            $chatroom->title = 'New Chat Room';
            $chatroom->type = 'private';
            $chatroom->initiator_id = $auth_user_id;
            $chatroom->save();

            // Add Chat room members to chat room
            $chatroomuser = new ChatroomUser;
            $chatroomuser->chatroom_id = $chatroom->id;
            $chatroomuser->user_id = $request->user_id;
            $chatroomuser->save();
            // Notify User
            broadcast(new NewRoom($chatroomuser));
            $chatroomuser = new ChatroomUser;
            $chatroomuser->chatroom_id = $chatroom->id;
            $chatroomuser->user_id = $auth_user_id;
            $chatroomuser->save();
            // Notify User
            broadcast(new NewRoom($chatroomuser));
            return response($chatroom);
        }else{
            echo "Already Created";
        }            
    }

    public function getUsersChatRooms(){
        $auth_user_id = Auth::user()->id;
        $chatrooms = Chatroom::whereHas('participants', function ($query) use ($auth_user_id) {
            $query->where('user_id', '=', $auth_user_id);
        })
        ->with('participants')
        ->get();
        return response(new ParticipantCollection($chatrooms));
    }

    //Get All Messages of single ChatRoom

    public function getRoomConversations($room_id){
        if(!request()->has('page')){
            UnreadMessage::where('chatroom_id', $room_id)->where('user_id', auth()->user()->id)->delete();
        }
        $data = ChatroomMessage::where('chatroom_id', $room_id)->withTrashed()->get();
        $data = ChatroomMessage::where('chatroom_id', $room_id)->withTrashed()->orderBy('id','desc')->paginate(20);
        
        return (new MessageResourceCollection($data))->chatroom_id($room_id);
        // return response(new MessageResourceCollection($data));
    }

    // Send Message
    public function sendMessage(Request $request){
        $messagebody = '';
        if($request->quoting){
            $messagebody .= $request->qoutemessagebody;
        }
        $messagebody1 = $request->message;
        $array = explode(' ',$messagebody1);
        foreach($array as $arr){
            if(strpos( $arr, '@' ) !== false) {
                $highlight = '<a href="#">'.$arr.'</a>';
                $messagebody1 = str_replace($arr,$highlight,$messagebody1);
            }
        }

        $messagebody = $messagebody . $messagebody1;
        $user_id = Auth::user()->id;
        $newMessage = new ChatroomMessage;

        if ($request->hasfile('file')) {
           
            $newMessage->is_file = 1;
            $newMessage->message = $messagebody;
        } else {
            $newMessage->is_file = 0;
            $newMessage->message = $messagebody;
        }

        $newMessage->chatroom_id = $request->room_id;
        $newMessage->sender_id = $user_id;
        $newMessage->save();

        
        $chatroomusers = ChatroomUser::where('chatroom_id', $request->room_id)->pluck('user_id');
        foreach ($chatroomusers as $key => $value) {
            $unreadMessage = new UnreadMessage;
            if ($user_id != $value) {
                $unreadMessage->chatroom_id = $request->room_id;
                $unreadMessage->user_id = $value;
                $unreadMessage->message_id = $newMessage->id;
                $unreadMessage->save();
            }
        }
        
        broadcast(new newMessage($newMessage));

        return response(new MessageResource($newMessage));
    }

    public function editMessage(Request $request){
        $editMessage = ChatroomMessage::findOrFail($request->message_id);
        $editMessage->message = $request->newmessage . " <b class='text-muted'>( Edited )</b>";
        $editMessage->save();

        broadcast(new EditMessage($editMessage,$request->index));
    }

    public function createNewGroup(Request $request){
        $group_participants = rtrim($request->users, ',');
        $group_participants = explode(',', $group_participants);
        
        $auth_user_id = Auth::user()->id;
        //create new chat room
        $chatroom = new Chatroom;
        $chatroom->title = $request->groupname;
        $chatroom->type = 'group';
        $chatroom->initiator_id = $auth_user_id;
        // $chatroom->room_status = 'active';
        
        // Upload Logo 
        if ($request->hasfile('logo')) {
            $file = $request->file('logo');
            $filename = time() . $file->getClientOriginalName();
            Storage::disk('public')->put($filename, File::get($request->logo));
            $chatroom->icon = $filename;
        } 
        $chatroom->save();

        if ($chatroom) {
            //All Users insertion begins
            foreach ($group_participants as $value) {

                $chatParticipants = new ChatroomUser;
                $chatParticipants->chatroom_id = $chatroom->id;
                $chatParticipants->user_id = $value;
                $chatParticipants->save();

                broadcast(new NewRoom($chatParticipants));
            }
            //All Users insertion end

            //Current Login User insertion begins    
            $chatParticipants = new ChatroomUser;
            $chatParticipants->chatroom_id = $chatroom->id;
            $chatParticipants->user_id = auth()->user()->id;
            $chatParticipants->save();
            broadcast(new NewRoom($chatParticipants));
            //Current Login User insertion ends
        }


        return response()->json(['success' => 1]);
        
    }
    public function deleteMessage(Request $request){
        $deletedMessage = ChatroomMessage::where('id',$request->message_id)->first();
        ChatroomMessage::where('id',$request->message_id)->delete();
        broadcast(new DeleteMessage($deletedMessage,$request->index));
    }

    public function getGroupMembers($chatroom_id){
        $members = ChatroomUser::where('chatroom_id', $chatroom_id)->get();
        return response(new GroupMemberCollection($members));
    }

    public function getallusernotingroup($chatroom_id){
        $auth_user_id = Auth::user()->id;
        $added_users_id = ChatroomUser::where('chatroom_id', $chatroom_id)->pluck('user_id');
        $allusers = User::whereNotIn('id',$added_users_id)->where('id','!=',$auth_user_id)->get();
        return response()->json($allusers);
    }

    public function addUserToGroup(Request $request){
        $group_participants = rtrim($request->users, ',');
        $group_participants = explode(',', $group_participants);

        //All Users insertion begins
        foreach ($group_participants as $value) {
            $check = ChatroomUser::where('user_id',$value)->first();
            if(!empty($check)){
                $chatParticipants = new ChatroomUser;
                $chatParticipants->chatroom_id = $request->chatroom_id;
                $chatParticipants->user_id = $value;
                $chatParticipants->save();
                broadcast(new NewRoom($chatParticipants));
            }else{
                echo "Already Exists";
            }

        }
        //All Users insertion end
    }
    public function removeUserfromGroup(Request $request){
        ChatroomUser::where('chatroom_id',$request->chatroom_id)->where('user_id',$request->user_id)->delete();

    }

    public function deletegroup(Request $request){
        // dd($request->all());
        $chatroom_id = $request->chatroom['room_id'];
        $chatroom_users = ChatroomUser::where('chatroom_id',$chatroom_id)->get();
        foreach($chatroom_users as $user){
            broadcast(new GroupDelete($user));
        }
        Chatroom::where('id',$chatroom_id)->delete();
    }

    // Upload File
    public function uploadfile(Request $request){
        // dd($request->all());
        $file = $request->file('file');
        $name = $file->getClientOriginalName();
        //Storage::disk('do')->put('/PMS/chat/'.$name,file_get_contents($request->file('file')->getRealPath()),'public');
        // $url = Storage::disk('do')->url('/PMS/chat/'.$name);
        // $cdn_url = str_replace('digitaloceanspaces','cdn.digitaloceanspaces',$url);
        $user_id = auth()->user()->id;
        $newMessage = new ChatroomMessage;
        $newMessage->is_file = 1;
        // $newMessage->message = $cdn_url;
        $newMessage->chatroom_id = $request->chatroom_id;
        $newMessage->sender_id = $user_id;
        $newMessage->save();

        $newMessage->addMedia($request->file)->usingFileName($name)->toMediaCollection();
        
        broadcast(new newMessage($newMessage));
      
    }

    public function downloadfile($id){
        $messge = ChatroomMessage::find($id);
        return response()->download($messge->getMedia()[0]->getPath(), $messge->getMedia()[0]->file_name);
    }
}
