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
      <mu-bottom-nav-item value="/scenario" title="Scénario" icon="movie_creation"/>
      <mu-bottom-nav-item value="/login" title="Connexion" icon="lock" v-if="isConnected"/>
      <mu-bottom-nav-item value="/settings" title="Paramètres" icon="build" v-if="!isConnected"/>
    </mu-bottom-nav>
  </div>
</template>

<script>
import communication from "./libs/communication.js";

export default {
  data() {
    return {
      currentView: "login"
    };
  },
  conputed: {},
  methods: {
    changeView(newView) {
      this.setCurrentView;
      this.$router.push(newView);
    },
    setCurrentView(newView) {
      this.currentView = newView;
    },
    isConnected: function() {
      return communication.isConnected();
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
</style>
