module.exports = {
  publicPath: process.env.NODE_ENV === "production" ? "/mobile" : "/",
  chainWebpack: config => {
    config.resolve.symlinks(false);
  },
  devServer: {
    proxy: "http://0.0.0.0:80"
  }
};
