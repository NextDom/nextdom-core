export default {
    loopStarted: false,
    /**
     * Initialize event manger
     * @param {Communication} Communication Communication helper
     * @param {store} store Store for data management
     */
    init(Communication, store) {
        this.store = store;
        this.Communication = Communication;
    },
    /**
     * Start event loop
     */
    loop() {
        if (!this.store.getters.isEventsManagerStarted) {
            this.store.commit("setEventsManagerState", true);
            this.execute();
        }
    },
    /**
     * Event loop execution
     */
    execute() {
        let self = this;
        setTimeout(function () {
            self.getNewEvents();
        }, 1);
    },
    /**
     * Get new events since last call
     */
    getNewEvents() {
        const currentDate = new Date();
        const timestamp = parseInt(currentDate.getTime() / 1000);
        this.Communication.get(
            "/api/changes/get/" + timestamp,
            this.dispatchEvents.bind(this)
        );
    },
    /**
     * Dispatch all events for update
     * @param {Array} events Event received
     */
    dispatchEvents(events) {
        events.result.forEach(event => {
            // Commands state
            if (event.name === "cmd::update") {
                this.store.commit("updateCmd", {
                    cmdId: parseInt(event.option.cmd_id),
                    newState: event.option.value
                });
            } else if (event.name === "scenario::update") {
                this.store.commit("updateScenario", {
                    scenarioId: parseInt(event.option.scenario_id),
                    newState: event.option.state
                });
            }
        });
        this.execute();
    }
};
