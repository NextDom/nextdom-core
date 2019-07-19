import Vue from "vue";
import VueI18n from "vue-i18n";

// Init i18n package
Vue.use(VueI18n);

export const i18n = new VueI18n({
  locale: "en",
  fallbackLocale: "en",
  messages: {
    en: {
      close: "Close",
      configuration: "Configuration",
      connect: "Connect",
      desktopVersion: "Desktop version",
      disconnect: "Disconnect",
      equipments: "Equipments",
      loginTitle: "Login",
      login: "Login",
      nothing: "Nothing",
      password: "Password",
      roomsTitle: "Rooms",
      scenariosTitle: "Scenarios",
      settingsTitle: "Settings",
      summary: "Summary",
      summaryTitle: "Summary",
      visibility: "Visibility"
    },
    fr: {
      close: "Fermer",
      configuration: "Configuration",
      connect: "Connexion",
      desktopVersion: "Version desktop",
      disconnect: "Déconnexion",
      equipments: "Equipements",
      loginTitle: "Identification",
      login: "Identifiant",
      nothing: "Aucun",
      password: "Mot de passe",
      roomsTitle: "Pièces",
      scenariosTitle: "Scénarios",
      settingsTitle: "Paramètres",
      summary: "Résumé",
      summaryTitle: "Résumé",
      visibility: "Visibilité"
    }
  }
});
