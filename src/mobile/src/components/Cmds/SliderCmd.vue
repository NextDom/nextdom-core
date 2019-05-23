<template>
  <mu-container class="slider-cmd cmd">
    <span>{{ cmd.name }}</span>
    <mu-slider
      class="slider"
      v-bind:min="minValue"
      v-bind:max="maxValue"
      v-model="sliderValue"
      @change="sliderChange"
    ></mu-slider>
  </mu-container>
</template>

<script>
export default {
  name: "SliderCmd",
  data: function() {
    return {
      sliderValue: 0,
      minValue: 0,
      maxValue: 100
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
    if (this.cmd.hasOwnProperty("minValue")) {
      this.minValue = parseInt(this.cmd.minValue);
    }
    if (this.cmd.hasOwnProperty("maxValue")) {
      this.maxValue = parseInt(this.cmd.maxValue);
    }
  },
  methods: {
    /**
     * Read slider change
     */
    sliderChange(value) {
      this.$emit("executeCmd", this.cmd.id, { slider: value });
    }
  }
};
</script>

<style>
</style>
