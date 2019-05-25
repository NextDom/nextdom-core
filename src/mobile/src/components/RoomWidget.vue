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
import Utils from "../libs/Utils.js";
import theme from "muse-ui/lib/theme";

/**
 * Show the room widget
 * @group Components
 */
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
    // Room data
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
    /**
     * @vuese
     * Extract room icon class from HTML tag
     */
    roomIcon: function() {
      return Utils.extractIcon(this.room.icon, "fas fa-times");
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
</style>
