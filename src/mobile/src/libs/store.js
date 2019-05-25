/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */
import Vue from "vue";
import Vuex from "vuex";
import Templates from "@/libs/NextdomTemplates.js";

Vue.use(Vuex);

export const store = new Vuex.Store({
  state: {
    // List of showed commands
    showedCmds: [],
    // List of actions
    actionsList: {},
    // List component data by command id
    cmdsComponentsData: {},
    // List of eqLogics
    eqLogicsList: {}
  },
  mutations: {
    /**
     * Set component data linked to the command id
     * TODO: To reduce when all cases added
     * @param {*} state Store access
     * @param {Array} cmd Command object
     */
    setCmdComponentData(state, cmd) {
      let componentData = undefined;
      // Test for commands with template
      try {
        componentData = Templates["cmds"][cmd.type][cmd.subType][cmd.template];
      } catch {}
      // Test generic type
      if (componentData === undefined) {
        try {
          componentData = Templates["cmdsWithoutTemplate"][cmd.genericType];
        } catch {}
      }
      // Specials cases
      if (componentData === undefined) {
        const cmdName = cmd.name.toLowerCase();
        if (
          cmd.type === "action" &&
          cmd.subType === "other" &&
          (cmdName === "rafraichir" ||
            cmdName === "refresh" ||
            cmdName === "rafraîchir")
        ) {
          componentData = {
            component: "RefreshCmd",
            icon: false,
            button: false
          };
        }
      }
      // Set default
      if (componentData === undefined) {
        try {
          componentData = Templates["cmds"][cmd.type][cmd.subType]["no_data"];
        } catch {}
      }
      if (componentData === undefined) {
        let toShow =
          "No component for command Id : " +
          cmd.id +
          " - Name : " +
          cmd.name +
          " - Type : " +
          cmd.type +
          " - SubType : " +
          cmd.subType;
        if (cmd.genericType) {
          toShow += " - GenericType : " + cmd.genericType;
        }
        if (cmd.template) {
          toShow += " - Template : " + cmd.template;
        }
        if (cmd.value) {
          toShow += " - Value : " + cmd.value;
        }
        if (cmd.cmdValue) {
          toShow += " - CmdValue : " + cmd.cmdValue;
        }
        toShow += " - State : " + cmd.state;
        console.log(toShow);
        // No data found
        componentData = {
          component: "DefaultCmd",
          icon: false,
          button: false
        };
      }
      state.cmdsComponentsData[cmd.id] = componentData;
    },
    /**
     * Add showed command in list for updates
     * @param {*} state Store access
     * @param {Object} payload Command object and updateFunc {cmd, updateFunc}
     */
    addShowedCmd(state, payload) {
      state.showedCmds[payload.cmd.id] = payload.cmd;
      state.showedCmds[payload.cmd.id]["updateFunc"] = payload.updateFunc;
    },
    /**
     * Update command state
     * @param {*} state Store access
     * @param {*} payload Data to update {cmdId, newState}
     */
    updateCmd(state, payload) {
      if (state.showedCmds[payload.cmdId] !== undefined) {
        state.showedCmds[payload.cmdId].state = payload.newState;
        // Call update function if exists
        if (state.showedCmds[payload.cmdId].updateFunc !== undefined) {
          state.showedCmds[payload.cmdId].updateFunc();
        }
      }
    },
    /**
     * Add action (genericType) on command (cmdValue) from other command (cmdId)
     * @param {*} state Store access
     * @param {*} payload Action data {cmdId, cmdValue, genericType}
     */
    addAction(state, payload) {
      let action = {};
      action[payload.genericType] = payload.cmdId;
      if (!state.actionsList.hasOwnProperty(payload.cmdValue)) {
        state.actionsList[payload.cmdValue] = action;
      } else {
        state.actionsList[payload.cmdValue][payload.genericType] =
          payload.cmdId;
      }
    }
    /**
     * Set visibility of an eqLogic
     * @param {*} state Store access
     * @param {*} payload EqLogicId and visibility {eqLogicId, visibility}
     */
    /*
    setEqLogicVisibility(state, payload) {
      if (!state.eqLogicsList.hasOwnProperty(payload.eqLogicId)) {
        let eqLogicData = {
          visibility: payload.visibility
        }
        state.eqLogicsList[payload.eqLogicId] = eqLogicData;
      }
      else {
        state.eqLogicsList[payload.eqLogicId]['visibility'] = payload.visibility;
      }
    }
    */
  },
  getters: {
    /**
     * Get action link to a command
     * @param {Object} payload Id of the command and action requested {cmdId, action}
     */
    getAction: state => payload => {
      if (
        state.actionsList[payload.cmdId] !== undefined &&
        state.actionsList[payload.cmdId][payload.action] !== undefined
      ) {
        return state.actionsList[payload.cmdId][payload.action];
      }
      return false;
    },
    /**
     * Get component data of command
     * @param {Object} payload Id of the command {cmdId}
     */
    getCmdComponentData: state => payload => {
      return state.cmdsComponentsData[payload.cmdId];
    }
  }
});
