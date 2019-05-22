<template>
  <mu-container
    class="default-cmd cmd"
    style="color: red"
  >{{ cmd.name }} {{ cmd.value }} {{ cmd.id }} {{ cmd.visible }}</mu-container>
</template>

<script>
export default {
  name: "DefaultCmd",
  props: {
    cmd: null
  }
};
</script>
