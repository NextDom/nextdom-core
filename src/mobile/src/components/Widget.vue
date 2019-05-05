<template>
  <div class="widget">
    <h3>{{ eqlogic.name }}</h3>
    <div class="cmds" v-bind:key="cmd.id" v-for="cmd in cmds">
      <component
        v-bind:cmd="cmd"
        v-bind:is="getCmdComponent(cmd.type, cmd.subType, cmd.template, cmd.genericType)"
        v-on:executeAction="executeAction"
      ></component>
    </div>
  </div>
</template>

<script>
import templates from "@/libs/nextdomTemplates.js";
import communication from "@/libs/communication.js";

export default {
  name: "Widget",
  props: {
    eqlogic: Object,
    cmds: [Array]
  },
  // Inject all commands components
  components: Object.assign(templates.components, {}),
  methods: {
    /**
     * Execute an action linked to command
     * @param {int} cmdId Command id
     * @param {String} action Action to execute
     */
    executeAction(cmdId, action) {
      let cmdToCall = this.$store.getters.getAction({
        cmdId: cmdId,
        action: action
      });
      if (cmdToCall !== false) {
        communication.put("/api/cmd/exec/" + cmdToCall);
      }
    },
    /**
     * Get best component for command
     * @param {String} type Command type (info, action, etc.)
     * @param {String} subType Command subType (binary, numeric, other, etc.)
     * @param {String} template Render template name
     * @param {String} genericType Generic jeedom type
     *
     * @return Best component name
     */
    getCmdComponent(type, subType, cmdTemplate, genericType) {
      let componentName = "DefaultCmd";
      // Test for commands with template
      try {
        componentName = templates["cmds"][type][subType][cmdTemplate];
      } catch {
        componentName = undefined;
      }
      // Test generic type
      if (componentName === undefined) {
        try {
          componentName = templates["cmdsWithoutTemplate"][genericType];
        } catch {
          componentName = undefined;
        }
      }
      // No data
      if (componentName === undefined) {
        componentName = "DefaultCmd";
      }
      return componentName;
    }
  }
};
</script>

<style scoped lang="scss">
.widget {
  width: 25%;
  box-sizing: border-box;
  border: 1px solid black;
}
</style>
