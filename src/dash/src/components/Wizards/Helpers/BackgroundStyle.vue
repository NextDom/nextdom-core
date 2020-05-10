<template>
  <v-radio-group v-model="background" row>
    <v-radio value="no" label="Aucun" />
    <v-radio
      v-for="(backgroundData, backgroundName, index) in backgroundsList"
      v-bind:key="`background-${index}`"
      v-bind:value="`${backgroundName}.${backgroundData['ext']}`"
    >
      <template v-slot:label>
        <img v-if="backgroundData.thumb" v-bind:src="basePath + backgroundName + '-thumb.' + backgroundData.ext" />
        <img v-else v-bind:src="basePath + backgroundName + '.' + backgroundData.ext" />
      </template>
    </v-radio>
  </v-radio-group>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "BackgroundStyle",
  props: {
    value: {
      type: String,
      default: "no"
    }
  },
  data: () => ({
    basePath: "/data/backgrounds/",
    backgroundsList: []
  }),
  mounted() {
    Communication.get("/api/dash/backgrounds", files => {
      console.log(this.value);
      this.backgroundsList = files;
      console.log(this.value);
    });
  },
  computed: {
    background: {
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