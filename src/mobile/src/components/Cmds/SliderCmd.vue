<!--
This file is part of NextDom Software.

NextDom is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

NextDom Software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.

@Support <https://www.nextdom.org>
@Email   <admin@nextdom.org>
@Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
-->
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
/**
 * Show slider for graduable value change
 * @group Commands
 */
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
    // Command object
    cmd: null
  },
  computed: {
    /**
     * @vuese
     * Return parsed value
     */
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
     * @vuejs
     * Called on slider change value
     */
    sliderChange(value) {
      // Send event to Widget component that execute command on NextDom
      // @arg Id of the command to execute
      this.$emit("executeCmd", this.cmd.id, { slider: value });
    }
  }
};
</script>

<style>
</style>
