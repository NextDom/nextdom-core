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
  <div id="app">
    <router-view
      v-bind:key="$route.fullPath"
      v-on:setCurrentView="setCurrentView"
      v-on:changeView="changeView"
    />
    <mu-bottom-nav class="bottom-nav" v-bind:value.sync="currentView" shift @change="changeView">
      <mu-bottom-nav-item value="/" v-bind:title="$t('summaryTitle')" icon="home" />
      <mu-bottom-nav-item value="/rooms" v-bind:title="$t('roomsTitle')" icon="meeting_room" />
      <mu-bottom-nav-item
        value="/scenarios"
        v-bind:title="$t('scenariosTitle')"
        icon="movie_creation"
      />
      <mu-bottom-nav-item
        value="/login"
        v-bind:title="$t('loginTitle')"
        icon="lock"
        v-if="!isConnected"
      />
      <mu-bottom-nav-item
        value="/settings"
        v-bind:title="$t('settingsTitle')"
        icon="build"
        v-if="isConnected"
      />
    </mu-bottom-nav>
    <mu-snackbar id="test" color="error" v-bind:open.sync="showedError">
      <mu-icon left value="warning"></mu-icon>
      {{ errorMsg }}
      <mu-button flat slot="action" color="#fff" v-on:click="showedError = false">{{ $t('close') }}</mu-button>
    </mu-snackbar>
  </div>
</template>

<script>
import Communication from "./libs/Communication.js";
import AppEventsBus from "@/libs/AppEventsBus";
import { setTimeout } from "timers";

/**
 * @vuese
 * Global component
 */
export default {
  data() {
    return {
      currentView: "login",
      showedError: false,
      errorMsg: ""
    };
  },
  created() {
    this.initLocale();
    AppEventsBus.$on("showError", msg => {
      this.showErrorMsg(msg);
    });
  },
  methods: {
    /**
     * Init locale
     */
    initLocale() {
      var lang = navigator.language || navigator.userLanguage;
      if (lang.length > 2) {
        lang = lang.substring(0, 2);
      }
      this.$i18n.locale = lang;
    },
    /**
     * Go to another view
     */
    changeView(newView) {
      this.setCurrentView;
      this.$router.push(newView);
    },
    /**
     * Set the current view
     */
    setCurrentView(newView) {
      this.currentView = newView;
    },
    /**
     * Get connection state
     */
    isConnected: function() {
      return Communication.isConnected();
    },
    /**
     * Show an error message during 3 seconds
     */
    showErrorMsg: function(msg) {
      console.error(msg);
      this.showedError = true;
      this.errorMsg = msg;
      let self = this;
      setTimeout(() => {
        self.showedError = false;
      }, 3000);
    }
  }
};
</script>

<style scoped>
.bottom-nav {
  width: 100%;
  position: fixed;
  bottom: 0;
}

.global {
  margin-bottom: 56px;
}
</style>
