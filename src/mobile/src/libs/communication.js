import axios from "axios";

export default {
  lastError: {},
  connected: false,
  /**
   * Initialise communication helper
   * @param {router} router Vue router for redirection to login
   */
  init(router) {
    this.router = router;
  },
  /**
   * Ajax get query
   * @param {String} url API url
   * @param {function} callbackFunc Function called on response
   * @param {function} errorCallbackFunc Function called on error
   */
  get(url, callbackFunc, errorCallbackFunc) {
    axios
      .get(url)
      .then(response => {
        callbackFunc(response.data);
      })
      .catch(error => {
        if (error.response !== undefined && error.response.status === 403) {
          localStorage.setItem("token", null);
          this.router.push("/login");
        } else {
          if (errorCallbackFunc !== undefined) {
            errorCallbackFunc(error.response);
          } else {
            console.log(error);
          }
        }
      });
  },
  /**
   * Ajax put query
   * @param {String} url API url
   * @param {Function} callbackFunc  Function called on response
   */
  put(url, callbackFunc) {
    axios.put(url).then(response => {
      if (callbackFunc !== undefined) {
        callbackFunc(response);
      }
    });
  },
  /**
   * Connect to API and get JWT token
   * @param {*} username User login
   * @param {*} password User password
   * @param {*} callbackFunc Function called after connection try
   */
  connect(username, password, callbackFunc) {
    this.removeXAuthToken();
    axios
      .get("/api/connect?login=" + username + "&password=" + password)
      .then(response => {
        this.saveXAuthToken(response.data.token);
        this.connected = true;
        callbackFunc(true);
      })
      .catch(error => {
        this.connected = false;
        this.lastError = {
          status: error.response.status,
          error: error.response.data
        };
        callbackFunc(false);
      });
  },
  /**
   * Get connection state
   */
  isConnected() {
    return this.connected;
  },
  /**
   * Get last query error
   */
  getLastError() {
    return this.lastError;
  },
  /**
   * Save token in local storage
   * @param {*} token JWT token to save
   */
  saveXAuthToken(token) {
    this.connected = false;
    localStorage.setItem("token", token);
  },
  /**
   * Remove X auth token data
   */
  removeXAuthToken() {
    axios.defaults.headers.common["X-AUTH-TOKEN"] = null;
  }
};
