<template>
  <mu-container class="default-string-cmd cmd" v-if="parseInt(cmd.visible) === 1">
    <span class="pull-left">{{ cmd.name }}</span>
    <span class="pull-right">{{ cmd.state }}</span>
  </mu-container>
</template>

<script>
export default {
  name: "DefaultStringCmd",
  props: {
    cmd: null
  },
  mounted() {
    this.$store.commit("addShowedCmd", { cmd: this.cmd });
  }
};
</script>
