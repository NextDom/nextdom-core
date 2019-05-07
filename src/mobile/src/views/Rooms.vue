<template>
  <mu-container class="rooms">
    <h1>Rooms</h1>
    <template v-if="room">
      <mu-button flat color="primary" v-bind:to="fatherLink" v-if="showFatherLink">
        <mu-icon left value="chevron_left"></mu-icon>
        <template v-if="room.father !== undefined">{{ room.father.name}}</template>
        <template v-else>Racine</template>
      </mu-button>
      <mu-button flat color="secondary" v-bind:to="viewLink" v-if="room.father">
        <mu-icon left value="pageview"></mu-icon>Résumé
      </mu-button>
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
                <mu-switch v-model="form.isVisible" v-on:change="changeVisibility"></mu-switch>
              </mu-form-item>
            </mu-form>
          </mu-expansion-panel>
          <mu-expansion-panel v-if="eqLogics.length > 0">
            <div slot="header">Equipements</div>
            <mu-list>
              <mu-list-item v-for="eqLogic in eqLogics" v-bind:key="eqLogic.id">
                <span class="pull-left">{{ eqLogic.name }}</span>
                <!--
                <mu-form v-bind:model="eqLogicsVisibility">
                  <mu-form-item
                    prop="switch"
                    class="pull-right"
                    label="Visibilité"
                    label-position="left"
                  >
                    <mu-switch v-model="eqLogics.isVisible"></mu-switch>
                  </mu-form-item>
                </mu-form>
                -->
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
    if (this.roomId === undefined) {
      communication.get("/api/room/get_roots", data => {
        this.room = data;
      });
    } else {
      communication.get("/api/room/get_tree/" + this.roomId, data => {
        this.room = data;
        this.initRoomConfig();
      });
    }
  },
  methods: {
    initRoomConfig() {
      // Init visibility
      let isVisibleStoredValue = localStorage.getItem(
        "is-visible-room-" + this.room.id
      );
      if (isVisibleStoredValue !== null) {
        this.form.isVisible = isVisibleStoredValue === "true" ? true : false;
      }
      communication.get("/api/eqlogic/room/" + this.room.id, data => {
        // Loop with push for reactivity
        data.forEach(eqLogic => {
          this.eqLogics.push(eqLogic);
        });
      });
    },
    changeVisibility() {
      localStorage.setItem(
        "is-visible-room-" + this.room.id,
        this.form.isVisible ? "true" : "false"
      );
    }
  }
};
</script>

<style>
</style>