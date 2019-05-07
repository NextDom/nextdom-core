<template>
  <div class="refresh-cmd"></div>
</template>

<script>
export default {
  name: "RefreshCmd",
  props: {
    cmd: null
  },
  mounted() {
    this.$emit("setRefreshCommand", this.cmd.id);
  }
};
</script>
