<template>
  <div>
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{fontSize: widgetData.style.titleSize + 'px'}">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <v-icon v-bind:style="{fontSize: widgetData.style.contentSize + 'px', height: widgetData.style.contentSize + 'px' }">{{ icon }}</v-icon>
    </v-card-text>
  </div>
</template>

<script>
import Data from "@/libs/Data";

export default {
  name: "InfoBinary",
  props: {
    widgetData: {}
  },
  computed: {
    icon() {
      const iconCode = Data.iconGroups[this.widgetData.icon];
      if (typeof this.widgetData.state === "boolean" && this.widgetData.state) {
        return iconCode.on;
      } else if (this.widgetData.state > 0) {
        return iconCode.on;
      }
      return iconCode.off;
    }
  }
};
</script>

