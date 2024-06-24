<template>
  <v-radio-group v-model="picture" row>
    <v-radio v-for="(picture, index) in picturesList" v-bind:key="`pictures-${index}`" v-bind:value="picture">
      <template v-slot:label>
        <img v-bind:src="basePath + picture + '-on.png'" />/
        <img v-bind:src="basePath + picture + '-off.png'" />/
      </template>
    </v-radio>
  </v-radio-group>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "OnOffImgStyle",
  props: {
    value: String
  },
  data: () => ({
    picturesList: [],
    basePath: "/data/pictures/on-off/"
  }),
  created() {
    Communication.get("/api/dash/pictures/on-off", files => {
      let result = [];
      const codeExtract = /(.*)-o(?:n|ff)\.png/;
      for (let pictureIndex in files) {
        const code = files[pictureIndex].match(codeExtract);
        if (code.length > 1) {
          result.push(code[1]);
        }
      }
      this.picturesList = Array.from(new Set(result));
    });
  },
  computed: {
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