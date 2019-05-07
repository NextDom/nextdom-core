<template>
  <mu-grid-tile class="widget" v-bind:cols="tileWidth" v-bind:rows="tileHeight">
    <span slot="title">{{ eqlogic.name }}</span>
    <mu-button slot="action" icon v-on:click="executeCmd(refreshCmdId)" v-if="refreshCmdId">
      <mu-icon value="refresh"></mu-icon>
    </mu-button>
    <div class="cmds-icon" v-bind:class="{ 'half-size': bigWidget}">
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
        v-on:setRefreshCommand="setRefreshCommand"
      ></component>
    </div>
  </mu-grid-tile>
</template>

<script>
import templates from "@/libs/nextdomTemplates.js";
import communication from "@/libs/communication.js";

export default {
  name: "Widget",
  data: function() {
    return {
      iconsCount: 0,
      refreshCmdId: null,
      bigWidget: false
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
    tileWidth: function() {
      let result = this.iconsCount;
      if (this.iconsCount === 0) {
        result = 1;
      } else if (this.iconsCount > 2) {
        result = 2;
      }
      // Show buttons at right if there is more than 5 commands
      if (this.needBigWidget()) {
        result = 2;
      }
      return result;
    },
    /**
     * Get all commands with icon
     */
    iconCmds: function() {
      const cmdsWithIcon = this.cmds.filter(
        cmd =>
          this.$store.getters.getCmdComponentData({ cmdId: cmd.id }).icon ===
          true
      );
      //      this.iconsCount = cmdsWithIcon.length;
      return cmdsWithIcon;
    },
    tileHeight: function() {
      if (this.cmds.length > 14) {
        return 2;
      }
      return 1;
    },
    /**
     * Get all commands for data
     */
    dataCmds: function() {
      return this.cmds.filter(
        cmd =>
          this.$store.getters.getCmdComponentData({ cmdId: cmd.id }).icon ===
            false &&
          this.$store.getters.getCmdComponentData({ cmdId: cmd.id }).button ===
            false
      );
    },
    /**
     * Get all commands with button
     */
    buttonCmds: function() {
      return this.cmds.filter(
        cmd =>
          this.$store.getters.getCmdComponentData({ cmdId: cmd.id }).button ===
          true
      );
    }
  },
  /**
   * Initialize cmd component data on create
   */
  created() {
    for (let cmdIndex = 0; cmdIndex < this.cmds.length; ++cmdIndex) {
      this.$store.commit("setCmdComponentData", this.cmds[cmdIndex]);
    }
    this.iconsCount = this.iconCmds.length;
    this.bigWidget = this.needBigWidget();
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
        communication.post("/api/cmd/exec/" + cmdId);
      } else {
        communication.postWithOptions("/api/cmd/exec/" + cmdId, options);
      }
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
    },
    needBigWidget: function() {
      if (
        this.cmds.length - this.iconsCount > 6 &&
        this.buttonCmds.length > 0
      ) {
        return true;
      }
      return false;
    }
  }
};
</script>

<style>
.widget > .mu-grid-tile {
  padding-top: 0.5rem;
  background-color: white;
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
.cmd::after {
  content: "";
  clear: both;
  display: block;
}
.cmds-button > .cmd,
.cmds-button button {
  float: left;
}
.cmds-button::after {
  content: "";
  clear: both;
}
.half-size {
  width: 50%;
  float: left;
}
</style>