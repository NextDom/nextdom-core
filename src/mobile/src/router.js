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
