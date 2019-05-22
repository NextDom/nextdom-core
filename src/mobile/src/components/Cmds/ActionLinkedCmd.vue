<template>
  <div class="action-linked-cmd cmd">
    <mu-button v-if="showButton" v-on:click="action">{{ cmd.name }}</mu-button>
  </div>
</template>

<script>
export default {
  name: "ActionLinkedCmd",
  data: function() {
    return {
      showButton: false
    };
  },
  props: {
    cmd: null
  },
  mounted() {
    // Test if target state cmd is defined
    if (this.cmd.value !== 0) {
      this.$store.commit("addAction", {
        genericType: this.cmd.genericType,
        cmdId: this.cmd.id,
        cmdValue: this.cmd.value
      });
    } else {
      // If no link, show button
      this.showButton = true;
      this.$store.commit("addAction", {
        genericType: this.cmd.genericType,
        cmdId: this.cmd.id,
        cmdValue: this.cmd.value
      });
    }
  },
  methods: {
    action: function() {
      this.$emit("executeCmd", this.cmd.id);
    }
  }
};
</script>
