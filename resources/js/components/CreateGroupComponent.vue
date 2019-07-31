<template>
<div>
    <div class="alert alert-danger" v-show="group_member_alert">
        <span @click="closealertbox" class="close">&times;</span>
        <strong>Alert!</strong> Please Select atleast 3 Members.
    </div>
     <form id="creategroupform">
            <!-- <div class="preview text-center">
                <img class="preview-img" src="http://simpleicon.com/wp-content/uploads/account.png" alt="Preview Image" width="50" height="50"/>
                <div class="browse-button">
                    <i class="fa fa-pencil-alt"></i>
                    <input class="browse-input" 
                            type="file" 
                            required 
                            name="UploadedFile"
                            id="files" ref="files" 
                            v-on:change="handleFileUploads($event.target.files)" 
                            accept="image/*"/>
                </div>
            </div> -->
            <div class="form-group">
                <input class="form-control" type="text" v-model="groupname" required placeholder="Group Name"/>
            </div>
            <multiselect v-model="selectedMembers"  
                    track-by="name" label="name" 
                    placeholder="Select one" 
                    :options="allcontacts" 
                    :searchable="true" 
                    :multiple="true">
                    <template slot="singleLabel" slot-scope="{ option }">
                        <strong>{{ option.name }}</strong>
                    </template>
            </multiselect>
        </form>
        <button class="btn btn-primary float-right" data-dismiss="modal" style="border-radius: unset;" @click="createGroup()">Create</button>
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
               selectedMembers: [],
               selectedUser :'',
               groupname:'',
               formData: new FormData(),
               selectedUserIds:'',
               group_member_alert:false
            };
        },
        methods:{
            closealertbox(){
                this.group_member_alert = false;
            },
            createGroup(){

                if(this.selectedMembers.length>=2){
                    for (var i = 0; i < this.selectedMembers.length; i++) {
                        this.selectedUserIds = this.selectedUserIds.concat(this.selectedMembers[i].id)
                        this.selectedUserIds = this.selectedUserIds.concat(',')
                    }
                    this.group_member_alert = false;
                }else{
                    this.selectedUserIds = '';
                    this.group_member_alert = true; 

                    return;
                }
                this.formData.append('users',this.selectedUserIds);
                this.formData.append('groupname',this.groupname);
                axios.post( 'create-chat-groups',
                this.formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
                ).then(function(){
                    this.selectedMembers = null;
                    this.groupname = '';
                    this.selectedUserIds = '';
                })
        	    .catch(function(){});  
                // console.log(this.selectedMembers);
            },
            handleFileUploads(fileList){
                // this.formData.append("logo", fileList[0], fileList[0].name);
            }
        },
        components: {multiselect}
}
</script>

<style scoped>
.preview
{
    padding: 10px;
    position: relative;
}

.preview i
{
    color: white;
    font-size: 35px;
    transform: translate(50px,130px);
}

.preview-img
{
    border-radius: 100%;
    box-shadow: 0px 0px 5px 2px rgba(0,0,0,0.7);
}

.browse-button
{
    width: 75px;
    height: 75px;
    border-radius: 100%;
    position: absolute; /* Tweak the position property if the element seems to be unfit */
    top: 0px;
    left: 195px;
    background: linear-gradient(180deg, transparent, black);
    opacity: 0;
    transition: 0.3s ease;
}
.browse-button:hover
{
    opacity: 1;
}
.browse-input
{
    width: 75px;
    height: 75px;
    border-radius: 100%;
    transform: translate(-1px,-26px);
    opacity: 0;
}
.form-group input
{
    transition: 0.3s linear;
}

.Error
{
    color: crimson;
    font-size: 13px;
}
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>