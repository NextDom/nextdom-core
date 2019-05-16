export default {
  extractIcon(htmlIcon, defaultIcon) {
    if (htmlIcon !== "") {
      const iconRegexResult = htmlIcon.match(/.*class="(.*?)"/i);
      if (iconRegexResult.length > 1) {
        return iconRegexResult[1];
      }
    }
    return defaultIcon;
  }
};
