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
import communication from "@/libs/communication.js";

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
      communication.get(
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
     * Test if eqLogic must be showed
     * @param {eqLogic} int Id of the eqLogic to test
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
