<!--
Container de prévisualisation du Widget
-->
<template>
  <div>
    <v-card v-bind:class="!previewData.style.border || previewData.style.transparent ? 'hide-border' : ''" v-bind:style="widgetStyle">
      <component v-bind:is="previewData.type" v-bind:widgetData="previewData" />
    </v-card>
    <div class="fake-preview">
      <component v-bind:is="previewData.type" v-bind:widgetData="previewData" />
    </div>
  </div>
</template>

<script>
import WidgetTemplates from "@/libs/WidgetTemplates";

export default {
  name: "Widget",
  components: Object.assign(WidgetTemplates.components, {}),
  props: {
    previewData: {}
  },
  mounted() {
    const resizeObserver = new ResizeObserver(entries => {
      this.$eventBus.$emit("previewWidthChange", {
        width: entries[0].contentRect.width * 1.05,
        height: entries[0].contentRect.height
      });
    });
    resizeObserver.observe(document.querySelectorAll(".fake-preview")[0]);
  },
  methods: {
    sendAutoSize() {
      const fakePreview = document.querySelectorAll(".fake-preview")[0];
      let count = 5;
      console.log(fakePreview.clientWidth);
      console.log(fakePreview);
      if (fakePreview !== undefined) {
        this.$eventBus.$emit("previewWidthChange", {
          width: fakePreview.clientWidth,
          height: fakePreview.clientHeight
        });
      }
      setTimeout(() => {
        console.log(fakePreview.clientWidth);
      }, 5000);
      /*
      console.log(
        window.getComputedStyle(
          document
            .querySelectorAll(".fake-preview")[0]
            .getPropertyValue("width")
        )
      );
      */
    }
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
  padding: 0;
}

.v-card.hide-border {
  box-shadow: none !important;
}

.fake-preview {
  position: absolute;
  visibility: hidden;
}
</style>