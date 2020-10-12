import Vue from "vue";
import Router from "vue-router";
import Dash from "@/components/Dash"
import DashEditor from "@/components/DashEditor"

Vue.use(Router);

export default new Router({
    routes: [
        {
            path: "/:dashId(\\d+)?",
            name: "dash",
            component: Dash,
            props: true
        },
        {
            path: "/editor/:dashId(\\d+)",
            name: "dash-editor",
            component: DashEditor,
            props: true
        }
    ]
});
