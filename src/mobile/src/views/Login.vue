<!--
This file is part of NextDom Software.

NextDom is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

NextDom Software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.

@Support <https://www.nextdom.org>
@Email   <admin@nextdom.org>
@Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
-->
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
import Communication from "@/libs/Communication.js";
import EventsManager from "@/libs/EventsManager.js";

/**
 * Login page
 * @group Pages
 */
export default {
  name: "Login",
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
    /**
     * @vuese
     * Update tabs and URL
     * @arg New URL
     */
    this.$emit("changeView", "/login");
  },
  methods: {
    /**
     * @vuese
     * Try user inputs and submit login
     */
    login() {
      if (this.form.username !== "" && this.form.password !== "") {
        Communication.connect(
          this.form.username,
          this.form.password,
          response => {
            if (response === false) {
              this.error = Communication.getLastError();
            } else {
              EventsManager.loop();
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