<template>
  <div>
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <v-content>
        <v-btn text v-on:click="action" icon>
          <v-icon v-bind:style="{fontSize: widgetData.style.contentSize + 'px', height: widgetData.style.contentSize + 'px' }">{{ icon }}</v-icon>
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
    icon() {
      switch (this.widgetData.state) {
        case "stop":
          return "fa-play";
        default:
        case "in progress":
          return "fa-stop";
      }
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