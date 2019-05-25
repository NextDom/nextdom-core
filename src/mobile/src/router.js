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
import Router from "vue-router";
import Home from "./views/Home.vue";
import Rooms from "./views/Rooms.vue";
import Login from "./views/Login.vue";
import Settings from "./views/Settings.vue";
import Scenarios from "./views/Scenarios.vue";

Vue.use(Router);

export default new Router({
  routes: [
    {
      path: "/login",
      name: "login",
      component: Login
    },
    {
      path: "/:roomId(\\d+)?",
      name: "home",
      props: true,
      component: Home
    },
    {
      path: "/rooms/:roomId?",
      name: "rooms",
      props: true,
      component: Rooms
    },
    {
      path: "/settings",
      name: "settings",
      component: Settings
    },
    {
      path: "/scenarios",
      name: "scenarios",
      component: Scenarios
    }
  ]
});
