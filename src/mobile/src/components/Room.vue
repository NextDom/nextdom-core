<template>
  <div class="room">
    <template v-if="room.children">
      <router-link v-if="room" v-bind:to="{ name: 'rooms', params: { roomId: room.id }}">
        <div class="title">
          <span v-html="room.icon"></span>
          <h2>{{ room.name }}</h2>
        </div>
      </router-link>
    </template>
    <template v-else>
      <div class="title">
        <span v-html="room.icon"></span>
        <h2>{{ room.name }}</h2>
      </div>
    </template>
    <i
      class="settings fas"
      v-bind:class="[isVisible ? 'fa-eye' : 'fa-eye-slash']"
      v-on:click="toggleVisibility"
    ></i>
  </div>
</template>

<script>
export default {
  name: "Room",
  data: function() {
    return {
      isVisible: true
    };
  },
  props: {
    room: null
  },
  mounted() {
    let isVisibleStoredValue = localStorage.getItem(
      "is-visible-room-" + this.room.id
    );
    if (isVisibleStoredValue !== null) {
      this.isVisible = isVisibleStoredValue === "true" ? true : false;
    }
  },
  methods: {
    toggleVisibility() {
      this.isVisible = !this.isVisible;
      localStorage.setItem(
        "is-visible-room-" + this.room.id,
        this.isVisible ? "true" : "false"
      );
    }
  }
};
</script>

<style lang="scss">
@import "@/assets/styles/color.scss";

.room {
  width: 46%;
  background-color: $roomColor;
  margin: 2%;
  box-shadow: 0px 1px 5px $shadowColor;
  border-radius: 8px;
  position: relative;
}

.room .title span {
  display: block;
  text-align: center;
  margin: 1rem;
}

.room .title span i {
  font-size: 4.5rem;
  color: $primaryColor;
}

.room h2 {
  color: $primaryColor;
  text-align: center;
  margin: 0;
}

.room a {
  text-decoration: none;
}

.room .settings {
  position: absolute;
  top: 0;
  right: 0;
  margin: 0.5rem;
  font-size: 1.5rem;
  color: $secondaryColor;
}
</style>