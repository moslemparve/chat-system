<template>
  <div id="sidepanel">
    <div id="profile">
      <div class="wrap">
        <!-- <img
          id="profile-img"
          :src="profile_pre + user.name.toUpperCase().charAt(0) +'.png'"
          class="online"> -->
        <span class="online sender-avatar-icon profile_avatar_s">{{ user.name.toUpperCase().charAt(0) }}</span>
        <p><b>{{ user.name }}</b></p>
        
        <a href="#" class="float-right mt-3" @click.prevent="logout()" title="Logout">
          <i class="fa fa-sign-out fa-lg"></i>
        </a>
      </div>
    </div>
    <div id="bottom-bar">
      <span class="badge badge-light onlineuserscount" style="margin-right:10%"></span>
      <button id="addcontact" data-toggle="modal" data-target="#addusermodal">
        <i class="fa fa-user-plus fa-fw" aria-hidden="true"></i>
        <span>Add</span>
      </button>
      <button v-if="group_permission==1"
              id="creategroup"
              data-toggle="modal"
              data-target="#createnewgroup">
        <i class="fa fa-users fa-fw" aria-hidden="true"></i>
        <span>Group</span>
      </button>
    </div>
    <br>
    <div class="form-group has-search">
      <input type="text" class="form-control" v-model="search" placeholder="Search Chats...">
    </div>

    <div class="recent_chats">
      <span>Recent Chats</span>
      <hr class="mb-0">
    </div>
    <div id="contacts">
      <div v-if="allusers.length > 0">
        <ul class="contactlists">
          <li v-for="(contact,index) in orderedUsersLists"
              :key="index"
              class="contact"
              v-bind:class="{ 'active' :room_id == contact.room_id}"
              @click="selectContact(contact)">
              <div class="wrap">
                <span class="contact-status" v-bind:class="[{ 'busy' :contact.room_type == 'private'}, 'contact_no_'+contact.user_id]"></span>
                <span class="sender-avatar-icon float-right">{{ contact.roomname.toUpperCase().charAt(0) }}</span>
                <div class="meta">
                  <p class="name">{{ contact.roomname }}</p>
                  <span class="last_messge_time">{{ contact.displaylastmessagetime }}</span>
                </div>
              </div>
            <span class="last_message_info">{{ removetagsfromcontactlist(contact.lastmessage.slice(0,30)) }}</span>
            <span v-show="contact.unreadcount>0" class="badge badge-light" v-bind:class="'preunread_'+contact.room_id">{{ contact.unreadcount }}</span>
          </li>
        </ul>
      </div>
      <div v-else>
        <div class="meta">
          <p class="name">No Contact Available</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    user: {
      type: Object,
      required: true
    },
    allusers: {
      type: Array,
      default: []
    },
    group_permission: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      contacts: [],
      room_id: "",
      selected: "",
      usercontactlist: [],
      search: "",
      profile_pre: "/custom/alphabets/",
    };
  },
  methods: {
    removetagsfromcontactlist(message){
      return message.replace(/<\/?[^>]+(>|$)/g, "");
    },
    selectContact(contact) {
      $('.preunread_'+contact.room_id).hide();
      this.room_id = contact.room_id;
      this.selected = contact;
      this.$emit("selected", contact);
      // ###r
      this.$emit("menuWidth", "0px");
      $('#message_composer').focus();
    },
    logout() {
      axios.post("logout", {}).then(response => {
        location.reload();
      });
    }
  },
  computed: {
    // filterChatlist: function() {
    //   return this.usercontactlist.filter(contact => {
    //     return contact.roomname.toLowerCase().match(this.search);
    //   });
    // },
    orderedUsersLists: function () {
      if(this.search!=''){
        return this.usercontactlist.filter(contact => {
          return contact.roomname.toLowerCase().match(this.search);
        });
      }else{
        return _.orderBy(this.usercontactlist, 'lastmessagetime','desc')
      }
    }
  },
  watch: {
    allusers(contacts) {
      contacts = _.orderBy(contacts, 'lastmessagetime','desc')
      this.usercontactlist = contacts;
      this.room_id = contacts[0].room_id;
    }
  }
};
</script>

<style scoped>
#frame #sidepanel #contacts ul li.contact .wrap .avatar {
  width: 45px !important;
  height: 45px !important;
  border-radius: 50%;
  float: left;
  margin-right: 10px;
  vertical-align: middle;
  border-style: none;
  display: unset !important;
}
.avatar span {
  margin-left: -5px;
}

.sender-avatar-icon{
    height: 30px;
    text-align: center;
    width: 30px;
    color: white;
    background-color:#71e5ec;
    float: left;
    border-radius: 50%;
    display: inline-block;
    line-height: 2.5;
    margin: 10px;
}
.profile_avatar_s{
 padding: 20px 10px;
    width: 38px;
    background: #e5e4e8;
    line-height: 0;
    color: #000;
    font-weight: 600;

}
.badge-light {
    position: absolute;
    color: #ffffff;
    right: 5%;
    top: 50%;
    background-color: #44a66d;
}
span.last_messge_time {
    right: 0;
    position: absolute;
    top: 0;
    color: grey !important;
    font-size: 8px;
    background: #ffffff00 !important;
}

.onlineuserscount{
  margin-top: 10%;
  margin-right: 10%;
  position: unset;
}
</style>
