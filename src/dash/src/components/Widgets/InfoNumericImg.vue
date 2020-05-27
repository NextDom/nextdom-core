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
export default {
  name: "InfoNumericImg",
  props: {
    widgetData: {}
  },
  data: () => ({
    mult: 1,
    pictureCode: ""
  }),
  mounted() {
    if (!this.widgetData.percent) {
      this.mult = 1 / this.max;
    }
  },
  computed: {
    icon() {
      return (
        "/data/pictures/level/" +
        this.widgetData.picture.name +
        "-" +
        this.getClosest(
          this.widgetData.state * this.mult,
          this.widgetData.picture.values
        ) +
        ".png"
      );
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
