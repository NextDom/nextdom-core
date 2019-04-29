<template>
  <div class="summary" v-if="roomsSummary && isVisible">
    <div class="title">
      <h2>
        <span v-html="roomsSummary.icon"></span>
        {{ roomsSummary.name }}
      </h2>
    </div>
    <div class="content">
      <div v-if="roomsSummary.eqLogics">
        <EqLogics v-bind:eqLogicsList="roomsSummary.eqLogics"></EqLogics>
      </div>
      <div v-if="roomsSummary.children">
        <div v-for="room in roomsSummary.children" v-bind:key="room.id">
          <Summary v-bind:roomsSummary="room"></Summary>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import EqLogics from "./EqLogics";

export default {
  name: "Summary",
  data: function() {
    return {
      isVisible: true
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
        this.isVisible = false;
      }
    }
    //    roomsSummary.id;
  },
  components: {
    EqLogics
  }
};
</script>

<style scoped lang="scss">
.room {
  width: 100%;
}

.title {
  position: relative;
}

h2 {
  display: inline-block;
}
</style>
