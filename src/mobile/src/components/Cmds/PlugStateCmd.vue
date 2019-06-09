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
  <div class="plug-state-cmd cmd icon">
    <i class="fas" v-bind:class="icon" v-on:click="action"></i>
  </div>
</template>

<script>
/**
 * Show plug state with icon
 * @group Commands
 */
export default {
  name: "PlugStateCmd",
  props: {
    // Command object
    cmd: null
  },
  data: function() {
    return {
      icon: "fa-plug"
    };
  },
  mounted() {
    this.update();
    this.$store.commit("addShowedCmd", {
      cmd: this.cmd,
      updateFunc: this.update
    });
  },
  methods: {
    /**
     * @vuese
     * Send action event to widget
     */
    action() {
      let action = "ENERGY_ON";
      if (this.cmd.state) {
        action = "ENERGY_OFF";
      }
      // Send event to Widget component that execute an action linked
      // @arg Id of the command to execute
      this.$emit("executeAction", this.cmd.id, action);
    },
    /**
     * @vuejs
     * Called on update for change icon
     */
    update() {
      if (this.cmd.state) {
        this.icon = "fa-plug";
      } else {
        this.icon = "fa-times";
      }
    }
  }
};
</script>
