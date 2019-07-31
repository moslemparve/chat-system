<template>
  <div>
    <div class="message-input shadow-lg">
      <table class="table">
        <tr>
          <td class="tabletd1" style="cursor:pointer">
            <i :class="(!openfileuploadingbox) ? 'fa fa-paperclip fa-lg attachment' : 'fa fa-arrow-down fa-lg'"
              aria-hidden="true"
              @click="uploadfileboxopen"
            ></i>
          </td>
          <td class="tabletd2">
            <at-ta :members="members" name-key="user_name">
              <textarea
                id="message_composer"
                v-model="message"
                @keydown.enter.exact.prevent="sendMessage"
                @keydown="sendTypingEvent"
                placeholder="Type your message here..."
                v-bind:style="{height : message.length > 300 ? '200px' : '' }" autofocus
              ></textarea>
            </at-ta>
          </td>
          <td class="tabletd3" @click="sendMessage">
            <button class="submit">
              <i class="fa fa-paper-plane" aria-hidden="true"></i>
            </button>
          </td>
        </tr>
      </table>
      <emoji-picker @emoji="append" :search="search">
        <div
          class="emoji-invoker"
          slot="emoji-invoker"
          slot-scope="{ events: { click: clickEvent } }"
          @click.stop="clickEvent">
          <svg height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
          </svg>
        </div>
        <div slot="emoji-picker" slot-scope="{ emojis, insert }">
          <div class="emoji-picker" :style="{ position: 'absolute', bottom: '70px', right: '10%' }">
            <div class="emoji-picker__search">
              <input type="text" v-model="search" v-focus>
            </div>
            <div>
              <div v-for="(emojiGroup, category) in emojis" :key="category">
                <h5>{{ category }}</h5>
                <div class="emojis">
                  <span
                    v-for="(emoji, emojiName) in emojiGroup"
                    :key="emojiName"
                    @click="insert(emoji)"
                    :title="emojiName"
                  >{{ emoji }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </emoji-picker>

      <!-- <picker v-show="showemojibox" :style="{ position: 'absolute', bottom: '50px', left: '0' }" @select="addEmoji" set="facebook" /> -->
      <vue2Dropzone class="dropzoneUpPosition"
        v-show="openfileuploadingbox"
        ref="myVueDropzone"
        id="dropzone"
        :options="dropzoneOptions"
        v-on:vdropzone-sending="sendingEvent"
        @vdropzone-queue-complete="vqueueComplete"
      ></vue2Dropzone>
    </div>
  </div>
</template>
<script>
import AtTa from "vue-at/dist/vue-at-textarea";
import At from "vue-at";

import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";
// import { Picker } from 'emoji-mart-vue';
import EmojiPicker from 'vue-emoji-picker'
export default {
  components: { AtTa, At, vue2Dropzone,EmojiPicker},
  props: {
    selectedContact: {
      type: Object,
      default: null
    },
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      search: '',
      attachmentIcon: "fa fa-paperclip attachment fa-lg",
      openfileuploadingbox: false,
      message: "",
      members: [],
      typing: {
        user: null,
        chatroom_id: null
      },
      // showemojibox:false,
      queueComplete: false,
      dropzoneOptions: {
        url: "/uploadfile",
        thumbnailWidth: 150,
        maxFilesize: 50,
        addRemoveLinks: true,
        autoProcessQueue: false,
        headers: {
          "Access-Control-Allow-Origin": "*",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        parallelUploads: 1
      }
    };
  },
  directives: {
    focus: {
      inserted(el) {
        el.focus()
      },
    },
  },
  mounted(){
    window.addEventListener("dragenter",function(e){
      e = e || event;
      e.preventDefault();
      this.openfileuploadingbox = true;
      $('#dropzone').show();
    },false);
      
  },
  methods: {
    append(emoji) {
      this.message += emoji
    },
    addEmoji(emoji){
        this.message = this.message + ' ' + emoji.native;
    },
    sendMessage() {
      this.message = $.trim(this.message);
      if (this.message === "") {
        this.$refs.myVueDropzone.processQueue();
        return;
      }
      this.$emit("send", this.message);
      this.message = "";
      // var audio = new Audio('audio/sendmessage.mp3');
      // audio.play();
    },
    uploadfileboxopen() {
      this.openfileuploadingbox = !this.openfileuploadingbox;
    },
    // openemojobox() {
    //   this.showemojibox = !this.showemojibox;
    // },
    sendingEvent(file, xhr, formData) {
      formData.append("chatroom_id", this.selectedContact.room_id);
    },
    vqueueComplete(file, xhr, formData) {
      this.openfileuploadingbox = !this.openfileuploadingbox;
      this.queueComplete = true;
      setTimeout(function() {
        $(".dz-preview").remove();
        $(".dz-message").show();
      }, 1000);
    },

    sendTypingEvent() {
      this.typing.user = this.user;
      this.typing.chatroom_id = this.selectedContact.room_id;
      Echo.private(`newMessage.${this.selectedContact.room_id}`)
      .whisper(
        "typing",
        this.typing
      );
      Echo.join('chat')
          .whisper('typing', this.user);
    }
  },
  watch: {
    selectedContact(room) {
      this.members = [];
      if (room.room_type != "private") {
        axios.get("getGroupMembers/" + room.room_id).then(response => {
          this.members = response.data;
          let i = this.members.map(item => item.id).indexOf(this.user.id) // find index of your object
          this.members.splice(i, 1)
        });
      }
    }
  }
};
</script>

<style scoped>
table{
    margin-bottom: unset;
    /* background:#eaeaea; */
    background: #f5f5f5;
    width: 100%;
     margin: auto;
}
.table th, .table td {
    border: 1px solid #eeeeee;
    vertical-align: middle;
    text-align: center;
    /* padding: 0.2rem; */
}
.fa-arrow-down {
  z-index: 4;
  font-size: 1.1em;
  color: #73777c;
  opacity: 0.5;
  cursor: pointer;
}
textarea {
  font-family: "proxima-nova", "Source Sans Pro", sans-serif;
  float: left;
  border: none;
  width: 100%;
  padding: 14px 32px 10px 8px;
  font-size: 1em;
  color: #32465a;
  resize: none;
  white-space: pre-wrap;
  height: 50px;
  border-radius: 10px;
}
.fa-paperclip{
    color:#73777c;
}

.dropzoneUpPosition{
  position: absolute; 
  bottom: 66px; 
  width:100%; 
  /* left: 7%;  */
  /* margin: auto; */
}




.emoji-invoker {
  position: absolute;
  top: 39px;
  right: 130px;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s;
}
.emoji-invoker:hover {
  transform: scale(1.1);
}
.emoji-invoker > svg {
  fill: #b1c6d0;
}

.emoji-picker {
  position: relative;
  z-index: 1;
  font-family: Montserrat;
  border: 1px solid #ccc;
  width: 15rem;
  height: 20rem;
  overflow: scroll;
  padding: 1rem;
  box-sizing: border-box;
  border-radius: 0.5rem;
  background: #fff;
  box-shadow: 1px 1px 8px #c7dbe6;
}
.emoji-picker__search {
  display: flex;
}
.emoji-picker__search > input {
  flex: 1;
  border-radius: 10rem;
  border: 1px solid #ccc;
  padding: 0.5rem 1rem;
  outline: none;
}
.emoji-picker h5 {
  margin-bottom: 0;
  color: #b1b1b1;
  text-transform: uppercase;
  font-size: 0.5rem;
  cursor: default;
}
.emoji-picker .emojis {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}
.emoji-picker .emojis:after {
  content: "";
  flex: auto;
}
.emoji-picker .emojis span {
  padding: 0.2rem;
  cursor: pointer;
  border-radius: 5px;
}
.emoji-picker .emojis span:hover {
  background: #ececec;
  cursor: pointer;
}
.tabletd1 {
  width: 5%;
}
.tabletd3 {
    width: 6%;
}

.tabletd2 {
      width: 70%;
    padding: 27px;
}
@media screen and (max-width: 768px){
  table{
    width: 100%;
  }
  .emoji-invoker {
     right: 63px;
     top: 29px;
  }
  .tabletd2 {
    padding: 0px;
  }
  
}

</style>
