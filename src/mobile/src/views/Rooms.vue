<template>
  <mu-container class="global rooms">
    <h1>Rooms</h1>
    <template v-if="room">
      <mu-container class="button-wrapper">
        <mu-button class="pull-left" color="primary" v-bind:to="fatherLink" v-if="showFatherLink">
          <mu-icon left value="chevron_left"></mu-icon>
          <template v-if="room.father !== undefined">{{ room.father.name}}</template>
          <template v-else>Racine</template>
        </mu-button>
        <mu-button class="pull-right" color="secondary" v-bind:to="viewLink" v-if="room.id">
          <mu-icon left value="pageview"></mu-icon>Résumé
        </mu-button>
      </mu-container>
      <h2 v-if="room.id">{{ room.name }}</h2>
      <mu-grid-list v-bind:cols="3" v-bind:padding="0">
        <RoomWidget v-for="child in room.children" v-bind:key="child.id" v-bind:room="child"></RoomWidget>
      </mu-grid-list>
      <div class="room-config" v-if="room.id">
        <mu-container>
          <mu-expansion-panel>
            <div slot="header">Configuration</div>
            <mu-form v-bind:model="form">
              <mu-form-item prop="switch" label="Visibilité" label-position="left">
                <mu-switch v-model="form.isVisible" v-on:change="changeRoomVisibility"></mu-switch>
              </mu-form-item>
            </mu-form>
          </mu-expansion-panel>
          <mu-expansion-panel v-if="eqLogics.length > 0">
            <div slot="header">Equipements</div>
            <mu-list>
              <mu-list-item
                button
                v-bind:ripple="false"
                v-for="eqLogic in eqLogics"
                v-bind:key="eqLogic.id"
              >
                <mu-list-item-title>{{ eqLogic.name }}</mu-list-item-title>
                <mu-list-item-action v-on:click="changeEqLogicVisibility(eqLogic.id)">
                  <mu-icon
                    v-bind:data-id="eqLogic.id"
                    v-bind:value="eqLogicsVisibility[eqLogic.id]"
                  ></mu-icon>
                </mu-list-item-action>
              </mu-list-item>
            </mu-list>
          </mu-expansion-panel>
        </mu-container>
      </div>
    </template>
  </mu-container>
</template>

<script>
import RoomWidget from "@/components/RoomWidget.vue";
import communication from "../libs/communication.js";

export default {
  name: "rooms",
  data: function() {
    return {
      room: null,
      form: {
        isVisible: true
      },
      eqLogicsVisibility: {},
      eqLogics: []
    };
  },
  props: {
    roomId: undefined
  },
  components: {
    RoomWidget
  },
  computed: {
    showFatherLink: function() {
      if (this.room.id !== null) {
        return true;
      }
      return false;
    },
    fatherLink: function() {
      if (this.room.father === undefined) {
        return "/rooms";
      } else {
        return "/rooms/" + this.room.father.id;
      }
    },
    viewLink: function() {
      return "/" + this.room.id;
    }
  },
  mounted() {
    this.$emit("setCurrentView", "/rooms");
    // Get data from default room
    if (this.roomId === undefined) {
      communication.get("/api/room/get_roots", data => {
        this.room = data;
      });
    } else {
      // Get data from specific room
      communication.get("/api/room/get_tree/" + this.roomId, data => {
        this.room = data;
        this.initRoomConfig();
      });
    }
  },
  methods: {
    /**
     * Init visibility and get data
     */
    initRoomConfig() {
      // Init room visibility
      let isVisibleStoredValue = localStorage.getItem(
        "is-visible-room-" + this.room.id
      );
      if (isVisibleStoredValue !== null) {
        this.form.isVisible = isVisibleStoredValue === "true" ? true : false;
      }
      communication.get("/api/eqlogic/room/" + this.room.id, data => {
        // Loop with push for reactivity
        data.forEach(eqLogic => {
          this.initEqLogicVisibility(eqLogic.id);
          this.eqLogics.push(eqLogic);
        });
      });
    },
    /**
     * Change the visibility of the room in the summary
     */
    changeRoomVisibility() {
      localStorage.setItem(
        "is-visible-room-" + this.room.id,
        this.form.isVisible ? "true" : "false"
      );
    },
    /**
     * Init eqLogic visibility in local storage and data
     * @param {eqLogicId} int Id of the eqLogic to init
     */
    initEqLogicVisibility(eqLogicId) {
      const localStorageKey = "is-visible-eqLogic-" + eqLogicId;
      let isVisibleStoredValue = localStorage.getItem(localStorageKey);
      if (isVisibleStoredValue !== null) {
        this.eqLogicsVisibility[eqLogicId] =
          isVisibleStoredValue === "true" ? "visibility" : "visibility_off";
      } else {
        this.eqLogicsVisibility[eqLogicId] = "visibility";
        localStorage.setItem(localStorageKey, "true");
      }
    },
    /**
     * Method called on visibility update click
     * @param {eqLogicId} int Id of the eqLogic with a visibility to change
     */
    changeEqLogicVisibility(eqLogicId) {
      let temp = this.eqLogicsVisibility;

      if (temp[eqLogicId] === "visibility") {
        localStorage.setItem("is-visible-eqLogic-" + eqLogicId, "false");
        temp[eqLogicId] = "visibility_off";
      } else {
        localStorage.setItem("is-visible-eqLogic-" + eqLogicId, "true");
        temp[eqLogicId] = "visibility";
      }
      // Hack for DOM update with data change
      this.eqLogicsVisibility = Object.assign({}, temp);
    }
  }
};
</script>

<style scoped>
.button-wrapper::after {
  content: "";
  clear: both;
  display: block;
}
</style>