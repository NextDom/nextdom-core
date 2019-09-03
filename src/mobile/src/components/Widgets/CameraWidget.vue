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
  <div v-packery-item class="packery-item large">
    <div class="widget-title">
      <span class="title">{{ eqlogic.name }}</span>
      <span class="actions pull-right">
        <mu-button
          class="pull-right"
          slot="action"
          icon
          v-on:click="executeCmd(refreshCmdId)"
          v-if="refreshCmdId"
        >
          <mu-icon value="refresh"></mu-icon>
        </mu-button>
      </span>
    </div>
    <div>
      <img class="thumbnail" v-bind:src="snapshotUrl" />
    </div>
  </div>
</template>

<script>
import BaseWidget from "./BaseWidget";
import { setInterval } from "timers";

/**
 * Show eqLogic widget
 * @group Components
 */
export default {
  name: "CameraWidget",
  extends: BaseWidget,
  data: function() {
    return {
      snapshotUrl: "",
      refreshInterval: 5000
    };
  },
  mounted() {
    this.refreshInterval = parseInt(
      this.$props.eqlogic.configuration["thumbnail::refresh"]
    );
    this.updateSnapshotUrl();
    let self = this;
    setInterval(function() {
      self.updateSnapshotUrl();
    }, self.refreshInterval * 1000);
  },
  methods: {
    /**
     * @vuese
     * Update the snapshot url with time
     */
    updateSnapshotUrl() {
      const now = new Date();
      this.snapshotUrl =
        "/plugins/camera/core/php/snapshot.php?id=" +
        this.$props.eqlogic.id +
        "&apikey=" +
        this.$props.eqlogic.configuration.localApiKey +
        "&t=" +
        now.getTime() +
        "&thumbnail=1";
    },
    /**
     * @vuese
     * Construct the stream URL from eqLogic data
     */
    constructStreamUrl(eqLogicConfiguration) {
      let url = "http";
      if (eqLogicConfiguration.protocole) {
        url = eqLogicConfiguration.protocole;
      }
      url += "://";
      if (eqLogicConfiguration.username) {
        url +=
          encodeURIComponent(eqLogicConfiguration.username) +
          ":" +
          encodeURIComponent(eqLogicConfiguration.password) +
          "@";
      }
      url += eqLogicConfiguration.ip;
      if (eqLogicConfiguration.port) {
        url += ":" + eqLogicConfiguration.port;
      }
      return url + eqLogicConfiguration.urlStream;
    }
  }
};
</script>
<style lang="scss" scoped>
.thumbnail {
  max-width: 100%;
  display: block;
  height: 20rem;
  margin-left: auto;
  margin-right: auto;
}
</style>
