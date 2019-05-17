<template>
  <mu-container class="line-state-cmd cmd">
    <span class="pull-left">{{ cmd.name }}</span>
    <i class="fas pull-right" v-bind:class="icon"></i>
  </mu-container>
</template>

<script>
export default {
  name: "LineStateCmd",
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
        this.icon = "fa-times";
      }
    }
  }
};
</script>

<style scoped>
i {
  margin-top: 4px;
}
</style>
