<template>
  <div v-bind:style="gridStyle" class="grid-container" v-bind:class="previewClass">
    <span class="grid-group-btns" v-if="$store.getters.editMode && gridData.children.length === 0">
      <v-hover v-model="divideHorizontallyPreview">
        <v-btn id="fab-vertical" fab color="success" v-on:click="divide('horizontal')">&#9707;</v-btn>
      </v-hover>
      <v-hover v-model="divideVerticallyPreview">
        <v-btn id="fab-horizontal" fab color="success" v-on:click="divide('vertical')">&#9707;</v-btn>
      </v-hover>
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-btn fab color="primary" v-on="on" v-on:click="addWidget()">
            <v-icon>fa-plus</v-icon>
          </v-btn>
        </template>
        <span>Ajouter un élément</span>
      </v-tooltip>
    </span>
    <template v-else-if="gridData.children.length === 1">
      <Widget v-bind:widgetData="gridData.children[0].widgetData" />
    </template>
    <!-- TODO: Faire un v-for -->
    <template v-else-if="gridData.children.length === 2">
      <GridContainer v-model="gridData.children[0]" />
    </template>
    <template v-if="gridData.children.length === 2">
      <GridContainer v-model="gridData.children[1]" />
    </template>
  </div>
</template>

<script>
import Widget from "./Widgets/Widget";

export default {
  name: "GridContainer",
  components: {
    Widget
  },
  props: {
    value: Object
  },
  data: () => ({
    divideHorizontallyPreview: false,
    divideVerticallyPreview: false
  }),
  mounted() {
    this.$eventBus.$on("addedWidget", widgetId => {
      if (this.$store.getters.gridEventCaller === this.gridData.id) {
        this.gridData.children.push({
          children: [],
          orientation: "",
          type: "widget",
          widgetData: this.$store.getters.widgets[widgetId]
        });
      }
    });
  },
  computed: {
    gridData: {
      get() {
        return this.value;
      },
      set(newValue) {
        this.$emit("input", newValue);
      }
    },
    previewClass() {
      if (this.divideHorizontallyPreview) {
        return "horizontal-divide-preview";
      }
      if (this.divideVerticallyPreview) {
        return "vertical-divide-preview";
      }
      return "";
    },
    gridStyle() {
      let result = {};
      switch (this.gridData.children.length) {
        default:
        case 0:
          break;
        case 1:
          break;
        case 2:
          if (this.gridData.orientation === "horizontal") {
            result = { flexDirection: "row" };
          } else {
            result = { flexDirection: "column" };
          }
      }
      if (this.$store.getters.editMode) {
        result["boxShadow"] = "0 0 5px #b0f7b0";
      }
      return result;
    }
  },
  methods: {
    divide(orientation) {
      this.divideHorizontallyPreview = false;
      this.divideVerticallyPreview = false;
      this.gridData.children.push({
        id: this.gridData.id + "0",
        children: [],
        orientation: "",
        type: "grid"
      });
      this.gridData.children.push({
        id: this.gridData.id + "1",
        children: [],
        orientation: "",
        type: "grid"
      });
      this.gridData.orientation = orientation;
    },
    addWidget() {
      this.$store.commit("setGridEventCaller", this.gridData.id);
      this.$eventBus.$emit("showAddItemWizard");
    }
  }
};
</script>

<style>
.grid-container {
  width: 100%;
  height: 100%;
  display: flex;
  position: relative;
}
.grid-container > div {
  flex: 1 1 auto;
}
.horizontal-grid {
  flex-direction: row;
}
.vertical-grid {
  flex-direction: column;
}

.vertical-divide-preview {
  background: linear-gradient(
    180deg,
    rgba(0, 0, 0, 0) calc(50% - 1px),
    rgba(192, 192, 192, 1) calc(50%),
    rgba(0, 0, 0, 0) calc(50% + 1px)
  );
}

.horizontal-divide-preview {
  background: linear-gradient(
    90deg,
    rgba(0, 0, 0, 0) calc(50% - 1px),
    rgba(192, 192, 192, 1) calc(50%),
    rgba(0, 0, 0, 0) calc(50% + 1px)
  );
}

.grid-group-btns {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

#fab-vertical span,
#fab-horizontal span {
  font-size: 2.5rem;
  margin-top: -0.5rem;
}

#fab-horizontal {
  transform: rotateZ(90deg);
}
</style>