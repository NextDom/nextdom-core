<template>
  <div class="sabotage-info-cmd cmd">Sabotage : {{ cmd.value }}</div>
</template>

<script>
export default {
  name: "SabotageInfoCmd",
  props: {
    cmd: null
  },
  mounted() {
    this.$store.commit("addShowedCmd", { cmd: this.cmd });
  }
};
</script>
