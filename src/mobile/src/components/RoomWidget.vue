<template>
  <mu-grid-tile class="room-widget">
    <span slot="title">{{ room.name }}</span>
    <router-link v-bind:to="{ name: 'rooms', params: { roomId: room.id }}">
      <div class="icon" v-bind:class="{inactive: !isVisible}">
        <i v-bind:class="roomIcon"></i>
      </div>
    </router-link>
  </mu-grid-tile>
</template>

<script>
export default {
  name: "RoomWidget",
  data: function() {
    return {
      menuShowed: false,
      menuTrigger: null,
      isVisible: true
    };
  },
  props: {
    room: null
  },
  mounted() {
    // Init visibility
    let isVisibleStoredValue = localStorage.getItem(
      "is-visible-room-" + this.room.id
    );
    if (isVisibleStoredValue !== null) {
      this.isVisible = isVisibleStoredValue === "true" ? true : false;
    }
  },
  computed: {
    roomIcon: function() {
      let iconToShow = "fas fa-times";
      if (this.room.icon !== "") {
        const iconRegexResult = this.room.icon.match(/.*class="(.*?)"/i);
        if (iconRegexResult.length > 1) {
          iconToShow = iconRegexResult[1];
        }
      }
      return iconToShow;
    }
  }
};
</script>

<style scoped>
.icon > i {
  width: 100%;
  display: block;
  text-align: center;
  font-size: 5rem;
  padding: 1rem 0.5rem 0 0.5rem;
}
a {
  color: black;
}
.icon.inactive i {
  color: #888;
}
</style>
