<template>
  <mu-container class="slider-cmd cmd">
    <span>{{ cmd.name }}</span>
    <mu-slider class="slider" v-model="cmd.state" @change="sliderChange"></mu-slider>
  </mu-container>
</template>

<script>
export default {
  name: "SliderCmd",
  props: {
    cmd: null
  },
  computed: {
    preparedValue() {
      return parseInt(this.cmd.state);
    }
  },
  mounted() {
    this.cmd.state = parseInt(this.cmd.state);
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
