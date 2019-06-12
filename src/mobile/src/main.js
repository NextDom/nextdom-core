/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */
import Vue from "vue";
import App from "./App.vue";
import router from "./router";
import MuseUI from "muse-ui";
import VuePackeryPlugin from "vue-packery-plugin";
import "material-icons";
import "./assets/theme-color";
import "./assets/theme.css";

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

import Communication from "./libs/Communication.js";
import { store } from "./libs/Store.js";
import EventsManager from "./libs/EventsManager.js";

Vue.config.productionTip = false;

/**
 * Route to login if not connected
 */
router.beforeEach((to, from, next) => {
  if (Communication.isConnected()) {
    next();
  } else if (to.name === "login") {
    next();
  } else {
    next("/login");
  }
});

// Init Communication helper for ajax calls
Communication.init(router);
// Init events manager (ask for new events)
EventsManager.init(Communication, store);
// Init MuseUI framekwork
Vue.use(MuseUI);
// Init packery for home view
Vue.use(VuePackeryPlugin);

new Vue({
  router,
  store,
  MuseUI,
  render: h => h(App)
}).$mount("#app");
