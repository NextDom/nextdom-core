<template>
  <div class="light-state-cmd cmd icon">
    <i class="icon" v-bind:class="icon" v-on:click="action"></i>
  </div>
</template>

<script>
export default {
  name: "LightStateCmd",
  props: {
    cmd: null
  },
  data: function() {
    return {
      icon: "nextdom-lumiere-on"
    };
  },
  mounted() {
    this.update();
    this.$store.commit("addShowedCmd", {
      cmd: this.cmd,
      updateFunc: this.update
    });
  },
  methods: {
    action() {
      let action = "LIGHT_ON";
      if (this.cmd.state) {
        action = "LIGHT_OFF";
      }
      this.$emit("executeAction", this.cmd.id, action);
    },
    /**
     * Called on update for change icon
     */
    update() {
      if (this.cmd.state) {
        this.icon = "nextdom-lumiere-on";
      } else {
        this.icon = "nextdom-lumiere-off";
      }
    }
  }
};
</script>
