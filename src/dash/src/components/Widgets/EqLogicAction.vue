<template>
  <div v-if="showIcon">
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <v-content>
        <v-btn text v-on:click="action" icon>
          <v-icon v-bind:style="{fontSize: widgetData.style.contentSize + 'px', height: widgetData.style.contentSize + 'px' }">{{ icon }}</v-icon>
        </v-btn>
      </v-content>
    </v-card-text>
  </div>
</template>

<script>
import Communication from "@/libs/Communication";
import Data from "@/libs/Data";

export default {
  name: "EqLogicAction",
  props: {
    widgetData: {}
  },
  computed: {
    showIcon: function() {
      return !(
        this.widgetData.icon === "" ||
        this.widgetData.icon === 0 ||
        this.widgetData.icon === undefined
      );
    },
    icon() {
      const iconCode = Data.iconGroups[this.widgetData.icon];
      if (typeof this.widgetData.state === "boolean" && this.widgetData.state) {
        return iconCode.on;
      } else if (this.widgetData.state > 0) {
        return iconCode.on;
      }
      return iconCode.off;
    }
  },
  methods: {
    action() {
      if (this.widgetData.state) {
        Communication.post("/api/cmd/exec/" + this.widgetData.offCommandId);
      } else {
        Communication.post("/api/cmd/exec/" + this.widgetData.onCommandId);
      }
    }
  }
};
</script>

<style scoped>
.v-card__title {
  justify-content: center;
}
</style>