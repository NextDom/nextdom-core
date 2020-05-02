<template>
  <div v-if="showIcon">
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <v-content>
        <v-btn text v-on:click="action" icon>
          <v-icon v-bind:style="{fontSize: widgetData.style.contentSize + 'px' }">{{ widgetData.icon }}</v-icon>
        </v-btn>
      </v-content>
    </v-card-text>
  </div>
  <div v-else>
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">
      <v-btn text v-on:click="action" icon>{{ widgetData.title }}</v-btn>
    </v-card-title>
  </div>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "CmdAction",
  props: {
    widgetData: {}
  },
  computed: {
    showIcon: function() {
      return !(
        this.widgetData.icon === "" ||
        this.widgetData.icon === 0 ||
        this.widgetData.icon === undefined
      );
    }
  },
  methods: {
    action() {
      Communication.post("/api/cmd/exec/" + this.widgetData.cmdId);
    }
  }
};
</script>

<style scoped>
.v-card__title {
  justify-content: center;
}
</style>