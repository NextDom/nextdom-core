<template>
  <div class="grid-container" v-bind:class="gridClass">
    <span class="grid-group-btns" v-if="$store.getters.editMode && gridData.children.length === 0">
      <v-hover v-model="divideHorizontallyPreview">
        <v-btn class="fab-vertical" fab color="success" v-on:click="divide('horizontal')">&#9707;</v-btn>
      </v-hover>
      <v-hover v-model="divideVerticallyPreview">
        <v-btn class="fab-horizontal" fab color="success" v-on:click="divide('vertical')">&#9707;</v-btn>
      </v-hover>
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-btn fab color="primary" v-on="on" v-on:click="addWidget()">
            <v-icon>fa-plus</v-icon>
          </v-btn>
        </template>
        <span>Ajouter un élément</span>
      </v-tooltip>
      <v-hover v-model="hoverDeleteState" v-if="root !== '0'">
        <v-btn fab color="error" v-on:click="$emit('delete')">
          <v-icon>mdi-delete</v-icon>
        </v-btn>
      </v-hover>
    </span>
    <template v-else-if="gridData.children.length === 1">
      <Widget v-bind:widgetData="$store.getters.widgets[gridData.children[0].widgetId]" v-on:remove="widgetRemoved" />
    </template>
    <template v-else-if="gridData.children.length === 2">
      <GridContainer
        v-for="(child, index) in gridData.children"
        v-model="gridData.children[index]"
        v-bind:key="`container-${root}${index.toString()}`"
        v-bind:root="root + index"
        v-on:delete="deleteChildren"
        v-on:setDeletePreviewState="setDeletePreviewState"
      />
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
    value: Object,
    root: {
      type: String,
      default: "0"
    }
  },
  data: () => ({
    divideHorizontallyPreview: false,
    divideVerticallyPreview: false,
    deletePreviewState: false,
    hoverDeleteState: false
  }),
  mounted() {
    this.$eventBus.$on("addedWidget", widgetId => {
      if (this.$store.getters.gridEventCaller === this.gridData.id) {
        this.gridData.children.push({
          children: [],
          orientation: "",
          type: "widget",
          widgetId: widgetId
        });
      }
    });
  },
  watch: {
    hoverDeleteState: function(newValue) {
      this.$emit("setDeletePreviewState", newValue);
    }
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
    gridClass() {
      let result = [];
      if (this.divideHorizontallyPreview) {
        result.push("horizontal-divide-preview");
      }
      if (this.divideVerticallyPreview) {
        result.push("vertical-divide-preview");
      }
      if (this.deletePreviewState) {
        result.push("delete-preview");
      }
      if (this.gridData.children.length === 2) {
        if (this.gridData.orientation === "horizontal") {
          result.push("horizontal-grid");
        } else {
          result.push("vertical-grid");
        }
      }
      if (this.$store.getters.editMode) {
        result.push("edit-container");
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
    },
    deleteChildren() {
      this.deletePreviewState = false;
      this.gridData.children = [];
    },
    setDeletePreviewState(state) {
      this.deletePreviewState = state;
    },
    widgetRemoved() {
      this.gridData.children = [];
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

.grid-group-btns {
  display: none;
}

.horizontal-grid {
  flex-direction: row;
}

.horizontal-grid > div {
  width: 50%;
}

.vertical-grid {
  flex-direction: column;
}

.vertical-grid > div {
  height: 50%;
}

.edit-container {
  box-shadow: 0 0 5px #b0f7b0;
}

.grid-container:hover > .grid-group-btns,
.grid-group-btns:hover {
  position: absolute;
  display: block;
  width: max-content;
  z-index: 9999;
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

.fab-vertical span,
.fab-horizontal span {
  font-size: 2.5rem;
  margin-top: -0.5rem;
}

.fab-horizontal {
  transform: rotateZ(90deg);
}

.delete-preview::after {
  pointer-events: none;
  position: absolute;
  content: "";
  display: block;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 0, 0, 0.2);
}
</style>