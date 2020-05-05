<!--
Affichage d'une fenêtre de choix d'une commande à ajouter
-->
<template>
  <v-dialog v-model="showed">
    <v-tabs fixed-tabs background-color="indigo" dark v-model="tab">
      <v-tab>Informations</v-tab>
      <v-tab>Actions</v-tab>
      <v-tab>Plugins</v-tab>
    </v-tabs>
    <v-tabs-items v-model="tab">
      <v-tab-item v-for="(sampleType, index) in sample" v-bind:key="index">
        <v-slide-group v-model="selected" class="pa-4" show-arrows>
          <v-slide-item v-for="item in sampleType" v-bind:key="item.component" v-slot:default="{active, toggle}">
            <!--
              Aperçu d'un élément avec les données fournies par l'objet sample
            -->
            <v-hover v-slot:default="{ hover }">
              <v-card
                class="preview-card"
                v-bind:style="item.customSize !== 'undefined' ? item.customSize : {}"
                v-bind:elevation="hover ? 12 : 2"
                v-bind:class="{ 'on-hover': hover }"
                v-on:click="toggle"
              >
                <v-scale-transition class="transition">
                  <v-icon v-if="active" class="selection" color="green" size="48">fa-check-circle</v-icon>
                </v-scale-transition>
                <component v-bind:is="item.component" v-bind:widgetData="item.data" />
              </v-card>
            </v-hover>
          </v-slide-item>
        </v-slide-group>
        <!--
          Information sur l'objet sélectionné
        -->
        <v-expand-transition>
          <v-sheet v-if="selected != null" color="grey lighten-4" height="200" tile>
            <v-container class="fill-height">
              <v-row align="center" justify="center">
                <p>{{ sample[tab][selected].presentation }}</p>
              </v-row>
              <v-row align="center" justify="center">
                <v-btn color="primary" v-on:click="addItem(sample[tab][selected].component)">Ajouter</v-btn>
              </v-row>
            </v-container>
          </v-sheet>
        </v-expand-transition>
      </v-tab-item>
    </v-tabs-items>
  </v-dialog>
</template>

<script>
import WidgetTemplates from "@/libs/WidgetTemplates";

export default {
  name: "SelectItemToAddWizard",
  components: Object.assign(WidgetTemplates.components, {}),
  data: () => ({
    showed: false,
    tab: null,
    selected: null,
    sample: [
      [
        {
          component: "InfoBinary",
          data: {
            icon: "door",
            state: true,
            title: "Information",
            hideBorder: true,
            style: {
              titleSize: 20,
              contentSize: 40
            }
          },
          presentation:
            "Information à 2 états (On / Off, Ouvert / Fermé) représentée par des icônes"
        },
        {
          component: "InfoBinaryImg",
          data: {
            picture: "v1",
            state: true,
            title: "Information",
            hideBorder: true,
            style: {
              titleSize: 20,
              contentSize: 58,
              height: 100,
              width: 100
            }
          },
          presentation:
            "Information à 2 états (On / Off, Ouvert / Fermé) représentée par des images"
        },
        {
          component: "InfoNumeric",
          data: {
            title: "Information",
            hideBorder: true,
            state: 20,
            unit: "°C",
            icon: "fa-thermometer-three-quarters",
            style: {
              titleSize: 20,
              contentSize: 30
            }
          },
          presentation: "Information affichant une valeur numérique"
        },
        {
          component: "InfoNumericImg",
          data: {
            title: "Information",
            hideBorder: true,
            state: 20,
            percent: true,
            unit: "%",
            picture: { name: "shutter", values: [0, 20, 100] },
            style: {
              titleSize: 20,
              contentSize: 52
            }
          },
          presentation: "Valeur numérique représenté en image"
        }
      ],
      [
        {
          component: "CmdAction",
          data: {
            title: "Commande",
            hideBorder: true,
            icon: "fa-play",
            style: {
              titleSize: 20,
              contentSize: 30,
              height: 100,
              width: 100
            }
          },
          presentation: "Activer une commande directement"
        },
        {
          component: "ScenarioAction",
          data: {
            title: "Scenario",
            hideBorder: true,
            icon: "fa-play",
            state: true,
            style: {
              titleSize: 20,
              contentSize: 30
            }
          },
          presentation: "Contrôler un scénario (Etat, démarrer, arrêter)"
        },
        {
          component: "ScenarioActionImg",
          data: {
            title: "ScenarioImg",
            hideBorder: true,
            picture: "",
            state: true,
            style: {
              titleSize: 20,
              contentSize: 30
            }
          },
          presentation: "Contrôler un scénario (Etat, démarrer, arrêter)"
        },
        {
          component: "EqLogicAction",
          data: {
            title: "Objet avec état",
            hideBorder: true,
            icon: "lamp",
            state: true,
            style: {
              titleSize: 15,
              contentSize: 30
            }
          },
          presentation:
            "Allumer ou éteindre un objet avec un retour de son état"
        }
      ],
      [
        {
          component: "Camera",
          data: {
            title: "Camera",
            hideBorder: true,
            eqLogicId: -1,
            localApiKey: "",
            refreshInterval: 0,
            quality: true,
            style: {
              titleSize: 25,
              contentSize: 50
            },
            snapshot:
              "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/4RS4RXhpZgAASUkqAAgAAAAGABoBBQABAAAAVgAAABsBBQABAAAAXgAAACgBAwABAAAAAgAAADEBAgANAAAAZgAAADIBAgAUAAAAdAAAAGmHBAABAAAAiAAAAJoAAABIAAAAAQAAAEgAAAABAAAAR0lNUCAyLjEwLjE0AAAyMDIwOjA0OjI5IDA5OjQ1OjI0AAEAAaADAAEAAAABAAAAAAAAAAgAAAEEAAEAAAAAAQAAAQEEAAEAAACPAAAAAgEDAAMAAAAAAQAAAwEDAAEAAAAGAAAABgEDAAEAAAAGAAAAFQEDAAEAAAADAAAAAQIEAAEAAAAGAQAAAgIEAAEAAACpEwAAAAAAAAgACAAIAP/Y/+AAEEpGSUYAAQEAAAEAAQAA/9sAQwAIBgYHBgUIBwcHCQkICgwUDQwLCwwZEhMPFB0aHx4dGhwcICQuJyAiLCMcHCg3KSwwMTQ0NB8nOT04MjwuMzQy/9sAQwEJCQkMCwwYDQ0YMiEcITIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy/8AAEQgAjwEAAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8AxPL4pPLq55eKQxUAUmjrX09ANPDf7Rqk6YFa+nRhtOAI4yaAKzYOSPyqAx/Nj0zVlbZkyjcgnINIybJMHn0oAoSpyaz7sbIXPtxWuyYYg9TziqF1GGOxshSKAMZw23PtVd/7p71fmhwwA4UCqci8++TQBX7VPC+xDwTmoscU6JSR8xO3nFAD2fJ6EUw1KysDtJzVefchIUjPUZoA1fN8vwnqnP3lVfzNc1COc+9act2G0O4tCDvm2ngZxg5qjbRtgbhjnvQBIgO8gDO4Yq9HaHByC30qqJBHMCnUVbW/dVI2gk9D6UAI9ossoCnaAMEd6sR2MaLjAP1qrNeNImAgVj1YGqnnzcp5jbfTNABqyRrJtjx05xUcVnssFkKjexJJI5qK4+5nvV9LsHSoHfByzQqO+VCkn/x4frQBmxA+b+BrNuT++krVhBaY/Q1k3X+vkoAtqP8ARY/90VXere3FtF/uD+VVX60ARGkpTSUAPQcj61oOvy1Qj6j61psPlFAFbHBqpIvJq+q9ap3BEeWY8UAeueXSMmKt7KYycUAUXWtbTB/xLyfRj/SqDpWnpi/6BIoGT5nT6gf4UAQRQsMuwOWPGfSmTxOXzg4/Qdq1ni+TJFRvFkHigDH8jbuJ5b1rMu49zOcEkcYzgetbzrtKKRy5NULuNSdhGc9RQBhSgEZznjrWdOoHPpWrdJhfl4GMYFZVydqkUAVSRzU0GDHzjqcVmz3G3PNVbC9mkmnUuSsbAp7ZzmgDrLu2ihS2KS+YZIlkYFcbD6H8qzrlU3cAYpwuSYD5jFnYfeJzVbywx5Y0ANJFNJHrUgtwxIG4/hSixd8kEgDuaAIcio2kw4Xk0ssEkJw4wPWmbQcc9aAFL+9MLkHNIyc/eqE53bd2KAJLg/u6kKldJsT/AHridv8Ax2Mf0pksTtCuCCSM1PMGNppsCjLIkhcDszOev4AUAJZxlmZsd8CsK6/1sn1rsbEvYurIBuUc5XPWuQu1/wBLkT/ppj9aANt3gXRzCU/0jcpV8dFAOR/Ksdx61pXA7e1Z8g60AVzRQabmgCeLlh9a1WHyisuAfvFHvWs4+WgCFR1rCvbgTTFV+4vT3q5qN4UQwxnk/eP9KyD60AfQ2yo2TirRWmMvtQBSZK09EjyZ19NrAfmP61WKdfpV3RsJfMp43RkfqD/SgC9LFlSKrPE5l6jZjpitSZAq5P0qoCX2EwuoxjLAY/nQBkzowuFHl5UHIb065zVG8iw28fStm7gla4QDAh/i9c1lTRsFbeOF4FAHPXa4UAjGTXP37dfaunvM7ckdq5W/B5NAGBcudxHUnpV61tvs8GCPmPLH3o0+yN1dNMR8kZwPc1p3UPlRjPegCoHPkrzRvIINRBv3Q9s1csLQ3BMjAlFOMZ60ASWsu8+X0561faIBQoJAz+dPEaQLuiXaPQCnFRLgsx46UAVprU3ClGzjOfeqn9mmMfMS3oK1HXb1lx+FQmGaQ4Rxj/a70AZr2Kt0+U9Kgn0piCYySw9elaz21wP4QR60w714bp7UAZTBvK2BdsqjHNPtWYXdvGwyWBLGrc8O1g4/GmWgzdx+wJ/z+dAEuoNiVFBIJPasl7G3ncSqTnOcq2cmtm/hzmUE7lU4HrXP6Om2Kdj1zt/L/wDXQBZmhZ+RiqE0LrncpFaaMTuz0Wo0mWQ4wQfSgDEdTioC+GAPTPNbssEb/wAOD6iqd3ZedGQNpfnDHg0AZMF75chdyeDkAVstfCW2DJnLfpWDNZzwn5ozj1HIq/kR2qgnoKAKkzZYk1ATQ77j7UigsfagD6UK9aYVqwVpu3pQBD5eRU2nYj1CEt90nB+hp2zC5qJMpKrgcqQRQBvwQbt7tn72FUn7opGt3EbKu3BYnntWhEm5C3qB/LP9ahuYvMhkUNsPBDemP/1UAYy/vyzEgHG0gdQQazL0KVdcjIrYEUUiSM0YY7+49geKy7xVWN2x8pXPFAHM365yeoNcpqMbsfJjG6RyEUDua7K5j/c8+mazND0432oy3bDMcRKIT3Pc/lQA2x0gWlqkQ5Kjk+p71m63HskCei5rv008+lcNr+DqEy+h20AcxDmQsvQBiK6Wwt/Lt0AkI4yRWTa2uNzjuxroluIEX5IQOO9ACLaux3lh9AODUckTKOBge3Iq0t4npz27YqtNMzblwVVqAI1ZRncQW7UuQMNxn3qEYU4Zdy07zo8YCHH+9QA95fVR+dRN5bcHAH86bK4Zdu3aKhKDHGfxoAhvZkA2A/Niqay7JNynDIDVuSINww5qtLakK4Q4ZhjnpQBDPqc02mTSsFVxwCvrUOmrs0wHuzE/5/Kq18GttKWJxh2fkVehQpYwoOy5NAAOLeRvWmW8SBDKHBY8bR2qwu1Y41cgA+taF3p8AvIre0VVaVBuYHIJPegDL/smSeKS5trkqRyyt0qs7BSAT1q4pvNNV7SRTsfOGxkH6GpLNrdleKTaWJ+6w60AZpwcg4NVp7KOdccr9Kn2pG0xQEJuOBnpiqwuyv3hkZxx1oAzZ9PeLkAkeoqAAgcV0B5GfXtVWW1jc5xhvUUAfQRFAGaeRQBzQAFeKiKYarRGRUbLQB0enOJdMhYdQNjfUf8A1sVTvBJPCY04YuFbHpmnaE+UngPqHH8j/Sp5gEl3r0DYagCo8IjAUepJrGv0YH/Zx0roJV/eE9s1k36ZQ0Achqe5o1hiGZZDtUV1Wl6MtnYxQIPujk46k9TWd4f09tQ1uS5Zcw2w492PT+prvYbLPB6UAYps9iMSuAFrx3VT5t/M395ia951aL7No13ORjbEx/SvCL1P9IbjtQBXs48wnnGGJP51b2YGcZFO05A0bezEGrphIxgDHrQBXjjV19/Q00Iykrgsv90irZg2ncB+NOEQkHHD/wA6AKRt45BlTtPoelQvC8X3lDLWkY1ORIMMO46ineSNnK7h6qcH8qAMjYhXgkfWkMZB5XI9RWt9ggmyYpNrdw1RSadLH/rD8nqtAGSyAscfmajaPitGW08tuASh6EdqgNuxGV+YD04oAy57ZJEKSx7lPY1E5aIgrFvUcden4VpOpBAIP0NQSRqeVO32oASOwhvbJ284JcI3yKehGO9Uo75tPaKZtnmDIAJ4zin3CyBNi/L7isjUZkMgicZAGT7UAbNzqK30S7QV28sD61zNy+Z3IZuT2NXQwtdKLZPIOMn16Vl2f7+7RccD5jn2oA1Jf3VoE74AqkiGSaNe2cmpdQnKyqo7dvXNLYlZCzjoOKAC+kZdoU4PXio4J3dtjjJx1pt0RJIcE5HApbRT8zn6UAfRhFJTjTeKAJB601hSg8fhTWNAFrS5vJ1CPJwH+Q/j0/WtmSJwHAAIJOQTiuYzhsjjHSuqjn8+zSbuy5OPXv8ArQBRnnWK38xznBxhepPtWRq8myBgOrcVoTxsQDnCplz+RqnpVsur6xFAoYxW7fvA3XA/xoA6Dw/pAstLiQriR/nf6muggt/lxjrzUyQjuKsrHjpxQBzHjVvJ8LzgdZCq/mc/0rwy9j/fMa9s+Ij40e3iH8UufyH/ANevG7xP3h+tAEOjxgySg4P7zP6VsmDfnHb8qpaRCVeZ8Z/eD+QrdMG0BgOO9AGUkTEsCnI6qe9SLHEw4Ug/yrSeEEBx1HpUbWchTzQQW6kDvQBSktCRlX+btmolyrbWwr+h4z/StSJ9qkqg3d89qk2JMmJY1z/OgDJeANyyMreuP6iol81CU3qR2VuQa0JIoEbaflx0Ib+lQSRs3ABkH+0v9aAKT4Hyuhjz+RqI28G7BYxv/eB4NW2gnSPlGQd+QRVaWymaPzQQT/dFAFea0mBwcSL6qBWbPBGDjkN71cEzq2zoO4Y4okVTww4PrQBkSwOnbcvqOlZd5Yx3Iz0Yd63pYihyjkexqjMMsAyYz/EDQBzeqkiNIOnc0zS4giySnvwK2bm0EyFWww9e4rLuVNpZlFzjpmgCg8izXPmZyMng1ortt7QkADPOMVlW8Xm3KL2zk49KuajOBtiDYJ5oAhdgnTqetW/9TbjscVRtkaWdQxyF5NSX8xDBB25NAH0iTTScUE5pjGgB4btQx4qAPg9aUyZGKAHE9619InLxPbk/cO8e4/8A11iFuKfZ3X2a8jkzx0b6GgDdu3CW7g9+PzrZ8IaesVpLeFAHmbAOOw4rnblHuLyOBDlpMKK9FtbdLW1igjGFjUKPwoAeowop9NpR0oA4X4hyZ+yxdgpNeWXS/vDXpfj2QNqSJ/diH8684uRlz9aAJ9LX5JSR0YH+Vb/l7VAzlT09axtKXPmr/npXSRoPLQH7uOlAGasckMhV8eW3f0qwkZU7lztPTFWJY+OQMGoYfMhYo2GGflPtQAyS0glO5C0b9+ODVKSGWB9svMZ6MOn41qSyRouXViDwcDgUhB27ThkPQ+1AGascUJ3Hbz3NRS3DKenB6HFSXdu0MgZctHngHoKfFMsi7Cgz6GgCiJVlOXbgdgM0xJfLmbI/dH17VYk3xyMqKCT29KqTZOexoAkntrecHIU5rCvrJoXCxlmU/wANXjIysFJ49fSoxetyroTjvigDClLA7fut6NVSUsvRTg/iK3rl7abh0/MVlz2eCDDJ8voaAMubIwyfjVeUB1IZQ1XZoZQCCB9RVVl9aAMsWq2zPImcH17VjTOZpnc9D/Kulk5FZc9khkDpwufmFADbVRDbGR+M/MfpVMkSyl2PHXrVm7kygjHQ9arQxZfb/CeTQB9KFu1RluKRjULPwaAEZ8GkV6hduabvxmgC1vqGWQBS2elRmTiql7PiLGetAHbeBM6pf+dId32Vec+vQf59q9Irkfh1pv2Lw0tw4xLduZCf9nov+P411pNACUopKUUAeYeNpd+tzj+6qr+lcNNy5+tdh4skDazeH/poR+VcbOeT9aAL+k5Eze5FdRHl7bYgUuvY1yNgCzuobaflOfxrp7d/LkCsxwe5GKALEVuzRlZBh/SoGjOCjcMp71e3kAcKcdwaSWNLgZztkHQigCgQSOFJX1P8qrvvfAjcjnkcVZWZo5SjjaePmI4NLLbeb86YDeq0AVzgx+XJ8w9Say5Q0FwuxSRnnFW55zbnEykHsQODUMpDgMvIoAbcZEit+BqhcOiOd2AT61o5S4iwSNw71QuIgc7lBoAoScnjFQSsSDjrUsx8tunyjtVUyLuyCCrd/SgCjOH3ZDEj0qk9wyNtANaEycZqnMmRg/nQBCZy4/pUEhBBBUUskTIdwNVzKzE5/A0AQSKAevJ7VA4A7VZbk8jmoJMigDMubYMS6HnuPWokwi+/er74zUDW4dsg49aAPe2OKryNg/hSpLuGPaopDQBGW5phbn8KQmmMeKAFMmKoPuu9Qitk6u6oAPUmrDvhSfSrXgS0/tHxra7uUhLTMD7dP1IoA9ysrdbOwgt0GFijCD8BU1BNJmgAJpRTaUHFAHjfiKbfqt2c9Zn/AJ1zE7YY/WtrWZSb6U+rsf1rnrh/mNAFqznEd6iHjzFI/HrXYtGtzZo+cNtHzDrXm9y7rPbOh+ZZAa73TZg+njrgUAWrefYPLmOGHfsamLkH5TmqMwDDuCehFVrdpFZi8hODz7igDYLrKpST9arNHNb8xPuX0phlwQecfWnLPz8vSgBoniuQyy/Ke4NUp7JgS1tKCP7rVZnEcnUc1QkYxtgZHvmgCuxmhJLwN9VNI0iyLkIxqRrpuQxyKgmSKX5xlW9QcUAUZ035Gz9ayniaHIVODWhdrLHJvdyU9V4qCVtwyM0AZ0rM0bEcEDpVeOR3T5gCR+tWJmVZNuDkiqjnZnacGgAk+ZflNZ0q7WOehq4zZAYd+tQzQrIn05oAqEcetQuB2qaRNveq7MDmgCF1BOKFXJwOlLnI+tXLa2yAx6UAf//ZAP/iArBJQ0NfUFJPRklMRQABAQAAAqBsY21zBDAAAG1udHJSR0IgWFlaIAfkAAQAHQAHACkAHmFjc3BBUFBMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD21gABAAAAANMtbGNtcwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADWRlc2MAAAEgAAAAQGNwcnQAAAFgAAAANnd0cHQAAAGYAAAAFGNoYWQAAAGsAAAALHJYWVoAAAHYAAAAFGJYWVoAAAHsAAAAFGdYWVoAAAIAAAAAFHJUUkMAAAIUAAAAIGdUUkMAAAIUAAAAIGJUUkMAAAIUAAAAIGNocm0AAAI0AAAAJGRtbmQAAAJYAAAAJGRtZGQAAAJ8AAAAJG1sdWMAAAAAAAAAAQAAAAxlblVTAAAAJAAAABwARwBJAE0AUAAgAGIAdQBpAGwAdAAtAGkAbgAgAHMAUgBHAEJtbHVjAAAAAAAAAAEAAAAMZW5VUwAAABoAAAAcAFAAdQBiAGwAaQBjACAARABvAG0AYQBpAG4AAFhZWiAAAAAAAAD21gABAAAAANMtc2YzMgAAAAAAAQxCAAAF3v//8yUAAAeTAAD9kP//+6H///2iAAAD3AAAwG5YWVogAAAAAAAAb6AAADj1AAADkFhZWiAAAAAAAAAknwAAD4QAALbEWFlaIAAAAAAAAGKXAAC3hwAAGNlwYXJhAAAAAAADAAAAAmZmAADypwAADVkAABPQAAAKW2Nocm0AAAAAAAMAAAAAo9cAAFR8AABMzQAAmZoAACZnAAAPXG1sdWMAAAAAAAAAAQAAAAxlblVTAAAACAAAABwARwBJAE0AUG1sdWMAAAAAAAAAAQAAAAxlblVTAAAACAAAABwAcwBSAEcAQv/bAEMAGxIUFxQRGxcWFx4cGyAoQisoJSUoUTo9MEJgVWVkX1VdW2p4mYFqcZBzW12FtYaQnqOrratngLzJuqbHmairpP/bAEMBHB4eKCMoTisrTqRuXW6kpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpKSkpP/CABEIAKgBLAMBEQACEQEDEQH/xAAZAAADAQEBAAAAAAAAAAAAAAAAAQIDBAX/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAHMBDNBCESQIQDAAAgZQAMyKJJGAhAMoQEnaAAWIBEkCEIBgIBDKAkCSyCBgIQDLEIR2gAjUkCREkkiGMCRDEAABJQiCyRCAZYgMj0hAI1EIQiCCRAaECEACAAEUUYlCEIBlgZknpiARqAiREEGZAjQBgMkQhDAoowLJEIBmhmQB6ggEbASIkgzMiCwADQAEIBCGMkBEkiEaECEesIBGoxEkkGRkBRIGgwAQCAkYzIoAESZlEiGeqACLLEIkzMjI2IMizQBAAAIgAIKEMQCEQID1gARRqIgkzMwNTAyNShkiACQJJEMZRBQECGSI9QBiGbEkkmZmblHEIoBiAQAAhEFABZmQMgoQz0wAANiQJMhm4jzwKAYhiEAgEBIxGYyCiSiCz0wGAjUAJMDqLMzzwLABiGACJEIQiCAEAwEUemAwEWWSSB0FGBwCNQAAEMQCARJJJJkWQWSMD1RAMQG5mQdJYzlOMDUYgAYiRASICREkEklEFkHrgAABoAjqADkOUZqIYxEgMQgEZkiJEIgkBiPWAQxAUUamoAcZzjNRklAIkBCEICSCBAIyKIGesIBAAFnYAAcJiM2GIQABAyREiJEBJIhECGeoAhAAjU7AADzzMZqUMkBAIkCBCJJJACRCJA9QQhABJ2G4AI4DMDUsYEjIJAZBJIEiJEIQgA9EQgEBB6gwAk4DMR0AUACEBAgIJESAiRAIQzvAkAEM9IAAg4DMk2NSBjGIkQEkASSIBEiACj/xAAhEAABAwQDAQEBAAAAAAAAAAABABARAiAwMQMhMkBBIv/aAAgBAQABBQL4eT1ZFwzHLV6aWlv0uMxsPwnYakTmOGVPWI+gEUNXnEbRbFobZRX5cbxpjYcMNChhs4JwDTFyiqRlCKptIxDVhRWzCqYY56psgh4UX02FiqAoVW0MR0fI0oRRMqLIuG0WLHswxQyEPPQ0pzFFUBjooZh0ipRYscFOnKAbk8lDB1eUFUqUUEcFO3oHbc2ihuyL4u/HGIqkQG5mG/gIRY4hpUian5fSG7IvhG8oI4BsrjHT8npBDKR8O7atobHyHFw+rCwQwD5SuEfy50WC/Ps/R0Hq8lxgj5eMTXZV5LHVHz//xAAUEQEAAAAAAAAAAAAAAAAAAACQ/9oACAEDAQE/AUe//8QAFBEBAAAAAAAAAAAAAAAAAAAAkP/aAAgBAgEBPwFHv//EABwQAAICAgMAAAAAAAAAAAAAABFQAUAgYHCAgf/aAAgBAQAGPwKj5t447KUJilCY9DJQxjNv/8QAIxAAAgIDAAICAwEBAAAAAAAAAAEQESAhMUFRMGFxgZGxof/aAAgBAQABPyGV0cuPOCldOEqGvQhQxqhD6cfA8V1CYMawUp7N7CFpiQtFhts5Ah9OPgeK7BDwOVDis2f4L/IPpUr4GPeKFFQ1DHCQ2TwQ2X8DOxd5o6cDwUveXZfIMYxs8xTRQaqXCirX7OHbFxiobxnwitiPwMY5JUsFFQooaFXs7HRplFDUpish/BM/Qj8DWxyeQahbYlUUfsopm57Ynk6Z5LiiwaaFwefb6EjwdHI1QhSL5isLl9LrY+xwzyVbK8WeRyWHTPqHzRW2IPpqf1Q7CWxdQxuP3hRpIUK6u+GhQKNCLhisdEhqRXYMSoIUdvJWdy0Jaor0JtCmo0ldCdHiFwvPqsrwOGh39ihadnUKKiorNr2MGTd0WsakLblxCyb+B8sYxbdChV6KNHOjplFR2fyUxXgaooqGaI2Y26PY6Ohvgb+ijiG9WXMKjisA1g52Gj8ONODKGVQ+i1Bck3ktHgaG3RWqekMUVqKGqlpej9n6NjTUtDiyC2xtRxF5vdIpPrEcS4M6o+h9Mo4XC2NRGsWkxKhrZ18J6/IYot7w6DOziHrZ0o70doQ0jQyy0xDGmoctULSH34b1EqVYdvyPINDOoao6sKGXFD+Q2JbellZ0MUirih/cOTuWP52KL+8OkGdISCcPZtdOjTXC/ZwYxl3g8qze6C0r1h/wQZdbOSqei1Nei/Y0mNi2vEMajzDzvFn4lvLdDgbUP6FFjGWWV6Hd7GPBrGj/2gAMAwEAAgADAAAAEAIIAJJBIJJBJAJABIJBAAIAJAJIIAIIABIBIAIJBJBIJJIBBJAAIABIJBBAJJAJABBAJABAJBJIABAIJIBJJBABIIABBIBABAAIIJAJIJJIBAIBABBBBAAAJBBIIAJJAAAAJIIBIIAAABAJJBIBAABIBABIAJAABIAIIBBIJAIIJBBBJBAAIIJABIIIIJJAJBBAAAAIIJBBBJBBJIIAIABAABIBAABIAIJBBJAIBAABJAJJAIAJJAJABIJAAJJJBBIJBAIBIJJJJABAJJIIBJAIBIIBBIIIIAJJABBAIAAIIJAIIBBJABJIBBJIBIJJAIBBIAJIJJIBJJABBJJJBJIABJBJJAJAABIBAABBJAJAIAJBBJIAABAIAIBAIIIABABBJBJBABJBIBIBP//EABQRAQAAAAAAAAAAAAAAAAAAAJD/2gAIAQMBAT8QR7//xAAUEQEAAAAAAAAAAAAAAAAAAACQ/9oACAECAQE/EEe//8QAJhABAAICAgICAwEBAAMAAAAAAQARITFBURBhcYEgkaGxwdHh8P/aAAgBAQABPxCpUqDH5mEZWoIkcqMfCoZZ41R2HQhDkh3loEcrE0zapWAJtN82fMfwJx4EqVKlQfujVZsgWFSsyssxJQrHVxPFnE+o4mpUdoyUF/RBfgciLluyVM2jBoeLjKv9Tb6m+b/mMfJOJzB4qVKgw+ZgLLOWbXmVb6IMUFxNwRN+BjETEDUrNxC3WfFS5cXxvNH/AN4QZWb4lDC2/ojHyTiczEtitcqVEmmWNTCJ3Kma4iLjE28FqOKHI5mPLYufc4eoxcuXHLLlwS5vMR6A/RLgI5W+FcNYm0fO04nMtUaPFSokSVa/USJDWHZ/sFEE5R+ItvhYqMS+ZvU+UwU7m/x4fCi6iWNl5XUcstT+qYfBGPlFk4leH35qVKlQfoiRzPM/8BAJ3Bma+Da0bZgDiGvA1CpaSi5nuVNR7TBoYp2RsLal82uJrIOPD6iKqL4iGyIxaimeYltZjzF8sqJMydXMSbTIjKtjmMUNTd8cbiNvnUqIc/MCAcwRVZ7l01cp3mIG4s6Yn7lCVcSse5sQLeVQ7OJbDRqbIh9RBirm0JiYrfFSpUqJK5cWvEwW5WUhKmw9WMFYg3DHB538QQoMEFIQcpHtt6lNVuXDJ+o3cNXz4fl+40xKj4QzjMuYKgze4ZhThuNzR7io27eQTDUWAl+KlRm0eSBiXHS0mSCnMyZVhjAsRFGdPiHgMlg53V8Qo1mIrZLDEC+amOka7uZjqo9YFPmKgdsND3OTLU0f6iaPEEAKqBDkeyWzbeZTwD6Zc9+KhEjse5VFdS/TfEKfLH9YA33Ble4oPLmGABRqOLFbe4MsNQGZ5zKvT+4o4ge5XZKiYlRIpGCcSgdCWbBb6l5fe/UsGDm0jNGINQftmvrFxBRgXeH8CPga+xmIcu7m8GI8mXW7wPiE+rhgygyy0sM2dM/RmTZiJc1UpJh3H1rwkSA4hnZwVFQp3KMsPCTcbZgEWKlWhMKTuCjyR8XM7TL0MFt+LHsWGY0KhjPvsQZTW6qZEBUSmUfKZJXWGXWBZOI1GmRiIM5JRMMURe4DiBWiKkeMzCHECqo7r9S6rNTJUq0/AjGVU6SwYaiiW6iNxXqX6awfMP7gRfKbgyw5/Eq8RDWpVF7JQ4qITEzh/wCkprQnpnJkma2HUwuqY8xT2RXDZ8whsjbTB2SqUmaWf90G25hbuLdvcyVmT8koTZFGxrcZK7f+wBC7W52BtgSorPohzMn4ha1QOGK0RfjBowfcMmSfCRA19IivL2RNM/czm4LdRgcYlqyXEvWICzhjoqCghFO5mHPxFX+L4rOkcH9hOTCWBnyrp0BDDl8Q0GF2ZYbKqBw5IsyrPctHRM3MawDxTl3GCncZxBd2RemL3iNMVuoRTLbf1PgYL9CPiBjPm5cWXK04wlAeyWL2v5+Cv5/H/GDLqVR8zgfcKE9MRAUJq8PMA7bhtR4c+H9x2TwGkbI11U28JL1modnyxLaAuvKy5cuLDENuPuEY0Ffgrf2nKf5QXcumsMSn0yqZZyjEBncxmibcQYce4xw3EXcQdy/zFGIYKjEgQ9QhgrxcWXLlyguIx039/gtFzJXtnKf4wEElzZuNFcx6cMLfPUFYGI1xMyCirjhYg6sjHfgL0zJLuMYxiU2S/CxfFy5xTL9u/r8FSdDHOXhVBD7hpvMupQ53OhZChuZwX6gnSmXtuUS+YJg3K5G45iRIK14Yxm5TqMdRfFy5YhtaIJegH4Kk9oszlHB0SO0RyPsQQ6meII4Y1yoZ1PyfcDpGWM09xV4gvgiDiZ0xs2i1uCOMeGMqB3C0Y+GKhlWdZPr8XXwRQpmj1Lxe0zHcvtR1tl5npKO4KlzmZbiLtUyqyx6ilTMa3LzTPUszEqXOYFsKE//Z"
          },
          customSize: {
            height: "10rem",
            width: "18.8rem"
          },
          presentation: "Affichage d'une caméra"
        }
      ]
    ]
  }),
  mounted() {
    this.$eventBus.$on("showAddItemWizard", () => {
      this.showed = true;
    });
  },
  watch: {
    tab: function() {
      this.selected = null;
    }
  },
  methods: {
    addItem(itemType) {
      this.$eventBus.$emit("WizardAddItem", itemType);
      this.showed = false;
    }
  }
};
</script>

<style scoped>
.v-card {
  margin: 1rem;
}

.v-card .transition {
  position: absolute;
}
i.selection {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.preview-card {
  width: 10rem;
  height: 8rem;
}
</style>