<template>
    <div>
	    <div class="contact-profile">
            <span v-if="selecteduser" 
                class="sender-avatar-icon"
                data-toggle="modal" 
                data-target="#groupMembersModal">
                {{ selecteduser.roomname.toUpperCase().charAt(0) }}
            </span>
            <!-- <span v-else-if="selecteduser" class="sender-avatar-icon">
                {{ selecteduser.roomname.toUpperCase().charAt(0) }}
            </span> -->

            <p v-if="selecteduser"><b>{{ selecteduser.roomname}}</b></p>
            <p v-else>Start a new chat</p>
            
            <span class="typing_indicator float-right"></span>
            
        </div>
        <div class="messages">
            <img src="/custom/loader.gif" style="position:absolute;top:0;left:50%;width:20px;z-index:100;" v-show="loading_more">
            <img src="/custom/loader.gif" style="position:absolute;top:30%;left:50%;width:50px;z-index: 100;" v-if="!loading_chat">
            <div v-if="loading_chat && !hasChatHistory && !temphasChatHistory">
                <h4 style="position:absolute;top:40%;left:44%;">No Messages Yet</h4>
                <button class="btn btn-primary" style="position:absolute;top:50%;left:50%;" @click="sendFirstHi()">Say Hi</button>
            </div>
            <ul class="messages"  v-else v-on:scroll="scrollevent()" v-chat-scroll="{always: false}" id="message_container">
                <li class="sent message sent_message" v-for="(message,index) in messages" :key="index" v-if="message.sender_id==user.id">
                     <span v-if="checkdate(message)" class="date_line">
                         <span class="subtitle fancy"><span>{{message.group_at}}</span></span>
                     </span>
                     <span class="float-right">
                         <div v-if="message.message_deleted=='0'" class="dropdown float-right actionbuttonsender">
                            <button class="btn" type="button" id="dropdownMenuButton" 
                                    data-toggle="dropdown" 
                                    aria-haspopup="true" 
                                    aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <span v-if="message.is_file =='0'" class="dropdown-item" @click="forwardMessageModal(message,index)"><i class="fa fa-share" ></i> Forward</span>
                                <span v-if="message.is_file =='0'" class="dropdown-item" @click="quoteMessage(message,index)"><i class="fa fa-quote-left" ></i> Quote</span>
                                <span v-if="message.is_file =='0'" class="dropdown-item" @click="openEditMessageEditor(message,index)"><i class="fa fa-edit" ></i> Edit</span>
                                <span class="dropdown-item" @click="delteMessage(index,message.message_id)"><i class="fa fa-trash"></i> Delete</span>
                            </div>
                        </div>
                     </span>
                    
                    <div class="messages_pre_wrap message_text_10">
                        <span class="text-muted sendmessagetime">{{ message.created_at }}</span>
                    </div>
                    
                    <p class="text-muted" v-if="message.message_deleted!='0'" :class="messagetextprefix + message.message_id">{{ message.message_deleted }}</p>
                    <p v-else-if="message.is_file=='0'" :class="messagetextprefix + message.message_id" v-html="message.message" v-linkified v-bind:style="{maxWidth : message.message.length > 300 ? '80%' : '' }"></p>
                    <div v-else>
                        <a v-if="message.is_image" :href="`/media/${message.file_id}`">
                            <img :src="message.fileurl" class="file_thumbnail"/>
                        </a>
                        <p v-else class="file_container_send card text-center" style="white-space: unset; max-width: 200px;"> 
                            <a :href="`/media/${message.file_id}`">
                              <span class="text-center"><i class="fa fa-file fa-2x" /></span>
                              <br/>
                              <br/>
                              <span class="text-center p-3" style="font-size: 16px; margin: 10px;">  {{ message.file_name }} </span>
                              <br/>
                              <span>{{ message.file_size }}</span>
                            </a>
                             <!-- <br> <hr> <a :href="`/media/${message.file_id}`"> <h6> Download </h6> </a> -->
                             </a>
                        </p>
                    </div>
                </li>
                <li v-else class="replies message">
                    <span v-if="checkdate(message)" class="date_line1">
                         <span class="subtitle fancy"><span>{{message.group_at}}</span></span>
                     </span>
                    <span class="sender-avatar-icon">{{ message.sender_name.toUpperCase().charAt(0) }}</span>
                    <div v-if="message.message_deleted=='0'" class="dropdown float-right actionsbuttonreply">
                        <button class="btn" type="button" id="dropdownMenuButton" 
                                data-toggle="dropdown" 
                                aria-haspopup="true" 
                                aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <span v-if="message.is_file =='0'" class="dropdown-item" @click="forwardMessageModal(message,index)"><i class="fa fa-share" ></i> Forward</span>
                            <span v-if="message.is_file =='0'" class="dropdown-item" @click="quoteMessage(message,index)"><i class="fa fa-quote-left" ></i> Quote</span>
                        </div>
                    </div>
                    <div class="messages_pre_wrap message_text_10">
                        <span v-if="selecteduser.room_type =='group'" class="text-muted groupsendername" >{{ message.sender_name.split(' ')[0] }}</span>
                        <span class="text-muted recievemessagetime">{{ message.created_at }}</span>
                    </div>
                    <p class="text-muted" v-if="message.message_deleted!='0'" :class="messagetextprefix + message.message_id">{{ message.message_deleted }}</p>
                    <p v-else-if="message.is_file=='0'" :class="messagetextprefix + message.message_id" v-html="message.message" v-linkified  v-bind:style="{maxWidth : message.message.length > 300 ? '80%' : '' }"></p>
                    <div v-else>
                        <a v-if="message.is_image" :href="`/media/${message.file_id}`">
                            <img :src="message.fileurl" class="file_thumbnail1"/>
                        </a>
                        <p v-else class="file_container_recieve card text-center" style="white-space: unset; max-width: 200px;"> 
                            <!-- <a :href="`/media/${message.file_id}`">
                                  <span class="float-left p-3">  {{ message.file_name }} </span><br>
                              <span class="float-left"><i class="fa fa-file fa-1x" /> | {{ message.file_size }} </span>
                             <br> <hr> <a :href="`/media/${message.file_id}`">  <h6> Download </h6> </a>
                             </a> -->
                             <a :href="`/media/${message.file_id}`">
                              <span class="text-center"><i class="fa fa-file fa-2x" /></span>
                              <br/>
                              <br/>
                              <span class="text-center p-3" style="font-size: 16px; margin: 10px;">  {{ message.file_name }} </span>
                              <br/>
                              <span>{{ message.file_size }}</span>
                            </a>
                        </p>
                    </div>                  
                </li>
            </ul>
        </div>
        <div class="quotemesg" style="white-space: unset;" v-if="qoutemessagebody!=''">
            <div style="white-space: unset;">
                <i class="fa fa-quote-left fa-1x"></i> <br>
                 <span style="margin: 0;" v-html="qoutemessagebody"></span>
               <br>
                <b style="" v-text="quotemessagename"></b>
                <hr>
            </div>
        </div>
        <i v-if="qoutemessagebody!=''" @click="cancelquotemessage" class="quotemesgclose fa fa-close"></i>
        <ComposeMessage ref="messagecomposercomponent" @send="sendMessage" :selectedContact="selectedContact" :user="user"></ComposeMessage>
        <!-- Group Members Model Starts -->
        <div class="modal fade" id="groupMembersModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <p v-if="selecteduser">{{ selecteduser.roomname}} 
                            <button v-if="group_permission===1" type="button" 
                                    class="btn btn-primary deletegroupbutton" 
                                    data-toggle="modal" 
                                    data-target="#deleteGroupConfirmation"
                                    @click="hideGroupmodel">
                                    <i  class="fa fa-trash"></i>
                            </button>
                        </p>
                        <button  type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div style="width:100%;" v-if="selecteduser && group_permission===1 && selecteduser.room_type == 'group'">
                            <div style="width:80%;float:left;">
                                <multiselect v-model="selectedMembers"  
                                        track-by="name" label="name" 
                                        placeholder="Add Members" 
                                        :options="addnewtogrouplist" 
                                        :searchable="true"
                                        :multiple="true">
                                        <template slot="singleLabel" slot-scope="{ option }">
                                            <strong>{{ option.name }}</strong>
                                        </template>
                                </multiselect>
                            </div>
                            <div style="width:20%;float:left;text-align:right;">
                                <button class="btn btn-success" style="height: 45px;" @click="addMemberstoGroup()"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <p class="text-danger" v-if="selecteduser && selecteduser.room_type == 'private'">All the Chat history will clear out in case of delete</p>
                        <br>
                        <div class="clearfix"></div>
                        <ul class="list-group" v-if="selecteduser && selecteduser.room_type == 'group'">
                            <li class="list-group-item" v-for="(member,index) in groupmembers" :key="index">
                                <table style="width:100%;">
                                    <tr>
                                        <td style="width:20%">
                                            <span class="sender-avatar-icon">{{ member.user_name.toUpperCase().charAt(0) }}</span>
                                            <!-- <img class="img" width="50px" :src="profile_pre + member.user_name.toUpperCase().charAt(0) +'.png'"> -->
                                            </td>
                                        <td style="width:60%;"><p style="line-height:3.5;margin-bottom:0px;" >{{member.user_name}}</p></td>
                                        <td style="width:20%;text-align:right;" v-if="group_permission===1"><i style="line-height:4;" 
                                            class="fa fa-trash" 
                                            @click="removeMembersfromGroup(member.user_id)"></i>
                                        </td>
                                    </tr>
                                </table>
                                
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="deleteGroupConfirmation" tabindex="-1" role="dialog" aria-labelledby="deleteGroupConfirmationTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteGroupConfirmationTitle">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are You Sure?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" @click="deletegroup()">Delete</button>
                </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="forwardMessageModal" tabindex="-1" role="dialog" aria-labelledby="forwardModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forwardModalLabel">Forward Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <li class="list-group-item" v-for="(member,index) in allusers" :key="index">
                            <table style="width:100%;">
                                <tr>
                                    <td style="width:20%">
                                        <span class="sender-avatar-icon">{{ member.roomname.toUpperCase().charAt(0) }}</span>
                                        <!-- <img class="img" width="50px" :src="profile_pre + member.user_name.toUpperCase().charAt(0) +'.png'"> -->
                                        </td>
                                    <td style="width:60%;"><p style="line-height:3.5;margin-bottom:0px;" >{{member.roomname}}</p></td>
                                    <td style="width:20%;text-align:right;"><i style="line-height:4;" 
                                        class="fa fa-share" 
                                        @click="forwardMessage(member.room_id)"></i>
                                    </td>
                                </tr>
                            </table>
                            
                        </li>
                    </ul>
                </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ComposeMessage from './ComposeMessageComponent';
    import multiselect from 'vue-multiselect';
    export default {
       props: {
            selectedContact:{
                type: Object,
                default: null
            },
            user: {
                type: Object,
                required: true
            },
            messages: {
                type: Array,
                default: []
            },
            group_permission: {
                type: Number,
                default: 0
            },
            allusers: {
                type: Array,
                default: []
            },
            loading_more:{
                type:Boolean,
                default:false
            },
            loading_chat:{
                type:Boolean,
                default:false
            },
            hasChatHistory:{
                type:Boolean,
                default:false
            },
        },
    data() {
            return {
                selectedMembers:[],
                selecteduser:null,
                editmessage:'',
                edit_text_prefix:'edit_message_',
                edit_button_prefix:'edit_button_',
                delete_button_prefix:'delete_button_',
                messagetextprefix:'message_text_',
                profile_pre:'/custom/alphabets/',
                groupmembers:[],
                addnewtogrouplist:[],
                editflag:false,
                selectedUserIds:'',
                editing_index:null,
                editing_message:null,
                hidegroupmodel:false,
                qoutemessagebody:'',
                quoting:false,
                quotemessagename:'',
                senderavatar:'',
                forwardingMessage:'',
                // loading:true,
                temphasChatHistory:false
            };
        },
        methods:{
            checkdate(message){
                if(message.group_at == this.lastmessage.group_at){
                    return false;
                }else{
                    this.lastmessage = message
                    return true;
                }
            },
            scrollevent(){
                var pos = $('#message_container').scrollTop();
                if (pos == 0) {
                    // console.log('from conver',this.selectedContact.room_id)
                    this.$emit('paginate_data', this.selectedContact);
                }
            },
            forwardMessageModal(message,index){
                $('#forwardMessageModal').modal('show');
                this.forwardingMessage = message.message;
            },
            forwardMessage(room_id){
                axios.post('sendMessage', {
                    room_id: room_id,
                    message: this.forwardingMessage,
                }).then((response) => {
                    this.$emit('newMessage', response.data);
                    $('#forwardMessageModal').modal('hide');
                });
            },
            sendFirstHi(){
                axios.post('sendMessage', {
                    room_id: this.selectedContact.room_id,
                    message: 'Hi',
                }).then((response) => {
                    this.$emit('newMessage', response.data);
                    this.temphasChatHistory = true
                });
            },
            cancelquotemessage(){
                this.qoutemessagebody = '';
                this.quoting = false;
            },
            quoteMessage(message,index){
                this.quoting = true;
                this.senderavatar = message.sender_name.toUpperCase().charAt(0);
                this.qoutemessagebody = message.message;
                this.quotemessagename = message.sender_name;
                // console.log($('.quotemesg').html())
            },
            checkURL(url) {
                return(url.match(/\.(jpeg|jpg|gif|png)$/) != null);
            },
            sendMessage(message){
                if(this.editflag){
                    if(this.editmessage==this.$refs.messagecomposercomponent.message){
                        return;
                    }
                    axios.post('editMessage', {
                        newmessage: this.$refs.messagecomposercomponent.message,
                        message_id: this.editing_message.message_id,
                        index :this.editing_index
                    }).then((response) => {
                        this.editmessage='';
                        this.$refs.messagecomposercomponent.message = '';
                        this.editflag = false;
                        this.editing_index = null;
                        this.editing_message = null;
                         this.$emit('newMessage', response.data);
                    });
                    return;
                }
                if(this.quoting){
                    this.qoutemessagebody = ''; 
                }
                axios.post('sendMessage', {
                    room_id: this.selectedContact.room_id,
                    message: message,
                    quoting:this.quoting,
                    qoutemessagebody:$('.quotemesg').html()
                }).then((response) => {
                    this.$emit('newMessage', response.data);
                });
            },
            openEditMessageEditor(message,index){
                // console.log(message)
                // $('#edit_message_'+message.message_id).show();
                var editmessage = '';
                editmessage = message.message.replace(/<\/?[^>]+(>|$)/g, "")
                editmessage = editmessage.replace("( Edited )", "");
                this.editmessage = editmessage;
                this.$refs.messagecomposercomponent.message = this.editmessage;
                this.editflag = true;
                this.editing_index = index;
                this.editing_message = message;
            },
            hideGroupmodel(){
               $('#groupMembersModal').modal('hide')
            },
            delteMessage(index,message_id){
                axios.post('deleteMessage', {
                    message_id: message_id,
                    index :index
                }).then((response) => {
                    // this.messages.splice(index, 1);
                    // this.$emit('newMessage', response.data);
                });
            },
            addMemberstoGroup(){
                if(this.selectedMembers.length>0){
                    for (var i = 0; i < this.selectedMembers.length; i++) {
                        this.selectedUserIds = this.selectedUserIds.concat(this.selectedMembers[i].id);
                        this.selectedUserIds = this.selectedUserIds.concat(',');
                    }
                    axios.post('addUserToGroup', {
                        chatroom_id: this.selecteduser.room_id,
                        users :this.selectedUserIds
                    }).then((response) => {
                        axios.get('getGroupMembers/' + this.selecteduser.room_id)
                        .then((response) => {
                            this.groupmembers = response.data;
                        });
                        axios.get('getallusernotingroup/' + this.selecteduser.room_id)
                        .then((response) => {
                            this.addnewtogrouplist = response.data;
                        });
                        this.selectedMembers = [];
                        this.selectedUserIds = '';
                    });
                }
            },
            removeMembersfromGroup(user_id){
               
                axios.post('removeUserfromGroup', {
                    chatroom_id: this.selecteduser.room_id,
                    user_id :user_id
                }).then((response) => {
                    axios.get('getGroupMembers/' + this.selecteduser.room_id)
                    .then((response) => {
                        this.groupmembers = response.data;
                    });
                    axios.get('getallusernotingroup/' + this.selecteduser.room_id)
                    .then((response) => {
                        this.addnewtogrouplist = response.data;
                    });
                });
            },
            deletegroup(){
                this.$emit('groupdeleted', this.selecteduser);
                axios.post('deletegroup', {
                    chatroom: this.selecteduser,
                }).then((response) => {

                });
            }
        },
        watch: {
            selectedContact (room) {
                this.selecteduser = room;
                if(room.room_type !='private'){
                    axios.get('getGroupMembers/' + room.room_id)
                    .then((response) => {
                        this.groupmembers = response.data;
                    });
                    axios.get('getallusernotingroup/' + room.room_id)
                    .then((response) => {
                        this.addnewtogrouplist = response.data;
                    });
                }
                    
            },
            messages(messages){
                this.messages = messages;
                this.lastmessage = messages[0];
                this.loading = false;
            }
        },
        mounted(){
            // console.log(this.loading_chat)
        },
        components: {ComposeMessage,multiselect}
        
    }
</script>

<style>
.date_line{
    position: absolute;
    left: 50%;
}
.date_line1{
    position: relative;
    left: 100%;
    top: -20px;
}
.btn:focus, .btn.focus {
    outline: 0;
    box-shadow: unset !important;
}
.fa-ellipsis-v{
    color:#8e8585;
}
/* .actionbuttons{
    display: none;
}
.sent_message:hover > .actionbuttons{
    display:block;
} */
.edit_message_input{
    border-radius: 0px;
}
.typing_indicator{
    font-size: 12px;
    color: #2c3e50;
}
.groupsendername {
    position: relative;
    text-transform: capitalize;
    /* left: 5%; */
}
.text-muted{
    font-style: italic;
}
.sent_message{
    position: relative;
}
.replies{
    position: relative;
}

.sendmessagetime{
    /* float: right; */
    position: absolute;
    top: 10px;
    right: 37px;
}
.actionbuttonssend {
    top: 3px;
    right: 108px;
    position: absolute;
}
.actionsbuttonreply{
    position: absolute;
    left: 60%;
    top: -3px;
}
.actionbuttonssendgroup {
    top: 3px;
    right: 108px;
    position: absolute;
}
.actionsbuttonreplygroup {
    position: absolute;
    left: 106px;
    top: -6px;
}
.recievemessagetime{
    /* float: right; */
    position: relative;
    top: 0;
    left: 10px;

}

.messages .text-muted{
    font-size: 10px;
}
#frame .content .messages{
    /* max-height: unset !important; */
    word-spacing: 2px;
}
.replies .text-muted{
    color: #abafb3 !important
}
.list-group-item{
    border: unset;
    padding: unset;
}
.deletegroupbutton{
    position: absolute;
    right: 10%;
    top: 4%;
}
.file_thumbnail{
    width: 30% !important;
    /* border-radius: unset !important; */
}
.file_thumbnail1{
    width: 60% !important;
    border-radius: 5px !important;
}

.sender-avatar-icon{
    height: 30px;
    text-align: center;
    width: 30px;
    color: #000;
    background-color: #e5e4e8;
    float: left;
    border-radius: 50%;
    display: inline-block;
    line-height: 2.2;
    margin: 10px;
}
.actionbuttonsender{
    top: 30px;
}
.quotemesg{
        left: 2%;
    width: 95%;
    position: absolute;
    bottom: 17%;
    background: whitesmoke;
    border-radius: 10px;
    padding: 10px;
}
.quotemesgclose{
    position: absolute;
    bottom: 20%;
    right: 5%;
}
.container_send_message{
    word-break: unset;
    white-space: unset;
}
#qouteMsgTable tr > td {
    margin: 0;
    padding: 0;
}
.file_container_send{
    width: 50%;
    float: right;
    background: rgb(241, 241, 244) !important;
    padding: 25px;
    word-break: unset;
    white-space: unset;
}
.file_container_recieve{
    width: 70%;
    float: left;
    padding: 25px;
    word-break: unset;
    white-space: unset;
}
@media screen and (max-width: 735px) {
    .typing_indicator{
        font-size: 8px;
        color: #2c3e50;
    }
    .contact-profile p{
        font-size: 12px;
    }  
    

}

.subtitle {
  margin: 0 0 2em 0;
  color: grey;
}
.fancy {
  line-height: 0.5;
  text-align: center;
}
.fancy span {
  display: inline-block;
  position: relative;  
}
.fancy span:before,
.fancy span:after {
  content: "";
  position: absolute;
  height: 5px;
  border-bottom: 1px solid grey;;
  top: 0;
  width: 600%;
}
.fancy span:before {
  right: 100%;
  margin-right: 15px;
}
.fancy span:after {
  left: 100%;
  margin-left: 15px;
}
</style>
