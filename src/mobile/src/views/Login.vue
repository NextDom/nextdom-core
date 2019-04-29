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
        axios.defaults.headers.common["X-AUTH-TOKEN"] = null;
        axios
          .get(
            "/api/connect?login=" + this.username + "&password=" + this.password
          )
          .then(response => {
            localStorage.setItem("token", response.data.token);
          })
          .catch(error => {
            if (error.response.status === 401) {
              this.error = error.response.data;
            } else {
              this.error = error.response.data;
            }
          });
      }
    }
  }
};
</script>
