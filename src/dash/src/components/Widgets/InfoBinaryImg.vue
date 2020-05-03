<template>
  <div>
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{fontSize: widgetData.style.titleSize + 'px'}">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <img v-bind:style="{height: widgetData.style.contentSize + 'px'}" v-bind:src="picture()" />
    </v-card-text>
  </div>
</template>

<script>
export default {
  name: "InfoBinaryImg",
  props: {
    widgetData: {}
  },
  computed: {
    titleStyle() {
      return "font-size: " + this.widgetData.style.titleSize + "px";
    }
  },
  methods: {
    picture() {
      let picture = this.widgetData.picture;
      if (typeof this.widgetData.state === "boolean" && this.widgetData.state) {
        picture += "-on";
      } else if (this.widgetData.state > 0) {
        picture += "-on";
      } else {
        picture += "-off";
      }
      return "/data/pictures/on-off/" + picture + ".png";
    }
  }
};
</script>

<style scoped>
.v-card__title {
  justify-content: center;
}
img {
  display: inline-block;
}
</style>