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
import CmdTemplates from "@/libs/NextdomCmdTemplates.js";

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
    eqLogicsList: {},
    // EqLogics position
    eqLogicsOrder: undefined
  },
  mutations: {
    /**
     * Initialize data from localStorage
     * @param {*} state
     */
    initialize(state) {
      if (state.eqLogicsOrder === undefined) {
        const rawEqLogicsOrder = localStorage.getItem("eqLogicsOrder");
        if (rawEqLogicsOrder === undefined || rawEqLogicsOrder === null) {
          state.eqLogicsOrder = {};
          localStorage.setItem(
            "eqLogicsOrder",
            JSON.stringify(state.eqLogicsOrder)
          );
        } else {
          state.eqLogicsOrder = JSON.parse(rawEqLogicsOrder);
        }
      }
    },
    /**
     * Set component data linked to the command id
     * TODO: To reduce when all cases added
     * @param {*} state Store access
     * @param {Array} cmd Command object
     */
    setCmdComponentData(state, cmd) {
      if (cmd.id == 1205 || cmd.id == 1206) {
        console.log(cmd);
      }
      let componentData = undefined;
      // Test generic type
      try {
        componentData = CmdTemplates["cmdsWithoutTemplate"][cmd.genericType];
      } catch {}
      // Test for commands with template
      if (componentData === undefined) {
        try {
          componentData =
            CmdTemplates["cmds"][cmd.type][cmd.subType][cmd.template];
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
          componentData =
            CmdTemplates["cmds"][cmd.type][cmd.subType]["no_data"];
        } catch {}
      } else if (
        componentData.component === "DefaultInfoCmd" &&
        cmd.icon !== ""
      ) {
        componentData = {
          component: "DefaultIconInfoCmd",
          icon: true,
          button: false
        };
      }
      if (componentData === undefined) {
        // TODO: Peut être dans les types générique, mais il faut vérifier si il n'y a pas de cas particuliers
        if (cmd.genericType === "DONT") {
          componentData = {
            component: "DontCmd",
            icon: false,
            button: false
          };
        } else {
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
    },
    /**
     * Update eqLogics order from incomplete list
     * @param {*} state Store access
     * @param {*} payload List of positions to update
     */
    updateEqLogicsOrder(state, payload) {
      let toUpdateKeys = Object.keys(payload);
      for (let i = 0; i < toUpdateKeys.length; ++i) {
        state.eqLogicsOrder[toUpdateKeys[i]] = payload[toUpdateKeys[i]];
      }
      this.commit("saveEqLogicsOrder", state.eqLogicsOrder);
    },
    /**
     * Update eqLogics order from incomplete list
     * @param {*} state Store access
     * @param {*} payload List to save
     */
    saveEqLogicsOrder(state, payload) {
      state.eqLogicsOrder = payload;
      localStorage.setItem(
        "eqLogicsOrder",
        JSON.stringify(state.eqLogicsOrder)
      );
    }
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
    },
    /**
     * Get eqLogics order
     */
    getEqLogicsOrder: state => payload => {
      return state.eqLogicsOrder;
    }
  }
});
