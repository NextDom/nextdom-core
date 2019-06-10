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
  <div class="dashboard">
    <template v-if="roomData && isVisible">
      <!-- Room name -->
      <h2>
        <span v-html="roomData.icon"></span>
        {{ roomData.name }}
      </h2>
      <!-- eqLogics of the room -->
      <div v-packery="{itemSelector: '.packery-item', percentPosition: true, initLayout: true}">
        <template v-for="eqLogic in roomData.eqLogics">
          <Widget
            v-if="isShowedEqLogic(eqLogic.id)"
            v-bind:key="eqLogic.id"
            v-bind:cmds="eqLogic.cmds"
            v-bind:eqlogic="eqLogic"
          ></Widget>
        </template>
      </div>
      <!-- Room children -->
      <div v-if="roomData !== null && roomData.children">
        <div v-for="room in roomData.children" v-bind:key="room.id">
          <Dashboard v-bind:roomData="room"></Dashboard>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import Widget from "./Widget";
import Communication from "@/libs/Communication.js";

/**
 * Show the dashboard of a room
 * @group Components
 */
export default {
  name: "Dashboard",
  data: function() {
    return {
      isVisible: true,
      nbColumns: 2,
      summary: false
    };
  },
  props: {
    // Data of the room to show
    roomData: null
  },
  mounted() {
    // Check if room must be showed
    if (this.roomData !== null) {
      const localStorageKey = "is-visible-room-" + this.roomData.id;
      let isVisibleStoredValue = localStorage.getItem(localStorageKey);
      if (isVisibleStoredValue !== null && isVisibleStoredValue === "false") {
        this.isVisible = false;
      }
      Communication.get(
        "/api/room/get_summary/" + this.roomData.id,
        summary => {
          this.summary = summary;
        }
      );
    }
  },
  components: {
    Widget
  },
  methods: {
    /**
     * @vuese
     * Test if eqLogic must be showed
     * @arg Id of the eqLogic to test
     * @return True if eqLogic must be showed
     */
    isShowedEqLogic(eqLogicId) {
      const localStorageKey = "is-visible-eqLogic-" + eqLogicId;
      const isVisibleStoredValue = localStorage.getItem(localStorageKey);
      if (isVisibleStoredValue !== null && isVisibleStoredValue === "false") {
        return false;
      }
      return true;
    }
  }
};
</script>
