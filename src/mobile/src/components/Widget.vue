<template>
  <!--
  <mu-grid-tile class="widget" v-bind:cols="tileWidth" v-bind:rows="tileHeight">
  -->
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
  <!--

  </mu-grid-tile>
  -->
</template>

<script>
import templates from "@/libs/nextdomTemplates.js";
import communication from "@/libs/communication.js";
import AppEventsBus from "@/libs/appEventsBus";

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
    eqlogic: Object,
    cmds: [Array]
  },
  // Inject all commands components
  components: Object.assign(templates.components, {}),
  computed: {
    /**
     * Get tile width depends from number of icons
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
     * Execute an action linked to command
     * @param {int} cmdId Command id
     * @param {String} action Action to execute
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
     * Execute command
     * @param {int} cmdId Command Id
     */
    executeCmd(cmdId, options) {
      if (options === undefined) {
        communication.post("/api/cmd/exec/" + cmdId, undefined, errorData => {
          AppEventsBus.$emit("showError", errorData.error);
        });
      } else {
        communication.postWithOptions(
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
     * Set battery information on widget
     * @param {batteryIcon} string Material icon
     */
    setBatteryInfo(batteryIcon) {
      this.batteryIcon = batteryIcon;
    },
    /**
     * Get component of the command
     */
    getCmdComponent: function(cmdId) {
      return this.$store.getters.getCmdComponentData({ cmdId: cmdId })
        .component;
    },
    /**
     * Set refresh command if exists
     */
    setRefreshCommand: function(cmdId) {
      this.refreshCmdId = cmdId;
    }
  }
};
</script>

<style>
.packery-item {
  background-color: white;
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
  background-color: rgba(0, 0, 0, 0.1);
  font-size: 0.8rem;
  text-transform: uppercase;
  height: 2.2rem;
  line-height: 2.2rem;
  margin-bottom: 0.5rem;
}

.widget-title span.title {
  margin-left: 0.2rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 80%;
  display: block;
}

.widget-title span.title {
  position: absolute;
  left: 0;
}

.widget-title button {
  margin: 0;
  padding: 0;
  height: 2.2rem;
  width: 2.2rem;
}

.cmds-icon {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  justify-content: center;
  align-items: stretch;
  align-content: stretch;
}
.cmd.icon {
  font-size: 3rem;
  display: flex;
  flex: 1 1 auto;
}
.cmd.icon i {
  width: 100%;
  text-align: center;
  margin-bottom: 0.5rem;
}
.cmd::after,
.cmds-data::before {
  content: "";
  clear: both;
  display: block;
}
.cmds-button {
  text-align: center;
  overflow: hidden;
}
.cmds-button > .cmd {
  display: inline-block;
}
.cmds-button::after {
  content: "";
  clear: both;
}
.cmds-data {
  margin-top: 0.5rem;
}
.half-size {
  width: 50%;
  float: left;
}
</style>