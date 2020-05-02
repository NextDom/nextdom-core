<template>
  <v-radio-group v-model="picture" row>
    <v-radio
      v-for="(pictures, name, index) in picturesList"
      v-bind:key="`pictures-${index}`"
      v-bind:value="name"
    >
      <template v-slot:label>
        <img v-bind:src="pictures.on" />/
        <img v-bind:src="pictures.off" />
      </template>
    </v-radio>
  </v-radio-group>
</template>

<script>
import Data from "@/libs/Data";

export default {
  name: "OnOffImgStyle",
  props: {
    value: String
  },
  computed: {
    picturesList() {
      let result = {};
      for (let pictureIndex in Data.assets.buttons.list) {
        const code = Data.assets.buttons.list[pictureIndex];
        result[code] = {
          on: require("../../../assets/buttons/on-off/" + code + "-on.png"),
          off: require("../../../assets/buttons/on-off/" + code + "-off.png")
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