<template>
  <mu-container class="sabotage-info-cmd cmd">
    <span class="pull-left">Sabotage</span>
    <i class="fas pull-right" v-bind:class="icon"></i>
  </mu-container>
</template>

<script>
export default {
  name: "SabotageInfoCmd",
  props: {
    cmd: null
  },
  data: function() {
    return {
      icon: "fa-check"
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
        this.icon = "fa-check";
      } else {
        this.icon = "fa-notifications_active";
      }
    }
  }
};
</script>
