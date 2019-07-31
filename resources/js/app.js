
require('./bootstrap');
// import VModal from 'vue-js-modal'

window.Vue = require('vue');
import multiselect from 'vue-multiselect'
import VueChatScroll from 'vue-chat-scroll'
import vue2Dropzone from 'vue2-dropzone'
import At from 'vue-at'
import AtTa from 'vue-at/dist/vue-at-textarea'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import { Picker } from 'emoji-mart-vue'
Vue.use(Picker)
Vue.use(VueChatScroll)
Vue.use(vue2Dropzone)
Vue.use(multiselect)
Vue.use(At)
Vue.use(AtTa)

import linkify from 'vue-linkify'
 
Vue.directive('linkified', linkify)
// Vue.use(VModal)

Vue.component('chat-component', require('./components/ChatComponent.vue').default);

const app = new Vue({
	el: '#app'
});

