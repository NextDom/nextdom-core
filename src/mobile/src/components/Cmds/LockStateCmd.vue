<template>
  <div class="lock-state-cmd cmd icon">
    <i class="icon" v-bind:class="icon"></i>
  </div>
</template>

<script>
export default {
  name: "LockStateCmd",
  props: {
    cmd: null
  },
  data: function() {
    return {
      icon: "nextdom-lock-ferme"
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
      if (this.cmd.state) {
        this.icon = "nextdom-lock-ferme";
      } else {
        this.icon = "nextdom-lock-ouvert";
      }
    }
  }
};
</script>
