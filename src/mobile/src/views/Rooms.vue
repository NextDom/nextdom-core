<!--
This file is part of NextDom Software.

NextDom is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

NextDom Software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.

@Support <https://www.nextdom.org>
@Email   <admin@nextdom.org>
@Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
-->
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
      <mu-container class="room-config" v-if="room.id">
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
                <mu-icon v-bind:data-id="eqLogic.id" v-bind:value="eqLogicsVisibility[eqLogic.id]"></mu-icon>
              </mu-list-item-action>
            </mu-list-item>
          </mu-list>
        </mu-expansion-panel>
      </mu-container>
    </template>
  </mu-container>
</template>

<script>
import RoomWidget from "@/components/RoomWidget.vue";
import Communication from "../libs/Communication.js";

/**
 * Navigate in rooms tree
 * @group Pages
 */
export default {
  name: "Rooms",
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
    // Current room Id
    roomId: {
      type: String,
      default: undefined
    }
  },
  components: {
    RoomWidget
  },
  computed: {
    /**
     * @vuese
     * Test if father link can be showed
     */
    showFatherLink: function() {
      if (this.room.id !== null) {
        return true;
      }
      return false;
    },
    /**
     * @vuese
     * Get father link
     */
    fatherLink: function() {
      if (this.room.father === undefined) {
        return "/rooms";
      } else {
        return "/rooms/" + this.room.father.id;
      }
    },
    /**
     * @vuese
     * Get dashboard link
     */
    viewLink: function() {
      return "/" + this.room.id;
    }
  },
  mounted() {
    /**
     * @vuese
     * Update tabs and URL
     * @arg New URL
     */
    this.$emit("setCurrentView", "/rooms");
    // Get data from default room
    if (this.roomId === undefined) {
      Communication.get("/api/room/get_roots", data => {
        this.room = data;
      });
    } else {
      // Get data from specific room
      Communication.get("/api/room/get_tree/" + this.roomId, data => {
        this.room = data;
        this.initRoomConfig();
      });
    }
  },
  methods: {
    /**
     * @vuese
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
      Communication.get("/api/eqlogic/room/" + this.room.id, data => {
        // Loop with push for reactivity
        data.forEach(eqLogic => {
          this.initEqLogicVisibility(eqLogic.id);
          this.eqLogics.push(eqLogic);
        });
      });
    },
    /**
     * @vuese
     * Change the visibility of the room in the summary
     */
    changeRoomVisibility() {
      localStorage.setItem(
        "is-visible-room-" + this.room.id,
        this.form.isVisible ? "true" : "false"
      );
    },
    /**
     * @vuese
     * Init eqLogic visibility in local storage and data
     * @arg eqLogicId Id of the eqLogic to init
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
     * @vuese
     * Method called on visibility update click
     * @arg eqLogicId Id of the eqLogic with a visibility to change
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

<style lang="scss" scoped>
@import "../assets/theme-color.scss";

.button-wrapper::after {
  content: "";
  clear: both;
  display: block;
}

h2 {
  margin: 0.5rem;
}

.room-config {
  padding-right: 0.2rem;
  padding-left: 0.2rem;
  margin-top: 0.5rem;
}

.mu-grid-tile-wrapper {
  padding: 0.2rem !important;
}

.mu-grid-tile-titlebar {
  height: 2.6rem;
}

.mu-grid-tile .icon > i {
  padding-top: 25%;
  font-size: 4rem;
  color: $textAlternate;
}

.mu-grid-tile {
  background-color: $textPrimary;
}
</style>
