import DefaultCmd from "@/components/Cmds/DefaultCmd.vue";
import DefaultInfoCmd from "@/components/Cmds/DefaultInfoCmd.vue";
import DefaultActionCmd from "@/components/Cmds/DefaultActionCmd.vue";
import PlugStateCmd from "@/components/Cmds/PlugStateCmd.vue";
import DoorStateCmd from "@/components/Cmds/DoorStateCmd.vue";
import LightStateCmd from "@/components/Cmds/LightStateCmd.vue";
import ConsumptionInfoCmd from "@/components/Cmds/ConsumptionInfoCmd.vue";
import PowerInfoCmd from "@/components/Cmds/PowerInfoCmd.vue";
import SabotageInfoCmd from "@/components/Cmds/SabotageInfoCmd.vue";

export default {
  components: {
    DefaultCmd,
    DefaultInfoCmd,
    DefaultActionCmd,
    PlugStateCmd,
    DoorStateCmd,
    LightStateCmd,
    ConsumptionInfoCmd,
    PowerInfoCmd,
    SabotageInfoCmd
  },
  cmds: {
    info: {
      binary: {
        prise: "PlugStateCmd",
        door: "DoorStateCmd"
      },
      numeric: {
        line: "DefaultInfoCmd"
      }
    },
    action: {
      other: {
        prise: "DefaultActionCmd"
      }
    }
  },
  cmdsWithoutTemplate: {
    ENERGY_STATE: "PlugStateCmd",
    ENERGY_ON: "DefaultActionCmd",
    ENERGY_OFF: "DefaultActionCmd",
    LIGHT_STATE: "LightStateCmd",
    LIGHT_ON: "DefaultActionCmd",
    LIGHT_OFF: "DefaultActionCmd",
    POWER: "PowerInfoCmd",
    CONSUMPTION: "ConsumptionInfoCmd",
    SABOTAGE: "SabotageInfoCmd"
  }
};
