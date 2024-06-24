<template>
  <v-radio-group v-model="picture" row>
    <v-radio v-for="(picture, index) in picturesList" v-bind:key="`pictures-${index}`" v-bind:value="picture">
      <template v-slot:label>
        <img v-bind:src="basePath + picture" />
      </template>
    </v-radio>
  </v-radio-group>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "ImgStyle",
  props: {
    value: String
  },
  data: () => ({
    picturesList: [],
    basePath: "/data/pictures/"
  }),
  created() {
    Communication.get("/api/dash/pictures/", files => {
      this.picturesList = files;
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