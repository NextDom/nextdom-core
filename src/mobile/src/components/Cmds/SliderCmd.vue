<template>
  <mu-container class="slider-cmd cmd">
    <span>{{ cmd.name }}</span>
    <mu-slider class="slider" v-model="sliderValue" @change="sliderChange"></mu-slider>
  </mu-container>
</template>

<script>
export default {
  name: "SliderCmd",
  data: function() {
    return {
      sliderValue: 0
    };
  },
  props: {
    cmd: null
  },
  computed: {
    preparedValue() {
      return parseInt(this.cmd.state);
    }
  },
  mounted() {
    this.sliderValue = parseInt(this.cmd.state);
  },
  methods: {
    /**
     * Read slider change
     * TODO: Surement des sliders avec un pas spécial à gérer
     */
    sliderChange(value) {
      this.$emit("executeCmd", this.cmd.id, { slider: value });
    }
  }
};
</script>

<style>
</style>
