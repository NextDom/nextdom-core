/**
 * Gestion de l'état global de l'application
 */
import Vue from "vue"
import Vuex from "vuex"

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        widgets: {},
        editMode: false,
        dashData: {},
        dashType: "grid",
        eventManagerStarted: false,
        gridEventCaller: -1
    },
    mutations: {
        addWidget(state, widgetData) {
            Vue.set(state.widgets, widgetData.id, widgetData);
        },
        removeWidget(state, widgetDataId) {
            Vue.delete(state.widgets, widgetDataId);
        },
        updateWidgetPos(state, data) {
            Vue.set(state.widgets[data.id].pos, 'top', data.top);
            Vue.set(state.widgets[data.id].pos, 'left', data.left);
        },
        setEditMode(state, newEditMode) {
            state.editMode = newEditMode;
        },
        /**
         * Update eventsManager loop state
         * @param {*} state Store access
         * @param {*} newEventManagerState New state
         */
        setEventsManagerState(state, newEventManagerState) {
            state.eventManagerStarted = newEventManagerState;
        },
        /**
         * Update command state
         * @param {*} state Store access
         * @param {*} payload Data to update {cmdId, newState}
         */
        updateCmd(state, payload) {
            for (let widget in state.widgets) {
                if (state.widgets[widget].cmdId === payload.cmdId) {
                    state.widgets[widget].state = payload.newState;
                }
            }
        },
        /**
         * Update scenario state
         * @param {*} state Store access
         * @param {*} payload Data to update {scenarioId, newState}
         */
        updateScenario(state, payload) {
            for (let widget in state.widgets) {
                if (state.widgets[widget].scenarioId === payload.scenarioId) {
                    Vue.set(state.widgets[widget], 'state', payload.newState);
                }
            }
        },
        setDashType(state, payload) {
            state.dashType = payload;
        },
        setGridEventCaller(state, payload) {
            state.gridEventCaller = payload;
        },
        initDash(state, payload) {
            state.dashData = payload;
        },
        initWidgets(state, payload) {
            state.widgets = payload;
        },
        loadFromLocalStorage(state) {
            state.dashData = JSON.parse(localStorage.dashData);
            state.widgets = JSON.parse(localStorage.widgetsData);
        },
        saveToLocalStorage(state) {
            localStorage.dashData = JSON.stringify(state.dashData);
            localStorage.widgetsData = JSON.stringify(state.widgets);
        },
    },
    getters: {
        widgets: state => state.widgets,
        editMode: state => state.editMode,
        isEventsManagerStarted: state => state.eventManagerStarted,
        dashType: state => state.dashType,
        dashData: state => state.dashData,
        gridEventCaller: state => state.gridEventCaller
    }
});