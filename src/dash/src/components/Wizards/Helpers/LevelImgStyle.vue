<template>
  <v-radio-group v-model="picture" row>
    <v-radio v-for="(pictures, name, index) in picturesList" v-bind:key="`pictures-${index}`" v-bind:value="name">
      <template v-slot:label>
        <img v-bind:src="pictures.min" />/
        <img v-bind:src="pictures.max" />
      </template>
    </v-radio>
  </v-radio-group>
</template>

<script>
import Data from "@/libs/Data";

export default {
  name: "LevelImgStyle",
  props: {
    value: String
  },
  computed: {
    picturesList() {
      let result = {};
      for (let pictureCode in Data.assets.var.list) {
        result[pictureCode] = {
          min: require("../../../assets/buttons/var/" + pictureCode + "-0.png"),
          max: require("../../../assets/buttons/var/" +
            pictureCode +
            "-100.png")
        };
      }
      return result;
    },
    picture: {
      get() {
        return this.value;
      },
      set(newValue) {
        this.$emit("input", newValue);
      }
    }
  }
};
</script>