<template>
  <div class="door-state-cmd cmd">
    <i class="icon" v-bind:class="icon"></i>
  </div>
</template>

<script>
export default {
  name: "DoorInfoCmd",
  props: {
    cmd: null
  },
  data: function() {
    return {
      icon: "nextdom-porte-ferme"
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
    /**
     * Called on update for change icon
     */
    update() {
      if (this.cmd.cmdValue) {
        this.icon = "nextdom-porte-ouverte";
      } else {
        this.icon = "nextdom-porte-ferme";
      }
    }
  }
};
</script>
