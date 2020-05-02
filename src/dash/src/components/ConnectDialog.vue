<!--
Boite de dialogue de connexion
-->
<template>
  <v-dialog v-model="showed" persistent>
    <v-card>
      <v-card-title>Connexion</v-card-title>
      <v-card-text>
        <v-container>
          <v-form ref="form" v-model="valid">
            <v-text-field v-model="login" label="Identifiant" required v-bind:rules="[v => !!v || 'Identifiant obligatoire']" />
            <v-text-field
              v-model="password"
              type="password"
              label="Mot de passe"
              required
              v-bind:rules="[v => !!v || 'Mot de passe obligatoire']"
              v-on:keyup.enter="connect"
            />
            <v-btn v-bind:disabled="!valid" v-on:click="connect">Connexion</v-btn>
          </v-form>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "ConnectDialog",
  data: () => ({
    valid: false,
    login: "",
    password: "",
    showed: true
  }),
  created() {
    // Test si la connexion n'a pas déjà été établie
    this.showed = !Communication.isConnected();
  },
  methods: {
    /**
     * Validation des identifiants
     */
    connect() {
      Communication.connect(this.login, this.password, result => {
        if (result) {
          this.$emit("connected");
          this.showed = false;
        } else {
          console.log("TODO Bad credentials");
        }
      });
    }
  }
};
</script>