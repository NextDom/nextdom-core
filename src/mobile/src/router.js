import Vue from "vue";
import Router from "vue-router";
import Home from "./views/Home.vue";
import Rooms from "./views/Rooms.vue";
import Login from "./views/Login.vue";

Vue.use(Router);

export default new Router({
  routes: [
    {
      path: "/login",
      name: "login",
      component: Login
    },
    {
      path: "/",
      name: "home",
      component: Home
    },
    {
      path: "/rooms/:roomId?",
      name: "rooms",
      props: true,
      component: Rooms
    }
  ]
});
