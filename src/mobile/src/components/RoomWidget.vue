<template>
  <mu-grid-tile class="room-widget">
    <span slot="title">{{ room.name }}</span>
    <router-link v-bind:to="{ name: 'rooms', params: { roomId: room.id }}">
      <div class="icon">
        <i v-bind:class="[isVisible ? 'visible' : 'invisible', roomIcon]"></i>
      </div>
    </router-link>
  </mu-grid-tile>
</template>

<script>
import utils from "../libs/utils.js";
import theme from "muse-ui/lib/theme";

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
    console.log(theme);
  },
  computed: {
    roomIcon: function() {
      return utils.extractIcon(this.room.icon, "fas fa-times");
    }
  }
};
</script>
<style scoped lang="scss">
@import "../assets/theme.scss";

.icon > i {
  width: 100%;
  display: block;
  text-align: center;
  font-size: 5rem;
  padding: 1rem 0.5rem 0 0.5rem;
}
.visible {
  color: $primary;
}

.invisible {
  color: $secondary;
}
/*
a {
  color: $primary;
}
.icon.inactive i {
  color: $secondary;
}
*/
</style>
