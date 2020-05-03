<template>
  <v-card
    v-bind:style="widgetStyle"
    v-bind:class="widgetData.style.border ? '' : 'hide-border'"
    v-bind:draggable="$store.getters.editMode"
    v-on:dragstart="dragStartEvent"
    v-on:dragend="dragEndEvent"
  >
    <component class="widget-content" v-bind:is="widgetData.type" v-bind:widgetData="widgetData" />
    <v-btn v-if="$store.getters.editMode" right absolute color="red theme--dark" small v-on:click="remove">
      <v-icon>mdi-delete</v-icon>
    </v-btn>
  </v-card>
</template>

<script>
import WidgetTemplates from "@/libs/WidgetTemplates";

export default {
  name: "Widget",
  components: Object.assign(WidgetTemplates.components, {}),
  props: {
    absolute: {
      type: Boolean,
      default: false
    },
    widgetData: {}
  },
  data: () => ({
    dragData: null,
    dragStarted: false
  }),
  mounted() {
    this.$eventBus.$on("widgetDropped", dropData => {
      if (this.dragStarted) {
        this.$store.commit("updateWidgetPos", {
          id: this.widgetData.id,
          top: this.widgetData.pos.top + (dropData.y - this.dragData.y),
          left: this.widgetData.pos.left + (dropData.x - this.dragData.x)
        });
        this.dragStarted = false;
      }
    });
  },
  computed: {
    widgetStyle() {
      let result = {};
      if (this.$store.getters.dashType === "manual") {
        result = {
          position: "absolute",
          top: this.widgetData.pos.top + "px",
          left: this.widgetData.pos.left + "px"
        };
        if (this.widgetData.style.width === "auto") {
          result["width"] = this.widgetData.style.width;
        } else {
          result["width"] = this.widgetData.style.width + "px";
        }
        if (this.widgetData.style.height === "auto") {
          result["height"] = this.widgetData.style.height;
        } else {
          result["height"] = this.widgetData.style.height + "px";
        }
      } else {
        result = {
          width: "100%",
          height: "100%"
        };
      }
      result["backgroundColor"] = this.widgetData.style.transparent
        ? "transparent"
        : this.widgetData.style.backgroundColor;
      return result;
    }
  },
  methods: {
    dragStartEvent(dragData) {
      this.dragData = dragData;
      this.dragStarted = true;
    },
    dragEndEvent() {
      this.dragStarted = false;
    },
    remove() {
      this.$store.commit("removeWidget", this.widgetData.id);
    }
  }
};
</script>

<style scoped>
.widget-content {
  height: 100%;
  width: 100%;
}

.v-card.hide-border {
  box-shadow: none !important;
}
</style>