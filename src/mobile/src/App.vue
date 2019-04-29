<template>
  <div id="app">
    <div id="nav">
      <router-link to="/">Home</router-link>|
      <router-link to="/rooms">Rooms</router-link>|
      <router-link to="/login">Login</router-link>|
    </div>
    <router-view v-bind:key="$route.fullPath"/>
  </div>
</template>

<style lang="scss">
@import "@/assets/styles/color.scss";

* {
  font-family: sans-serif;
}

html {
  background-color: $backgroundColor;
}

body {
  margin: 0;
}
</style>
