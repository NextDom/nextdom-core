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
import BaseWidget from "./BaseWidget";

/**
 * Show eqLogic widget
 * @group Components
 */
export default {
  name: "DefaultWidget",
  extends: BaseWidget,
  computed: {
    /**
     * @vuese
     * Test if a large widget must be used
     */
    isLargeWidget: function() {
      let result = false;
      // Large 2 if icons
      if (this.iconCmds.length > 2) {
        result = true;
      }
      // Show buttons at right if there is more than 5 commands
      if (this.buttonCmds.length > 6) {
        result = true;
      }
      return result;
    }
  }
};
</script>