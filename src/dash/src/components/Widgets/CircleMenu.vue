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
  <div class="circle-menu">
    <v-card-title v-if="widgetData.title !== ''" v-bind:style="{ fontSize: widgetData.style.titleSize + 'px' }">{{ widgetData.title }}</v-card-title>
    <v-card-text class="text-center">
      <v-content>
        <v-btn icon v-on:click="menuShowed = true">
          <img v-bind:style="{height: widgetData.style.contentSize + 'px'}" v-bind:src="'/data/pictures/' + widgetData.picture" />
        </v-btn>
      </v-content>
    </v-card-text>
    <v-overlay v-if="menuShowed">
      <div
        v-for="(menu, index) in widgetData.menus"
        v-bind:key="`circle-${index}`"
        v-bind:style="itemTransform(index)"
        class="item"
        v-on:click="itemClick(index)"
      >
        <div class="item-content" v-bind:class="animatedContent">
          <v-icon v-bind:style="iconTransform(index)">{{ menu.icon }}</v-icon>
        </div>
      </div>
      <div class="close-menu" v-on:click="menuShowed = false">
        <v-icon>mdi-close</v-icon>
      </div>
    </v-overlay>
  </div>
</template>

<script>
/**
 * Show eqLogic widget
 * @group Components
 */
export default {
  name: "CircleMenu",
  props: {
    widgetData: {}
  },
  data: () => ({
    menuShowed: false,
    cornerInDeg: 0.0
  }),
  mounted() {
    if (this.widgetData.menus !== undefined) {
      this.cornerInDeg = 360.0 / this.widgetData.menus.length;
    }
  },
  computed: {
    animatedContent() {
      if (this.menuShowed) {
        return "animated";
      }
      return "";
    }
  },
  methods: {
    itemTransform(index) {
      return "transform: rotateZ(" + this.cornerInDeg * index + "deg) ";
    },
    iconTransform(index) {
      return "transform: rotateZ(-" + this.cornerInDeg * index + "deg)";
    },
    itemClick(index) {
      this.$router.push({
        name: "dash",
        params: { dashId: parseInt(this.widgetData.menus[index].target) }
      });
    }
  }
};
</script>

<style>
.circle-menu .item,
.close-menu {
  position: absolute;
  top: calc(50% - 2.5rem);
  left: calc(50% - 2.5rem);
  transform: rotateZ(0);
  cursor: pointer;
}

.circle-menu .item-content,
.close-menu {
  border-radius: 2.5rem;
  width: 5rem;
  height: 5rem;
}

.circle-menu .item-content {
  transition: all 5s ease;
  background-color: red;
  transform: translateX(0);
}

.close-menu {
  background-color: grey;
}
.circle-menu .item-content.animated {
  animation: circleMenuAnimation 0.5s forwards;
}

.circle-menu .item i,
.close-menu i {
  margin: 1rem !important;
  font-size: 3rem !important;
  line-height: 3rem !important;
  width: 3rem !important;
  transition: all 5s ease;
  transform: rotateZ(0deg);
}

@keyframes circleMenuAnimation {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(10rem);
  }
}

@-moz-keyframes circleMenuAnimation {
  0% {
    -moz-transform: translateX(0);
  }
  100% {
    -moz-transform: translateX(10rem);
  }
}

@-webkit-keyframes circleMenuAnimation {
  0% {
    -webkit-transform: translateX(0);
  }
  100% {
    -webkit-transform: translateX(10rem);
  }
}

@-o-keyframes circleMenuAnimation {
  0% {
    -o-transform: translateX(0);
  }
  100% {
    -o-transform: translateX(10rem);
  }
}

@-ms-keyframes circleMenuAnimation {
  0% {
    -ms-transform: translateX(0);
  }
  100% {
    -ms-transform: translateX(10rem);
  }
}
</style>