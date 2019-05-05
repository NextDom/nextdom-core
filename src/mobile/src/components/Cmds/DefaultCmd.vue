<template>
  <div
    class="default-cmd cmd"
    style="color: red"
    v-if="parseInt(cmd.visible) === 1"
  >{{ cmd.name }} {{ cmd.value }} {{ cmd.id }} {{ cmd.visible }}</div>
</template>

<script>
export default {
  name: "DefaultCmd",
  props: {
    cmd: null
  }
};
</script>
