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
  <mu-container class="global settings">
    <h1>Paramètres</h1>
    <mu-button id="disconnect-button" color="primary" v-on:click="disconnect">
      <mu-icon left value="lock_open"></mu-icon>Déconnexion
    </mu-button>
    <mu-button id="force-desktop-button" color="secondary" v-on:click="forceDesktop()">
      <mu-icon left value="desktop_mac"></mu-icon>Version desktop
    </mu-button>
  </mu-container>
</template>

<script>
import Communication from "@/libs/Communication.js";

/**
 * Settings page
 * @group Pages
 */
export default {
  name: "Settings",
  components: {},
  mounted() {
    /**
     * @vuese
     * Update tabs and URL
     * @arg New URL
     */
    this.$emit("setCurrentView", "/settings");
  },
  methods: {
    /**
     * Disconnect user
     */
    disconnect: function() {
      Communication.disconnect();
      this.$emit("changeView", "/login");
    },
    /**
     * @vuese
     * Force user to desktop page
     */
    forceDesktop() {
      window.location = "/index.php?force_desktop=1";
    }
  }
};
</script>

<style scoped>
#disconnect-button,
#force-desktop-button {
  width: 100%;
  margin-bottom: 1rem;
}
</style>
