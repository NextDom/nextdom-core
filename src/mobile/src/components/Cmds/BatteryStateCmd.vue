<template>
  <div class="battery-state-cmd cmd icon"></div>
</template>

<script>
export default {
  name: "BatteryStateCmd",
  props: {
    cmd: null
  },
  data: function() {
    return {
      icon: "nextdom-porte-ferme"
    };
  },
  mounted() {
    this.update();
    this.$store.commit("addShowedCmd", {
      cmd: this.cmd,
      updateFunc: this.update
    });
  },
  methods: {
    /**
     * Called on update for change icon
     */
    update() {
      // TODO: Vérifier les valeurs
      let batteryIcon = "battery_full";
      if (this.cmd.value < 30) {
        batteryIcon = "battery_alert";
      }
      this.$emit("setBatteryInfo", batteryIcon);
    }
  }
};
</script>
