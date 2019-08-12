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
<template></template>

<script>
import CmdTemplates from "@/libs/NextdomCmdTemplates.js";
import Communication from "@/libs/Communication.js";
import AppEventsBus from "@/libs/AppEventsBus";

/**
 * Show eqLogic widget
 * @group Components
 */
export default {
  name: "BaseWidget",
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
  components: Object.assign(CmdTemplates.components, {}),

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
