import '@fortawesome/fontawesome-free/css/all.css'
import '@/assets/global.css'
import Vue from 'vue'
import App from '@/App.vue'
import vuetify from '@/plugins/vuetify'
import { store } from '@/libs/Store'
import EventsManager from "@/libs/EventsManager.js";
import Communication from "./libs/Communication.js";

Vue.config.productionTip = false
Vue.prototype.$eventBus = new Vue();
EventsManager.init(Communication, store);

new Vue({
  vuetify,
  store,
  iconfont: 'fa',
  render: h => h(App)
}).$mount('#app')
