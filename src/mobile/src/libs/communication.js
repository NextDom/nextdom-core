import axios from "axios";

export default {
  lastError: {},
  connected: false,
  tokenDuration: 10,
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
  post(url, callbackFunc) {
    axios.post(url).then(response => {
      if (callbackFunc !== undefined) {
        callbackFunc(response);
      }
    });
  },
  /**
   * Ajax put query with post options
   * @param {String} url API url
   * @param {Object} postOptions Options to send
   * @param {Function} callbackFunc  Function called on response
   */
  postWithOptions(url, postOptions, callbackFunc) {
    // Transform options needed for $_POST filled
    let data = new FormData();
    for (let postOptionsKey in postOptions) {
      data.append(postOptionsKey, postOptions[postOptionsKey]);
    }
    axios.post(url, data).then(response => {
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
    if (!this.connected) {
      this.reconnect();
    }
    return this.connected;
  },
  /**
   * Try to reconnect if token is always valid
   */
  reconnect() {
    const timestampToHours = 1000 * 60 * 60;
    if (localStorage.getItem("token") !== undefined) {
      const tokenCreationDate = localStorage.getItem("tokenCreationDate");
      if (tokenCreationDate !== undefined) {
        const now = new Date();
        const nowTimestamp = now.valueOf();
        const timeDiff = nowTimestamp - tokenCreationDate;
        if (timeDiff / timestampToHours < this.tokenDuration) {
          axios.defaults.headers.common["X-AUTH-TOKEN"] = localStorage.getItem(
            "token"
          );
          this.connected = true;
        }
      }
    }
  },
  /**
   * Disconnect user
   */
  disconnect() {
    this.removeXAuthToken();
    this.connected = false;
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
    const creationDate = new Date();
    // Store data in localStorage
    localStorage.setItem("token", token);
    localStorage.setItem("tokenCreationDate", creationDate.valueOf());
    axios.defaults.headers.common["X-AUTH-TOKEN"] = token;
  },
  /**
   * Remove X auth token data
   */
  removeXAuthToken() {
    localStorage.removeItem("token");
    localStorage.removeItem("tokenCreationDate");
    if (axios.defaults.headers.common.hasOwnProperty("X-AUTH-TOKEN")) {
      delete axios.defaults.headers.common["X-AUTH-TOKEN"];
    }
  }
};
