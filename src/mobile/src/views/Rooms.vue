<template>
  <div class="rooms">
    <h1>Rooms</h1>
    <template v-if="rootRoomsList">
      <template v-if="rootRoomsList.length > 1">
        <div class="roomslist">
          <Room v-for="room in rootRoomsList" v-bind:key="room.id" v-bind:room="room"></Room>
        </div>
      </template>
      <template v-else>
        <h2>{{ rootRoomsList[0].name }}</h2>
        <div class="roomslist">
          <Room
            v-for="child in rootRoomsList[0].children"
            v-bind:key="child.id"
            v-bind:room="child"
          ></Room>
        </div>
      </template>
    </template>
  </div>
</template>

<script>
import axios from "axios";
import Room from "@/components/Room.vue";

export default {
  name: "rooms",
  data: function() {
    return {
      rootRoomsList: null
    };
  },
  props: {
    roomId: undefined
  },
  components: {
    Room
  },
  mounted() {
    if (this.roomId === undefined) {
      axios.get("/api/room/get_roots").then(response => {
        this.rootRoomsList = response.data;
      });
    } else {
      axios.get("/api/room/get_tree/" + this.roomId).then(response => {
        this.rootRoomsList = [response.data];
      });
    }
  }
};
</script>

<style>
.rooms {
  margin: 0 1rem;
}

.roomslist {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: center;
  align-content: stretch;
}
</style>