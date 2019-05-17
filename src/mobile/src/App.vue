<template>
  <div id="app">
    <router-view
      v-bind:key="$route.fullPath"
      v-on:setCurrentView="setCurrentView"
      v-on:changeView="changeView"
    />
    <mu-bottom-nav class="bottom-nav" v-bind:value.sync="currentView" shift @change="changeView">
      <mu-bottom-nav-item value="/" title="Accueil" icon="home"/>
      <mu-bottom-nav-item value="/rooms" title="Pièces" icon="meeting_room"/>
      <mu-bottom-nav-item value="/scenarios" title="Scénario" icon="movie_creation"/>
      <mu-bottom-nav-item value="/login" title="Connexion" icon="lock" v-if="!isConnected"/>
      <mu-bottom-nav-item value="/settings" title="Paramètres" icon="build" v-if="isConnected"/>
    </mu-bottom-nav>
    <mu-snackbar id="test" color="error" v-bind:open.sync="showedError">
      <mu-icon left value="warning"></mu-icon>
      {{ errorMsg }}
      <mu-button flat slot="action" color="#fff" v-on:click="showedError = false">Fermer</mu-button>
    </mu-snackbar>
  </div>
</template>

<script>
import communication from "./libs/communication.js";
import EventsBus from "@/libs/eventsBus";
import { setTimeout } from "timers";

export default {
  data() {
    return {
      currentView: "login",
      showedError: false,
      errorMsg: ""
    };
  },
  created() {
    EventsBus.$on("showError", msg => {
      this.showErrorMsg(msg);
    });
  },
  methods: {
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
      return communication.isConnected();
    },
    /**
     * Show an error message during 3 seconds
     */
    showErrorMsg: function(msg) {
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
