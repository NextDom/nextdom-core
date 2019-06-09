import "muse-ui/dist/muse-ui.css";
import "muse-ui/src/theme/index";
import themeColors from "./theme-color.scss";
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
  `;
});
theme.use("nextdom");
