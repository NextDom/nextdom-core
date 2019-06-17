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
  <div v-packery-item class="packery-item" v-bind:class="[isLargeWidget ? 'large' : 'small']">
    <div class="widget-title">
      <span class="title">{{ eqlogic.name }}</span>
      <span class="actions pull-right">
        <mu-button class="pull-right" slot="action" icon v-if="batteryIcon">
          <mu-icon v-bind:value="batteryIcon"></mu-icon>
        </mu-button>
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
    <div class="cmds-icon" v-bind:class="{ 'half-size': largeWidget}">
      <component
        v-bind:cmd="cmd"
        v-bind:key="cmd.id"
        v-for="cmd in iconCmds"
        v-bind:is="getCmdComponent(cmd.id)"
        v-on:executeAction="executeAction"
      ></component>
    </div>
    <div class="cmds-button">
      <component
        v-bind:cmd="cmd"
        v-bind:key="cmd.id"
        v-for="cmd in buttonCmds"
        v-bind:is="getCmdComponent(cmd.id)"
        v-on:executeAction="executeAction"
        v-on:executeCmd="executeCmd"
        v-on:setRefreshCommand="setRefreshCommand"
      ></component>
    </div>
    <div class="cmds-data">
      <component
        v-bind:cmd="cmd"
        v-bind:key="cmd.id"
        v-for="cmd in dataCmds"
        v-bind:is="getCmdComponent(cmd.id)"
        v-on:executeAction="executeAction"
        v-on:executeCmd="executeCmd"
        v-on:setBatteryInfo="setBatteryInfo"
        v-on:setRefreshCommand="setRefreshCommand"
      ></component>
    </div>
  </div>
</template>

<script>
import Templates from "@/libs/NextdomTemplates.js";
import Communication from "@/libs/Communication.js";
import AppEventsBus from "@/libs/AppEventsBus";

/**
 * Show eqLogic widget
 * @group Components
 */
export default {
  name: "Widget",
  data: function() {
    return {
      refreshCmdId: null,
      largeWidget: false,
      batteryIcon: false,
      iconCmds: [],
      dataCmds: [],
      buttonCmds: []
    };
  },
  props: {
    // eqLogic data
    eqlogic: null,
    // List of commands
    cmds: {
      type: [Array],
      default: []
    }
  },
  // Inject all commands components
  components: Object.assign(Templates.components, {}),
  computed: {
    /**
     * @vuese
     * Test if a large widget must be used
     */
    isLargeWidget: function() {
      let result = false;
      // Width 2 if icons
      if (this.cmdsIconCount === 0) {
        result = false;
      } else if (this.cmdsIconCount > 2) {
        result = true;
      }
      // Show buttons at right if there is more than 5 commands
      if (this.buttonCmds.length > 8) {
        result = true;
      }
      return result;
    }
  },
  /**
   * Initialize cmd component data on create
   */
  created() {
    for (let cmdIndex = 0; cmdIndex < this.cmds.length; ++cmdIndex) {
      this.$store.commit("setCmdComponentData", this.cmds[cmdIndex]);
    }
    this.iconCmds = this.cmds.filter(
      cmd =>
        this.$store.getters.getCmdComponentData({ cmdId: cmd.id }).icon === true
    );
    this.dataCmds = this.cmds.filter(
      cmd =>
        this.$store.getters.getCmdComponentData({ cmdId: cmd.id }).icon ===
          false &&
        this.$store.getters.getCmdComponentData({ cmdId: cmd.id }).button ===
          false
    );
    this.buttonCmds = this.cmds.filter(
      cmd =>
        this.$store.getters.getCmdComponentData({ cmdId: cmd.id }).button ===
        true
    );
  },
  methods: {
    /**
     * @vuese
     * Execute an action linked to command
     * @arg cmdId Command id<br/>
     * @arg action Action to execute
     */
    executeAction(cmdId, action) {
      let cmdToCall = this.$store.getters.getAction({
        cmdId: cmdId,
        action: action
      });
      if (cmdToCall !== false) {
        this.executeCmd(cmdToCall);
      }
    },
    /**
     * @vuese
     * Execute command
     * @arg cmdId Command Id<br/>
     * @arg options Command options
     */
    executeCmd(cmdId, options) {
      if (options === undefined) {
        Communication.post("/api/cmd/exec/" + cmdId, undefined, errorData => {
          /**
           * Show error message
           * @arg Error informations
           */
          AppEventsBus.$emit("showError", errorData.error);
        });
      } else {
        Communication.postWithOptions(
          "/api/cmd/exec/" + cmdId,
          options,
          undefined,
          errorData => {
            AppEventsBus.$emit("showError", errorData.error);
          }
        );
      }
    },
    /**
     * @vuese
     * Set battery information on widget
     * @arg batteryIcon string Material icon
     */
    setBatteryInfo(batteryIcon) {
      this.batteryIcon = batteryIcon;
    },
    /**
     * @vuese
     * Get component of the command
     * @arg cmdId Id of the command
     */
    getCmdComponent: function(cmdId) {
      return this.$store.getters.getCmdComponentData({ cmdId: cmdId })
        .component;
    },
    /**
     * @vuese
     * Set refresh command if exists
     * @arg cmdId Id of the command to refresh
     */
    setRefreshCommand: function(cmdId) {
      this.refreshCmdId = cmdId;
    }
  }
};
</script>

<style lang="scss">
@import "../assets/theme-color.scss";

.packery-item {
  background-color: $textPrimary;
  margin: 1%;
  padding-bottom: 0.5rem;
}

.packery-item.small {
  width: 48%;
}

.packery-item.large {
  width: 98%;
}

.widget-title {
  position: relative;
  width: 100%;
  background-color: $primary;
  color: $textPrimary;
  font-size: 0.8rem;
  text-transform: uppercase;
  height: 2.2rem;
  line-height: 2.2rem;
  margin-bottom: 0;
}

.widget-title {
  .title {
    margin-left: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 80%;
    display: block;
  }

  span.title {
    position: absolute;
    left: 0;
  }

  button {
    margin: 0;
    padding: 0;
    height: 2.2rem;
    width: 2.2rem;
  }
}

.cmds-icon {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  justify-content: center;
  align-items: stretch;
  align-content: stretch;
}

.cmds-icon {
  .cmd.icon {
    font-size: 3rem;
    display: flex;
    flex: 1 1 auto;
  }
  .cmd.icon i {
    width: 100%;
    text-align: center;
    margin: 0.5rem 0;
  }
}

.packery-item .cmd::after,
.cmds-data::before {
  content: "";
  clear: both;
  display: block;
}

.cmds-data {
  .cmd {
    padding-right: 0.5rem;
    padding-left: 0.5rem;
  }
  .cmd:nth-child(odd) {
    background-color: $info;
  }
  .mu-slider {
    margin-left: 1%;
    margin-right: 1%;
    margin-bottom: 0.3rem;
    width: 98%;
  }
}

.cmds-button {
  text-align: center;
  overflow: hidden;
}

.cmds-button {
  .cmd {
    display: inline-block;
  }
  button {
    margin: 0.2rem;
  }
}
.cmds-button::after {
  content: "";
  clear: both;
}

.packery-item .half-size {
  width: 50%;
  float: left;
}
</style>
