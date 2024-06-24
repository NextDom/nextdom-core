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
  <div class="camera-card">
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">{{ widgetData.title }}</v-card-title>
    <img class="fit-content" v-bind:src="snapshotUrl" />
  </div>
</template>

<script>
/**
 * Show eqLogic widget
 * @group Components
 */
export default {
  name: "Camera",
  props: {
    widgetData: {}
  },
  data: function() {
    return {
      snapshotUrl: "",
      refreshUpdater: null
    };
  },
  mounted() {
    if (this.widgetData.snapshot !== null) {
      this.snapshotUrl = this.widgetData.snapshot;
    }
    this.updateSnapshotUrl();
    this.startRefreshProcess();
  },
  destroy() {
    this.stopRefreshProcess();
  },
  watch: {
    "widgetData.refreshInterval": function() {
      this.stopRefreshProcess();
      this.startRefreshProcess();
    }
  },
  methods: {
    startRefreshProcess() {
      if (
        this.widgetData.refreshInterval !== null &&
        this.widgetData.refreshInterval !== 0
      ) {
        this.refreshUpdater = setInterval(() => {
          this.updateSnapshotUrl();
        }, this.widgetData.refreshInterval * 1000);
      }
    },
    stopRefreshProcess() {
      if (this.refreshUpdater !== null) {
        clearInterval(this.refreshUpdater);
      }
    },
    /**
     * @vuese
     * Update the snapshot url with time
     */
    updateSnapshotUrl() {
      if (this.widgetData.eqLogicId !== -1) {
        const now = new Date();
        this.snapshotUrl =
          "/plugins/camera/core/php/snapshot.php?id=" +
          this.widgetData.eqLogicId +
          "&apikey=" +
          this.widgetData.localApiKey +
          "&t=" +
          now.getTime();
        if (!this.widgetData.quality) {
          this.snapshotUrl += "&thumbnail=1";
        }
      }
    },
    /**
     * @vuese
     * Construct the stream URL from eqLogic data
     * Le jour où le RTSP sera lisible par la balise video
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

<style>
.camera-card {
  width: 100%;
  height: 100%;
}

.camera-card .v-card__title {
  position: absolute;
  color: white;
  z-index: 200;
}
</style>