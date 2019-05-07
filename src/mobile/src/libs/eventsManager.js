export default {
  loopStarted: false,
  /**
   * Initialize event manger
   * @param {communication} communication Communication helper
   * @param {store} store Store for data management
   */
  init(communication, store) {
    this.store = store;
    this.communication = communication;
  },
  /**
   * Call getNewEvents in background
   */
  loop() {
    if (!this.loopStarted) {
      let self = this;
      setTimeout(function() {
        self.getNewEvents();
      }, 1);
    }
  },
  /**
   * Get new events since last call
   */
  getNewEvents() {
    const currentDate = new Date();
    const timestamp = parseInt(currentDate.getTime() / 1000);
    this.communication.get(
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
      }
    });
    this.loop();
  }
};
