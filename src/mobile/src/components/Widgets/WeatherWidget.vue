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
  <div v-packery-item class="packery-item large">
    <div class="widget-title">
      <span class="title">{{ eqlogic.name }}</span>
      <span class="actions pull-right">
        <mu-button
          class="pull-right"
          slot="action"
          icon
          v-on:click="executeCmd(refreshCmdId)"
          v-if="refreshCmdId"
        >
          <mu-icon value="refresh"></mu-icon>
        </mu-button>
      </span>
    </div>
    <div v-if="canShowToday" class="today">
      <i v-bind:class="getIconFromCondition(conditionsId[0].state)"></i>
      <div>{{ temperature.current.state }} {{ temperature.current.unite }}</div>
    </div>
    <table class="forecast" v-if="conditionsId.length > 1">
      <tr>
        <td></td>
        <td v-for="(conditionId, index) in conditionsId" v-bind:key="index">
          <i v-if="index > 0" class="icon" v-bind:class="getIconFromCondition(conditionId.state)"></i>
        </td>
      </tr>
      <tr>
        <td>{{ $t('min') }}</td>
        <td v-for="(conditionId, index) in conditionsId" v-bind:key="index">
          <div
            v-if="index > 0"
          >{{ temperature.min[index].state }} {{ temperature.min[index].unite }}</div>
        </td>
      </tr>
      <tr>
        <td>{{ $t('max') }}</td>
        <td v-for="(conditionId, index) in conditionsId" v-bind:key="index">
          <div
            v-if="index > 0"
          >{{ temperature.max[index].state }} {{ temperature.min[index].unite }}</div>
        </td>
      </tr>
    </table>
  </div>
</template>

<script>
import BaseWidget from "./BaseWidget";

/**
 * Show eqLogic widget
 * @group Components
 */
export default {
  name: "WeatherWidget",
  extends: BaseWidget,
  data: function() {
    return {
      conditions: [],
      conditionsId: [],
      temperature: {
        current: null,
        max: [],
        min: []
      },
      wind: {
        direction: null,
        speed: null
      },
      pressure: null,
      sunset: null,
      sunrise: null
    };
  },
  computed: {
    canShowToday() {
      return (
        this.conditions[0] !== null &&
        this.conditionsId[0] !== null &&
        this.temperature.current !== null
      );
    }
  },
  created() {
    // Sort all commands for easy usage
    for (let cmdIndex = 0; cmdIndex < this.dataCmds.length; ++cmdIndex) {
      const genericType = this.dataCmds[cmdIndex].genericType;
      let showed = true;
      if (genericType !== null) {
        if (genericType.startsWith("WEATHER_CONDITION_ID")) {
          this.conditionsId[
            this.extractDataId(genericType, "WEATHER_CONDITION_ID")
          ] = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_CONDITION")) {
          this.conditions[
            this.extractDataId(genericType, "WEATHER_CONDITION")
          ] = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_TEMPERATURE_MAX")) {
          this.temperature.max[
            this.extractDataId(genericType, "WEATHER_TEMPERATURE_MAX")
          ] = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_TEMPERATURE_MIN")) {
          this.temperature.min[
            this.extractDataId(genericType, "WEATHER_TEMPERATURE_MIN")
          ] = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_TEMPERATURE")) {
          this.temperature.current = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_HUMIDITY")) {
          this.humidity = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_SUNSET")) {
          this.sunset = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_SUNRISE")) {
          this.sunrise = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_PRESSURE")) {
          this.pressure = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_WIND_SPEED")) {
          this.wind.speed = this.dataCmds[cmdIndex];
        } else if (genericType.startsWith("WEATHER_WIND_DIRECTION")) {
          this.wind.direction = this.dataCmds[cmdIndex];
        } else {
          showed = false;
          console.warn("Not supported " + this.dataCmds[cmdIndex].genericType);
        }
        // Initialize update on change
        if (showed) {
          this.$store.commit("addShowedCmd", { cmd: this.dataCmds[cmdIndex] });
        }
      }
    }
  },
  methods: {
    /**
     * @vuese
     * Extract id from generic type field
     */
    extractDataId: function(genericType, code) {
      const extractedData = genericType.match(new RegExp(code + "_(.*)"));
      if (extractedData === null) {
        return 0;
      } else {
        if (extractedData.length > 1) {
          return parseInt(extractedData[1]);
        }
      }
    },
    /**
     * @vuese
     * Get icon depends of conditionId
     */
    getIconFromCondition(conditionId) {
      // Condition Id code : https://openweathermap.org/weather-conditions
      if (conditionId < 300) {
        return "icon meteo-orage";
      }
      if (conditionId < 400) {
        return "icon meteo-brouillard";
      }
      if (conditionId < 600) {
        return "icon meteo-pluie";
      }
      if (conditionId < 700) {
        return "icon meteo-neige";
      }
      if (conditionId < 800) {
        return "icon meteo-brouillard";
      }
      if (this.sunrise !== null && this.sunset !== null) {
        const currentDate = new Date();
        const currentFormatedHour =
          currentDate.getHours().toString() +
          currentDate.getMinutes().toString();
        if (
          currentFormatedHour > this.sunrise.state &&
          currentFormatedHour < this.sunset
        ) {
          if (conditionId === 800) {
            return "icon meteo-soleil";
          } else {
            return "icon meteo-nuageux";
          }
        } else {
          if (conditionId === 800) {
            return "fa fa-moon";
          } else {
            return "icon meteo-nuit-nuage";
          }
        }
      } else {
        if (conditionId === 800) {
          return "icon meteo-soleil";
        } else {
          return "icon meteo-nuageux";
        }
      }
    }
  }
};
</script>
<style lang="scss" scoped>
.today {
  text-align: center;
  margin-top: 0.5rem;

  i {
    font-size: 3rem;
  }
}
.forecast {
  margin-left: auto;
  margin-right: auto;
  margin-top: 1rem;
  td {
    text-align: center;
  }
}
</style>
