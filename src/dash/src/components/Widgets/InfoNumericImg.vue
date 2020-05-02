<template>
  <div>
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <v-content>
        <img v-bind:style="{height: widgetData.style.contentSize + 'px' }" v-bind:src="icon" />
      </v-content>
    </v-card-text>
  </div>
</template>

<script>
import Data from "@/libs/Data";

export default {
  name: "InfoNumericImg",
  props: {
    widgetData: {}
  },
  data: () => ({
    mult: 1
  }),
  mounted() {
    if (!this.widgetData.percent) {
      this.mult = 1 / this.max;
    }
  },
  computed: {
    icon() {
      let picture = this.widgetData.picture;

      return require("../../assets/buttons/var/" +
        picture +
        "-" +
        this.getClosest(
          this.widgetData.state * this.mult,
          Data.assets.var.list[picture]
        ) +
        ".png");
    }
  },
  methods: {
    getClosest(target, listOfValues) {
      let result = listOfValues[0];
      let best = Math.abs(target - result);
      for (
        let searchIndex = 1;
        searchIndex < listOfValues.length;
        ++searchIndex
      ) {
        let diff = Math.abs(target - listOfValues[searchIndex]);
        if (diff < best) {
          best = diff;
          result = listOfValues[searchIndex];
        }
      }
      return result;
    }
  }
};
</script>

<style scoped>
.v-card__title {
  justify-content: center;
}
</style>