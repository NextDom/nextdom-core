<template>
  <div>
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <v-content>
        <v-btn text v-on:click="action">
          <img v-bind:style="{height: widgetData.style.contentSize + 'px'}" v-bind:src="picture" />
        </v-btn>
      </v-content>
    </v-card-text>
  </div>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "ScenarioAction",
  props: {
    widgetData: {}
  },
  computed: {
    picture() {
      return "/data/pictures/" + this.widgetData.picture;
    }
  },
  methods: {
    action() {
      switch (this.widgetData.state) {
        case "stop":
          Communication.post(
            "/api/scenario/launch/" + this.widgetData.scenarioId
          );
          break;
        default:
        case "in progress":
          Communication.post(
            "/api/scenario/stop/" + this.widgetData.scenarioId
          );
          break;
      }
    }
  }
};
</script>

<style scoped>
.v-card__title {
  justify-content: center;
}
</style>