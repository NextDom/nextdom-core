<template>
  <div class="summary">
    <template v-if="roomsSummary && isVisible">
      <mu-grid-list v-bind:cols="nbColumns" v-bind:padding="5">
        <!-- Room name -->
        <mu-sub-header>
          <span v-html="roomsSummary.icon"></span>
          {{ roomsSummary.name }}
        </mu-sub-header>
        <!-- eqLogics of the room -->
        <template v-for="eqLogic in roomsSummary.eqLogics">
          <Widget
            v-if="isShowedEqLogic(eqLogic.id)"
            v-bind:key="eqLogic.id"
            v-bind:cmds="eqLogic.cmds"
            v-bind:eqlogic="eqLogic"
          ></Widget>
        </template>
      </mu-grid-list>
      <!-- Room children -->
      <div v-if="roomsSummary !== null && roomsSummary.children">
        <div v-for="room in roomsSummary.children" v-bind:key="room.id">
          <Summary v-bind:roomsSummary="room"></Summary>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import Widget from "./Widget";

export default {
  name: "Summary",
  data: function() {
    return {
      isVisible: true,
      nbColumns: 2
    };
  },
  props: {
    roomsSummary: null
  },
  mounted() {
    // Check if room must be showed
    if (this.roomsSummary !== null) {
      const localStorageKey = "is-visible-room-" + this.roomsSummary.id;
      let isVisibleStoredValue = localStorage.getItem(localStorageKey);
      if (isVisibleStoredValue !== null && isVisibleStoredValue === "false") {
        this.isVisible = false;
      }
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

