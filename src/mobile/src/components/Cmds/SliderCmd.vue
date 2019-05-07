<template>
  <div class="slider-cmd cmd">
    <mu-container>
      <mu-slider class="slider" v-model="preparedValue" @change="sliderChange"></mu-slider>
    </mu-container>
  </div>
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
  mounted() {},
  methods: {
    /**
     * Read slider change
     * TODO: Surement des sliders avec un pas spécial à gérer
     */
    sliderChange(value) {
      console.log(value);
      this.$emit("executeCmd", this.cmd.id, { slider: value });
    }
  }
};
</script>

<style>
</style>
