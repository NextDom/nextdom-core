<template>
  <div class="color-picker-cmd cmd">
    <mu-button v-on:click="openColorChoice">
      <mu-icon value="color_lens"></mu-icon>
    </mu-button>
    <mu-dialog title="Couleur" width="100%" max-width="100%" v-bind:open.sync="colorChoice">
      <ColorPicker
        v-bind:width="300"
        v-bind:height="300"
        v-bind:disabled="false"
        startColor="#FFFFFF"
        @colorChange="onColorChange"
      ></ColorPicker>
      <mu-button slot="actions" flat color="primary" v-on:click="closeColorChoice">Fermer</mu-button>
    </mu-dialog>
  </div>
</template>

<script>
import ColorPicker from "vue-color-picker-wheel";

export default {
  name: "SliderCmd",
  data: function() {
    return {
      colorChoice: false
    };
  },
  props: {
    cmd: null
  },
  components: {
    ColorPicker
  },
  mounted() {},
  methods: {
    /**
     * Read slider change
     */
    onColorChange(newColor) {
      this.$emit("executeCmd", this.cmd.id, { color: newColor });
    },
    openColorChoice() {
      console.log("test");
      this.colorChoice = true;
    },
    closeColorChoice() {
      this.colorChoice = false;
    }
  }
};
</script>

<style scoped>
.cpw_container {
  margin-left: auto;
  margin-right: auto;
}
</style>
