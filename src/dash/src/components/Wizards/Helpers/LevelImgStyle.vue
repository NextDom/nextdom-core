<template>
  <v-radio-group v-model="picture" row>
    <v-radio v-for="(pictureData, index) in picturesList" v-bind:key="`pictures-${index}`" v-bind:value="index">
      <template v-slot:label>
        <img v-bind:src="'/data/pictures/level/' + pictureData.name + '-0.png'" />/
        <img v-bind:src="'/data/pictures/level/' + pictureData.name + '-100.png'" />/
      </template>
    </v-radio>
  </v-radio-group>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "LevelImgStyle",
  props: {
    value: Object
  },
  data: () => ({
    basePath: "/data/pictures/level/",
    picturesListCache: {},
    picturesList: []
  }),
  mounted() {
    if (Object.keys(this.picturesListCache).length === 0) {
      Communication.get("/api/dash/pictures/level", files => {
        const codeExtract = /(.*)-(\d+)\.png/;
        let indexes = {};
        for (let pictureIndex in files) {
          const code = files[pictureIndex].match(codeExtract);
          // Objet pour retrouver l'index des images
          if (code.length > 2) {
            if (code[1] in indexes) {
              this.picturesList[indexes[code[1]]].values.push(code[2]);
            } else {
              // Créer l'info si un fichier avec ce code n'a jamais été trouvé
              indexes[code[1]] = this.picturesList.length;
              this.picturesList.push({
                name: code[1],
                values: [code[2]]
              });
            }
          }
        }
        // Définir les valeurs par défaut
        if (this.value.name in indexes) {
          this.$emit("input", this.picturesList[indexes[this.value.name]]);
        }
      });
    }
  },
  computed: {
    picture: {
      get() {
        return this.picturesList[this.value.name];
      },
      set(newValue) {
        this.$emit("input", this.picturesList[newValue]);
      }
    }
  }
};
</script>