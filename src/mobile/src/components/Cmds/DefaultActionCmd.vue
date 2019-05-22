<template>
  <div class="default-action-cmd cmd">
    <mu-button v-on:click="action">{{ cmd.name }}</mu-button>
  </div>
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
  },
  methods: {
    action: function() {
      this.$emit("executeCmd", this.cmd.id);
    }
  }
};
</script>

<style scoped>
button {
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
</style>
