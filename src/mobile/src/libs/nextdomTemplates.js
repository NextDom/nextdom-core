import DefaultCmd from "@/components/Cmds/DefaultCmd.vue";
import DefaultInfoCmd from "@/components/Cmds/DefaultInfoCmd.vue";
import DefaultActionCmd from "@/components/Cmds/DefaultActionCmd.vue";
import ActionOnIconCmd from "@/components/Cmds/ActionOnIconCmd.vue";
import PlugStateCmd from "@/components/Cmds/PlugStateCmd.vue";
import DoorStateCmd from "@/components/Cmds/DoorStateCmd.vue";
import LightStateCmd from "@/components/Cmds/LightStateCmd.vue";
import SliderCmd from "@/components/Cmds/SliderCmd.vue";
import ColorPickerCmd from "@/components/Cmds/ColorPickerCmd.vue";
import LineStateCmd from "@/components/Cmds/LineStateCmd.vue";
import PresenceStateCmd from "@/components/Cmds/PresenceStateCmd.vue";
import ConsumptionInfoCmd from "@/components/Cmds/ConsumptionInfoCmd.vue";
import PowerInfoCmd from "@/components/Cmds/PowerInfoCmd.vue";
import SabotageInfoCmd from "@/components/Cmds/SabotageInfoCmd.vue";
import RefreshCmd from "@/components/Cmds/RefreshCmd.vue";

export default {
  components: {
    DefaultCmd,
    DefaultInfoCmd,
    DefaultActionCmd,
    ActionOnIconCmd,
    ColorPickerCmd,
    PlugStateCmd,
    DoorStateCmd,
    LightStateCmd,
    LineStateCmd,
    PresenceStateCmd,
    SliderCmd,
    ConsumptionInfoCmd,
    PowerInfoCmd,
    SabotageInfoCmd,
    RefreshCmd
  },
  cmds: {
    info: {
      binary: {
        prise: {
          component: "PlugStateCmd",
          icon: true,
          button: false
        },
        door: {
          component: "DoorStateCmd",
          icon: true,
          button: false
        },
        line: {
          component: "LineStateCmd",
          icon: false,
          button: false
        },
        presence: {
          component: "PresenceStateCmd",
          icon: false,
          button: false
        }
      },
      numeric: {
        line: {
          component: "DefaultInfoCmd",
          icon: false,
          button: false
        }
      }
    },
    action: {
      slider: {
        light: {
          component: "SliderCmd",
          icon: false,
          button: false
        },
        default: {
          component: "SliderCmd",
          icon: false,
          button: false
        }
      },
      other: {
        prise: {
          component: "ActionOnIconCmd",
          icon: false,
          button: false
        },
        no_data: {
          component: "DefaultActionCmd",
          icon: false,
          button: true
        }
      }
    }
  },
  cmdsWithoutTemplate: {
    BRIGHTNESS: {
      component: "DefaultInfoCmd",
      icon: false,
      button: false
    },
    ENERGY_STATE: {
      component: "PlugStateCmd",
      icon: true,
      button: false
    },
    ENERGY_ON: {
      component: "ActionOnIconCmd",
      icon: false,
      button: false
    },
    ENERGY_OFF: {
      component: "ActionOnIconCmd",
      icon: false,
      button: false
    },
    LIGHT_STATE: {
      component: "LightStateCmd",
      icon: true,
      button: false
    },
    LIGHT_ON: {
      component: "ActionOnIconCmd",
      icon: false,
      button: false
    },
    LIGHT_OFF: {
      component: "ActionOnIconCmd",
      icon: false,
      button: false
    },
    LIGHT_SET_COLOR: {
      component: "ColorPickerCmd",
      icon: false,
      button: true
    },
    LIGHT_SLIDER: {
      component: "SliderCmd",
      icon: false,
      button: false
    },
    POWER: {
      component: "PowerInfoCmd",
      icon: false,
      button: false
    },
    CONSUMPTION: {
      component: "ConsumptionInfoCmd",
      icon: false,
      button: false
    },
    SABOTAGE: {
      component: "SabotageInfoCmd",
      icon: false,
      button: false
    },
    TEMPERATURE: {
      component: "DefaultInfoCmd",
      icon: false,
      button: false
    }
  }
};
