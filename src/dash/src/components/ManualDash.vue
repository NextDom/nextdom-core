<!--
Dash avec les placements fixes
-->
<template>
  <div class="full-size" v-bind:class="classMode" v-on:drop="widgetDropped" @dragover.prevent>
    <Widget v-for="widgetData in widgets" v-bind:key="widgetData.id" v-bind:widgetData="widgetData" absolute />
  </div>
</template>

<script>
import Widget from "./Widgets/Widget";

export default {
  name: "ManualDash",
  components: {
    Widget
  },
  mounted() {
    this.$store.commit("setDashType", "manual");
  },
  computed: {
    widgets() {
      return this.$store.getters.widgets;
    },
    classMode() {
      let classToAdd = "";
      if (this.$store.getters.editMode) {
        classToAdd = "edit-mode";
      } else if (this.$store.getters.deleteMode) {
        classToAdd = "delete-mode";
      }
      return classToAdd;
    }
  },
  methods: {
    widgetDropped(eventData) {
      this.$eventBus.$emit("widgetDropped", eventData);
    }
  }
};
</script>

<style>
.full-size {
  position: relative;
  height: 100%;
  width: 100%;
}

.edit-mode::after {
  content: "";
  display: block;
  width: 100%;
  height: 100%;
  border: 0.5rem dashed #b0f7b0;
}

.delete-mode {
  box-sizing: border-box;
  border: 1rem dashed #ca4b01;
}

.edit-mode .v-card {
  cursor: all-scroll;
}
</style>