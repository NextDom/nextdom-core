<template>
  <div>
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <v-content>
        <v-icon v-bind:style="{fontSize: widgetData.style.contentSize + 'px' }" v-if="widgetData.icon !== ''" left>{{ widgetData.icon }}</v-icon>
        <span class="value" v-bind:style="{fontSize: widgetData.style.contentSize + 'px' }">{{ widgetData.state }}</span>
        <span v-bind:style="{fontSize: widgetData.style.contentSize + 'px' }" v-if="widgetData.unit !== ''">{{ widgetData.unit }}</span>
      </v-content>
    </v-card-text>
  </div>
</template>

<script>
export default {
  name: "InfoNumeric",
  props: {
    widgetData: {}
  }
};
</script>

<style scoped>
.v-card__title {
  justify-content: center;
}
.value {
  margin: 1rem;
}

i {
  vertical-align: bottom !important;
  margin: 0;
}
</style>