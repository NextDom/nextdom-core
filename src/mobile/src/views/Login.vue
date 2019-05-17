<template>
  <mu-container id="global login">
    <h1>Login</h1>
    <mu-form v-bind:model="form">
      <mu-form-item label="Identifiant">
        <mu-text-field v-model="form.username"></mu-text-field>
      </mu-form-item>
      <mu-form-item label="Mot de passe">
        <mu-text-field type="password" v-model="form.password"></mu-text-field>
      </mu-form-item>
      <mu-alert color="error" v-if="error" transition="mu-scale-transition">
        <mu-icon left value="warning"></mu-icon>
        {{ error.status }} : {{ error.error }}
      </mu-alert>
      <mu-button color="primary" v-on:click="login()">Connexion</mu-button>
    </mu-form>
  </mu-container>
</template>

<script>
import communication from "@/libs/communication.js";
import eventsManager from "@/libs/eventsManager.js";

export default {
  name: "login",
  data: function() {
    return {
      form: {
        username: "",
        password: ""
      },
      error: null
    };
  },
  mounted() {
    this.$emit("changeView", "/login");
  },
  methods: {
    /**
     * Try user inputs and submit login
     */
    login() {
      if (this.form.username !== "" && this.form.password !== "") {
        communication.connect(
          this.form.username,
          this.form.password,
          response => {
            if (response === false) {
              this.error = communication.getLastError();
            } else {
              eventsManager.loop();
              this.$emit("changeView", "/");
              this.error = null;
            }
          }
        );
      }
    }
  }
};
</script>

<style scoped>
button {
  width: 100%;
}
.mu-alert {
  margin-bottom: 1rem;
}

.mu-scale-transition-enter-active,
.mu-scale-transition-leave-active {
  transition: transform 0.45s cubic-bezier(0.23, 1, 0.32, 1),
    opacity 0.45s cubic-bezier(0.23, 1, 0.32, 1);
  backface-visibility: hidden;
}

.mu-scale-transition-enter,
.mu-scale-transition-leave-active {
  transform: scale(0);
  opacity: 0;
}
</style>