<template>
  <div class="presence-state-cmd cmd">
    <span class="pull-left">{{ cmd.name }}</span>
    <i class="fas pull-right" v-bind:class="icon"></i>
  </div>
</template>

<script>
export default {
  name: "PresenceStateCmd",
  props: {
    cmd: null
  },
  data: function() {
    return {
      icon: "fa-walking"
    };
  },
  mounted() {
    this.update();
  },
  methods: {
    /**
     * Called on update for change icon
     */
    update() {
      if (this.cmd.state) {
        this.icon = "fa-walking";
      } else {
        this.icon = "fa-expand";
      }
    }
  }
};
</script>