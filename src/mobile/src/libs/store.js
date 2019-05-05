import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export const store = new Vuex.Store({
  state: {
    showedCmds: [],
    actionsList: {}
  },
  mutations: {
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
    }
  }
});
