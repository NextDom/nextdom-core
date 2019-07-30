import Vue from "vue";
import VueI18n from "vue-i18n";

// Init i18n package
Vue.use(VueI18n);

export const i18n = new VueI18n({
  locale: "en",
  fallbackLocale: "en",
  messages: {
    en: {
      advancedFeatures: "Advanced features",
      close: "Close",
      configuration: "Configuration",
      connect: "Connect",
      desktopVersion: "Desktop version",
      disconnect: "Disconnect",
      equipments: "Equipments",
      loginTitle: "Login",
      login: "Login",
      max: "Max",
      min: "Min",
      nothing: "Nothing",
      password: "Password",
      roomsTitle: "Rooms",
      scenariosTitle: "Scenarios",
      settingsTitle: "Settings",
      showLogs: "Show logs",
      summary: "Summary",
      summaryTitle: "Summary",
      visibility: "Visibility"
    },
    fr: {
      advancedFeatures: "Fonctions avancées",
      close: "Fermer",
      configuration: "Configuration",
      connect: "Connexion",
      desktopVersion: "Version desktop",
      disconnect: "Déconnexion",
      equipments: "Equipements",
      loginTitle: "Identification",
      login: "Identifiant",
      max: "Max",
      min: "Min",
      nothing: "Aucun",
      password: "Mot de passe",
      roomsTitle: "Pièces",
      scenariosTitle: "Scénarios",
      settingsTitle: "Paramètres",
      showLogs: "Afficher les journaux",
      summary: "Résumé",
      summaryTitle: "Résumé",
      visibility: "Visibilité"
    }
  }
});
