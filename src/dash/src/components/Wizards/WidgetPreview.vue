<!--
Container de prévisualisation du Widget
-->
<template>
  <v-card v-bind:class="previewData.style.border ? '' : 'hide-border'" v-bind:style="widgetStyle">
    <component v-bind:is="previewData.type" v-bind:widgetData="previewData" />
  </v-card>
</template>

<script>
import WidgetTemplates from "@/libs/WidgetTemplates";

export default {
  name: "Widget",
  components: Object.assign(WidgetTemplates.components, {}),
  props: {
    previewData: {}
  },
  computed: {
    widgetStyle() {
      return {
        width: this.previewData.style.width + "px",
        height: this.previewData.style.height + "px",
        backgroundColor: this.previewData.style.transparent
          ? "transparent"
          : this.previewData.style.backgroundColor
      };
    }
  }
};
</script>

<style scoped>
.v-card {
  margin-left: auto;
  margin-right: auto;
}

.v-card.hide-border {
  box-shadow: none !important;
}
</style>