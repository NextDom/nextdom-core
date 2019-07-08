<!--
This file is part of NextDom Software.

NextDom is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

NextDom Software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.

@Support <https://www.nextdom.org>
@Email   <admin@nextdom.org>
@Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
-->
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

/**
 * Show color picker in a popup
 * @group Commands
 */
export default {
  name: "ColorPickedCmd",
  data: function() {
    return {
      colorChoice: false
    };
  },
  props: {
    // Command object
    cmd: null
  },
  components: {
    ColorPicker
  },
  mounted() {},
  methods: {
    /**
     * @vuese
     * Send the new color to NextDom
     * @arg String Hexadecimal code of the new color (with #)
     */
    onColorChange(newColor) {
      // Send event to Widget component that execute command on NextDom
      // @arg Id of the command to execute.<br/> Json object with the attribut color that contains the new color
      this.$emit("executeCmd", this.cmd.id, { color: newColor });
    },
    /**
     * @vuese
     * Open the color choice popup
     */
    openColorChoice() {
      this.colorChoice = true;
    },
    /**
     * @vuese
     * Close the color choice popup
     */
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
