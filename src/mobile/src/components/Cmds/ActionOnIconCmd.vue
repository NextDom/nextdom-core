<template>
  <div class="action-on-icon-cmd"></div>
</template>

<script>
export default {
  name: "ActionOnIconCmd",
  props: {
    cmd: null
  },
  mounted() {
    this.$store.commit("addAction", {
      genericType: this.cmd.genericType,
      cmdId: this.cmd.id,
      cmdValue: this.cmd.value
    });
  }
};
</script>
