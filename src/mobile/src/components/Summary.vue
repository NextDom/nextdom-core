<template>
  <div class="summary">
    <template v-if="roomsSummary && isVisible">
      <mu-grid-list v-bind:cols="nbColumns" v-bind:padding="5">
        <mu-sub-header>
          <span v-html="roomsSummary.icon"></span>
          {{ roomsSummary.name }}
        </mu-sub-header>
        <Widget
          v-for="eqLogic in roomsSummary.eqLogics"
          v-bind:key="eqLogic.id"
          v-bind:cmds="eqLogic.cmds"
          v-bind:eqlogic="eqLogic"
        ></Widget>
      </mu-grid-list>
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
    if (this.roomsSummary !== null) {
      let isVisibleStoredValue = localStorage.getItem(
        "is-visible-room-" + this.roomsSummary.id
      );
      if (isVisibleStoredValue !== null && isVisibleStoredValue === "false") {
        console.log(
          "OHH " + this.roomsSummary.name + " - " + this.roomsSummary.id
        );
        this.isVisible = false;
      }
    }
  },
  components: {
    Widget
  }
};
</script>

