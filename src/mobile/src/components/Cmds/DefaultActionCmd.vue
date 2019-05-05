<template>
  <div class="default-action-cmd"></div>
</template>

<script>
export default {
  name: "DefaultActionCmd",
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
