<template>
  <div>
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <v-content>
        <v-btn text v-bind:to="{name: 'dash', params: {dashId: widgetData.target }}">
          <img v-bind:style="{height: widgetData.style.contentSize + 'px'}" v-bind:src="picture" />
        </v-btn>
      </v-content>
    </v-card-text>
  </div>
</template>

<script>
export default {
  name: "LinkToDash",
  props: {
    widgetData: {}
  },
  computed: {
    picture() {
      return "/data/pictures/" + this.widgetData.picture;
    }
  }
};
</script>

<style scoped>
.v-card__title {
  justify-content: center;
}
</style>