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
  <div class="default-action-cmd cmd">
    <mu-button v-on:click="action">{{ cmd.name }}</mu-button>
  </div>
</template>

<script>
/**
 * Show standard action with a button
 * @group Commands
 */
export default {
  name: "DefaultActionCmd",
  props: {
    // Command object
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
    /**
     * @vuese
     * Send action event to widget
     */
    action: function() {
      // Send event to Widget component that execute command on NextDom
      // @arg Id of the command to execute
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
