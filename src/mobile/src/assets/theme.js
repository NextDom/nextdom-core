import "muse-ui/dist/muse-ui.css";
import "muse-ui/src/theme/index";
import themeColors from "./theme.scss";
import theme from "muse-ui/lib/theme";

theme.add("nextdom", {
  primary: themeColors.primary,
  secondary: themeColors.secondary,
  success: themeColors.success,
  warning: themeColors.warning,
  info: themeColors.info,
  error: themeColors.error,
  track: themeColors.track,
  text: {
    primary: themeColors.textPrimary,
    secondary: themeColors.textSecondary,
    alternate: themeColors.textAlternate,
    disabled: themeColors.textDisabled,
    hint: themeColors.textHint
  },
  divider: themeColors.divider,
  background: {
    paper: themeColors.backgroundPaper,
    chip: themeColors.backgroundChip,
    default: themeColors.backgroundDefault
  }
});

theme.addCreateTheme(theme => {
  return `
  body {
    color: ${theme.primary};
  }
  h1, h2, h3 {
    color: ${theme.primary};
  }
  .mu-bottom-nav {
    background-color: ${theme.primary};
  }
  .mu-bottom-nav-item {
    color: ${theme.textPrimary};
  }
  .home>h1, .rooms>h1, .scenarios>h1, .settings>h1, .login>h1 {
    margin: 0.5rem;
    text-align: center;
  }
  .dashboard>h2, .rooms>h2 {
    margin: 0.5rem;
  }
  .room-config {
    margin: 0.5rem -1% -1%;
  }
  .button-wrapper {
    margin: 0.5rem -1%;
    width: 102%;
  }
  .cmds-button, .cmds-data, .cmds-icon {
    padding: 0.5rem;
  }
  .cmd button {
    margin: 0.2rem;
  }
  .mu-slider {
    margin-left: 1%;
    margin-right: 1%;
    margin-bottom: 5px;
    width: 98%;
  }
  .widget-title span.title {
    margin-left: 0.5rem;
  }
  .widget-title {
    margin-bottom: 0;
  }
  .cmds-data .container:nth-child(odd) {
    background-color: #cccccc30;
  }
  .packery-item {
    padding-bottom: 0;
  }
  .mu-grid-tile-wrapper {
    padding: 0.2rem !important;
  }
  .mu-grid-tile-titlebar {
    height: 42px;
  }
  .mu-grid-tile {
    background-color: #fff;
  }
  .mu-grid-tile .icon > i {
    padding-top: 25%;
    font-size: 4rem;
  }
  .mu-text-field-input {
    padding-left: 0.5rem;
  }
  `;
});
theme.use("nextdom");
