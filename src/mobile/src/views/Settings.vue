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
    <h1>{{ $t('settingsTitle') }}</h1>
    <mu-button id="disconnect-button" color="primary" v-on:click="disconnect">
      <mu-icon left value="lock_open"></mu-icon>
      {{ $t('disconnect') }}
    </mu-button>
    <mu-button id="force-desktop-button" color="secondary" v-on:click="forceDesktop()">
      <mu-icon left value="desktop_mac"></mu-icon>
      {{ $t('desktopVersion') }}
    </mu-button>
    <mu-expansion-panel>
      <div slot="header">
        <i class="fa fa-cogs"></i>
        {{ $t('advancedFeatures') }}
      </div>
      <mu-button id="show-logs" color="primary" v-on:click="showLogsListDialog">{{ $t('showLogs') }}</mu-button>
    </mu-expansion-panel>
    <mu-dialog transition="slide-bottom" scrollable fullscreen v-bind:open.sync="logDialogOpened">
      <mu-appbar color="primary" v-bind:title="currentLogFile">
        <mu-button slot="left" icon v-on:click="closeLogDialog">
          <mu-icon value="close"></mu-icon>
        </mu-button>
      </mu-appbar>
      <pre v-for="(logLine, index) in logContent" v-bind:key="index">{{ logLine }}</pre>
    </mu-dialog>
    <mu-dialog transition="slide-bottom" scrollable v-bind:open.sync="logsListDialogOpened">
      <mu-appbar color="primary" v-bind:title="$t('showLogs')">
        <mu-button slot="left" icon v-on:click="closeLogsListDialog">
          <mu-icon value="close"></mu-icon>
        </mu-button>
      </mu-appbar>
      <mu-list>
        <div v-for="(item, index) in logsList" v-bind:key="index">
          <template v-if="item.content.length > 0">
            <mu-list-item>
              <mu-list-item-action>
                <mu-icon value="folder"></mu-icon>
              </mu-list-item-action>
              {{ item.name }}
            </mu-list-item>
            <mu-list-item
              button
              v-on:click="showLogDialog(item.name + subItem.name)"
              v-for="(subItem, subItemIndex) in item.content"
              v-bind:key="subItemIndex"
            >
              <mu-list-item-action class="subfolder">
                <mu-icon value="list"></mu-icon>
              </mu-list-item-action>
              {{ subItem.name }}
            </mu-list-item>
          </template>
          <template v-else>
            <mu-list-item button v-on:click="showLogDialog(item.name)">
              <mu-list-item-action>
                <mu-icon value="list"></mu-icon>
              </mu-list-item-action>
              {{ item.name }}
            </mu-list-item>
          </template>
        </div>
      </mu-list>
    </mu-dialog>
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
  data() {
    return {
      logsListDialogOpened: false,
      logDialogOpened: false,
      logsList: [],
      currentLogFile: "",
      logContent: []
    };
  },
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
     * @vuese
     * Disconnect user
     */
    disconnect: function() {
      Communication.disconnect();
      this.$emit("changeView", "/login");
    },
    /**
     * @vuese
     * Show dialog of logs list
     */
    showLogsListDialog: function() {
      Communication.get("/api/logs/list", data => {
        this.logsList = data;
        this.logsListDialogOpened = true;
      });
    },
    /**
     * @vuese
     * Close dialog of logs list
     */
    closeLogsListDialog: function() {
      this.logsListDialogOpened = false;
    },
    /**
     * @vuese
     * Show dialog with content of a log file
     */
    showLogDialog: function(logFile) {
      this.closeLogsListDialog();
      const preparedLogFileName = logFile.replace("/", "___");
      Communication.get("/api/logs/get/" + preparedLogFileName, data => {
        this.logContent = data;
        this.currentLogFile = logFile;
        this.logDialogOpened = true;
      });
    },
    /**
     * @vuese
     * Close dialog of log file
     */
    closeLogDialog: function() {
      this.logDialogOpened = false;
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

.subfolder {
  margin-left: 1rem;
}

pre {
  margin: 0 1rem;
}
</style>
