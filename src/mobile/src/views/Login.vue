<template>
  <div id="login">
    <h1>Login</h1>
    <input type="text" name="username" v-model="username" placeholder="Username">
    <input type="password" name="password" v-model="password" placeholder="Password">
    {{ error }}
    <button type="button" v-on:click="login()">Login</button>
  </div>
</template>

<script>
import axios from "axios";
import communication from "@/libs/communication.js";

export default {
  name: "login",
  data: function() {
    return {
      username: "",
      password: "",
      error: ""
    };
  },
  methods: {
    login() {
      if (this.username !== "" && this.password !== "") {
        communication.connect(this.username, this.password, response => {
          if (response === false) {
            let error = communication.getLastError();
            if (error.status === 401) {
              this.error = error.data;
            } else {
              this.error = error.data;
            }
          }
        });
      }
    }
  }
};
</script>
