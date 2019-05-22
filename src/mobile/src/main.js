import Vue from "vue";
import App from "./App.vue";
import router from "./router";
import MuseUI from "muse-ui";
import VuePackeryPlugin from "vue-packery-plugin";
import "material-icons";
import "./assets/theme";

require("./assets/icons/animal/style.css");
require("./assets/icons/divers/style.css");
require("./assets/icons/fashion/style.css");
require("./assets/icons/loisir/style.css");
require("./assets/icons/maison/style.css");
require("./assets/icons/meteo/style.css");
require("./assets/icons/nature/style.css");
require("./assets/icons/nextdom/style.css");
require("./assets/icons/nextdom2/style.css");
require("./assets/icons/nextdomapp/style.css");
require("./assets/icons/nourriture/style.css");
require("./assets/icons/personne/style.css");
require("./assets/icons/securite/style.css");
require("./assets/icons/transport/style.css");
require("../node_modules/font-awesome/css/font-awesome.css");
require("../node_modules/font-awesome5/css/fontawesome-all.css");

import communication from "./libs/communication.js";
import { store } from "./libs/store.js";
import eventsManager from "./libs/eventsManager.js";

Vue.config.productionTip = false;

/**
 * Route to login if not connected
 */
router.beforeEach((to, from, next) => {
  if (communication.isConnected()) {
    next();
  } else if (to.name === "login") {
    next();
  } else {
    next("/login");
  }
});

// Init communication helper for ajax calls
communication.init(router);
// Init events manager (ask for new events)
eventsManager.init(communication, store);
// Init MuseUI framekwork
Vue.use(MuseUI);
Vue.use(VuePackeryPlugin);
new Vue({
  router,
  store,
  MuseUI,
  render: h => h(App)
}).$mount("#app");
