/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */
import DefaultCmd from "@/components/Cmds/DefaultCmd.vue";
import DefaultInfoCmd from "@/components/Cmds/DefaultInfoCmd.vue";
import DefaultStringCmd from "@/components/Cmds/DefaultStringCmd.vue";
import DefaultActionCmd from "@/components/Cmds/DefaultActionCmd.vue";
import ActionLinkedCmd from "@/components/Cmds/ActionLinkedCmd.vue";
import BatteryStateCmd from "@/components/Cmds/BatteryStateCmd.vue";
import PlugStateCmd from "@/components/Cmds/PlugStateCmd.vue";
import DoorStateCmd from "@/components/Cmds/DoorStateCmd.vue";
import LightStateCmd from "@/components/Cmds/LightStateCmd.vue";
import SliderCmd from "@/components/Cmds/SliderCmd.vue";
import ColorPickerCmd from "@/components/Cmds/ColorPickerCmd.vue";
import LineStateCmd from "@/components/Cmds/LineStateCmd.vue";
import LockStateCmd from "@/components/Cmds/LockStateCmd.vue";
import PresenceStateCmd from "@/components/Cmds/PresenceStateCmd.vue";
import ConsumptionInfoCmd from "@/components/Cmds/ConsumptionInfoCmd.vue";
import PowerInfoCmd from "@/components/Cmds/PowerInfoCmd.vue";
import SabotageInfoCmd from "@/components/Cmds/SabotageInfoCmd.vue";
import RefreshCmd from "@/components/Cmds/RefreshCmd.vue";
import WindowStateCmd from "@/components/Cmds/WindowStateCmd.vue";

export default {
  components: {
    DefaultCmd,
    DefaultInfoCmd,
    DefaultStringCmd,
    DefaultActionCmd,
    ActionLinkedCmd,
    BatteryStateCmd,
    ColorPickerCmd,
    PlugStateCmd,
    DoorStateCmd,
    LightStateCmd,
    LineStateCmd,
    LockStateCmd,
    PresenceStateCmd,
    SliderCmd,
    ConsumptionInfoCmd,
    PowerInfoCmd,
    SabotageInfoCmd,
    RefreshCmd,
    WindowStateCmd
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
        lock: {
          component: "LockStateCmd",
          icon: false,
          button: false
        },
        presence: {
          component: "PresenceStateCmd",
          icon: false,
          button: false
        },
        default: {
          component: "LineStateCmd",
          icon: false,
          button: false
        },
        no_data: {
          component: "LineStateCmd",
          icon: false,
          button: false
        }
      },
      numeric: {
        line: {
          component: "DefaultInfoCmd",
          icon: false,
          button: false
        },
        tile: {
          component: "DefaultInfoCmd",
          icon: false,
          button: false
        },
        default: {
          component: "DefaultInfoCmd",
          icon: false,
          button: false
        },
        no_data: {
          component: "DefaultInfoCmd",
          icon: false,
          button: false
        }
      },
      string: {
        no_data: {
          component: "DefaultStringCmd",
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
        },
        no_data: {
          component: "SliderCmd",
          icon: false,
          button: false
        }
      },
      other: {
        prise: {
          component: "ActionLinkedCmd",
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
    BATTERY: {
      component: "BatteryStateCmd",
      icon: false,
      button: false
    },
    BRIGHTNESS: {
      component: "DefaultInfoCmd",
      icon: false,
      button: false
    },
    CONSUMPTION: {
      component: "ConsumptionInfoCmd",
      icon: false,
      button: false
    },
    ENERGY_STATE: {
      component: "PlugStateCmd",
      icon: true,
      button: false
    },
    ENERGY_ON: {
      component: "ActionLinkedCmd",
      icon: false,
      button: false
    },
    ENERGY_OFF: {
      component: "ActionLinkedCmd",
      icon: false,
      button: false
    },
    HEATING_STATE: {
      component: "LineStateCmd",
      icon: false,
      button: false
    },
    HUMIDITY: {
      component: "DefaultInfoCmd",
      icon: false,
      button: false
    },
    LIGHT_STATE: {
      component: "LightStateCmd",
      icon: true,
      button: false
    },
    LIGHT_ON: {
      component: "ActionLinkedCmd",
      icon: false,
      button: false
    },
    LIGHT_OFF: {
      component: "ActionLinkedCmd",
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
    OPENING: {
      component: "DoorStateCmd",
      icon: true,
      button: false
    },
    POWER: {
      component: "PowerInfoCmd",
      icon: false,
      button: false
    },
    PRESENCE: {
      component: "PresenceStateCmd",
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
    },
    OPENING_WINDOW: {
      component: "WindowStateCmd",
      icon: true,
      button: false
    }
  }
};
