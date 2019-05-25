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
  <mu-container class="global home">
    <h1>Résumé</h1>
    <Dashboard v-bind:roomData="roomData"></Dashboard>
  </mu-container>
</template>

<script>
import Communication from "@/libs/Communication.js";
import Dashboard from "@/components/Dashboard.vue";
import EventsManager from "@/libs/EventsManager.js";
import AppEventsBus from "@/libs/AppEventsBus.js";

/**
 * Home page
 * @group Pages
 */
export default {
  name: "Home",
  data: function() {
    return {
      roomData: null
    };
  },
  props: {
    // Id of the root room
    roomId: {
      type: String,
      default: undefined
    }
  },
  components: {
    Dashboard
  },
  mounted() {
    /**
     * @vuese
     * Update tabs and URL
     * @arg New URL
     */
    this.$emit("setCurrentView", "/");
    // Default view
    if (this.roomId === undefined) {
      Communication.get("/api/summary/get_default_room_tree", this.initData);
    } else {
      // Room specific view
      Communication.get(
        "/api/summary/get_room_tree/" + this.roomId,
        this.initData,
        error => {
          /**
           * @vuese
           * Show an error message
           * @arg Error informations
           */
          AppEventsBus.$emit(
            "showError",
            error.status + " " + error.statusText
          );
        }
      );
    }
  },
  methods: {
    /**
     * @vuejs
     * Entry point when data is loaded
     * @arg data Rooms tree from root room
     */
    initData(data) {
      this.roomData = data;
      // Start update loop
      EventsManager.loop();
    }
  }
};
</script>

