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
  <mu-container class="global login">
    <h1>{{ $t('loginTitle')}}</h1>
    <mu-form v-bind:model="form">
      <mu-form-item v-bind:label="$t('login')" icon="account_circle">
        <mu-text-field v-model="form.username"></mu-text-field>
      </mu-form-item>
      <mu-form-item v-bind:label="$t('password')" icon="locked">
        <mu-text-field type="password" v-model="form.password"></mu-text-field>
      </mu-form-item>
      <mu-alert color="error" v-if="error" transition="mu-scale-transition">
        <mu-icon left value="warning"></mu-icon>
        {{ error.status }} : {{ error.error }}
      </mu-alert>
      <mu-button color="primary" v-on:click="login()">
        <mu-icon left value="lock_open"></mu-icon>
        {{ $t('connect')}}
      </mu-button>
    </mu-form>
    <p>
      <mu-button color="secondary" v-on:click="forceDesktop()">
        <mu-icon left value="desktop_mac"></mu-icon>
        {{ $t('desktopVersion')}}
      </mu-button>
    </p>
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
    },
    /**
     * @vuese
     * Force user to desktop page
     */
    forceDesktop() {
      window.location = "/index.php?force_desktop=1";
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

.mu-text-field-input {
  padding-left: 0.5rem;
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

.mu-button i {
  margin-right: 0.5rem;
}
</style>
