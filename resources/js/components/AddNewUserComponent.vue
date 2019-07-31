<template>
    <div style="text-align: right;">     
        <multiselect v-model="selectedUser"  
                    track-by="name" label="name" 
                    placeholder="Select one" 
                    :options="allcontacts" 
                    :searchable="true">
                    <template slot="singleLabel" slot-scope="{ option }">
                        <strong>{{ option.name }}</strong>
                    </template>
            </multiselect>   
        <!-- <select class="form-control" id="selectednewuser">
          <option v-for="(contact,index) in allcontacts" v-bind:key="index" :value="contact.id" :id="'contact_list_'+contact.id">{{ contact.name }}</option>
        </select> -->
        <br>
        <button class="btn btn-primary text-right" style="border-radius: unset;" @click="addselectedUser()" data-dismiss="modal">Start Chat</button>
    </div>
</template>
<script>
import multiselect from 'vue-multiselect'
    export default {
        props: {
            allcontacts: {
                type: Array,
                default: []
            }
        },
        data(){
            return {
                contactList: null,
                filter:'',
                imgPreUrl:'img/staff/',
                defaultImg :'default_avatar_male.jpg',
                selectedUser :'',
            };
        },
        methods:{
            addselectedUser(){
                // this.selectedUser = $('#selectednewuser').val();
                // this.selectedUser = parseInt(this.selectedUser)
                // console.log(this.selectedUser.id);
                // return;              
                if(this.selectedUser==''){
                    return;
                }
                axios.post('newprivatechat', {
                    user_id: this.selectedUser.id,
                }).then((response)=>{
                  this.$emit('newUserAdded', response.data)
                  const index = this.allcontacts.indexOf(this.selectedUser);
                  this.allcontacts.splice(index, 1);
                  $('.multiselect__option--highlight').parent().remove();
                });
            },
            selectContact(contact){
                this.selectedUser = contact.user_id;        
            }
        },
        components: {multiselect}
        
    }
</script>
<style scoped>
</style>