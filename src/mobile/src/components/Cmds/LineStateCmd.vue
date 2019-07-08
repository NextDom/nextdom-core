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
  <mu-container class="line-state-cmd cmd">
    <span class="pull-left">{{ cmd.name }}</span>
    <i class="fas pull-right" v-bind:class="icon"></i>
  </mu-container>
</template>

<script>
/**
 * Show binary info command in line with icon
 * @group Commands
 */
export default {
  name: "LineStateCmd",
  props: {
    // Command object
    cmd: null
  },
  data: function() {
    return {
      icon: "fa-check"
    };
  },
  mounted() {
    this.update();
  },
  methods: {
    /**
     * @vuejs
     * Called on update for change icon
     */
    update() {
      if (this.cmd.state) {
        this.icon = "fa-check";
      } else {
        this.icon = "fa-times";
      }
    }
  }
};
</script>

<style scoped>
i {
  margin-top: 4px;
}
</style>
