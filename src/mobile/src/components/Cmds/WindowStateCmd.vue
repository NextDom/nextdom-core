<template>
  <mu-container class="window-state-cmd cmd icon">
    <i class="icon" v-bind:class="icon"></i>
  </mu-container>
</template>

<script>
export default {
  name: "WindowStateCmd",
  props: {
    cmd: null
  },
  data: function() {
    return {
      icon: "nextdom-fenetre-ferme"
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
        this.icon = "nextdom-fenetre-ouverte";
      } else {
        this.icon = "nextdom-fenetre-ferme";
      }
    }
  }
};
</script>
