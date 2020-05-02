module.exports = {
  "publicPath": "/dash",
  "transpileDependencies": [
    "vuetify"
  ],
  devServer: {
    port: 8081,
    proxy: {
      "^/": {
        "target": 'http://0.0.0.0:888',
        //"target": 'http://10.0.0.100',
        "ws": true,
        "secure": false
      },
    }
  },
}
