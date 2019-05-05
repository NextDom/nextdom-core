<template>
  <div class="plug-state-cmd cmd">
    <i class="fas" v-bind:class="icon" v-on:click="action"></i>
    {{ cmd.state }}
  </div>
</template>

<script>
export default {
  name: "PlugStateCmd",
  props: {
    cmd: null
  },
  data: function() {
    return {
      icon: "fa-plug"
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
      let action = "ENERGY_ON";
      if (this.cmd.state) {
        action = "ENERGY_OFF";
      }
      this.$emit("executeAction", this.cmd.id, action);
    },
    /**
     * Called on update for change icon
     */
    update() {
      if (this.cmd.state) {
        this.icon = "fa-plug";
      } else {
        this.icon = "fa-times";
      }
    }
  }
};
</script>
