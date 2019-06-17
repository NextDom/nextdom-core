# Widget

Show eqLogic widget

## Props

<!-- @vuese:Widget:props:start -->
|Name|Description|Type|Required|Default|
|---|---|---|---|---|
|eqlogic|eqLogic data|—|`false`|-|
|cmds|List of commands|`Array`|`false`|[]|

<!-- @vuese:Widget:props:end -->


## Events

<!-- @vuese:Widget:events:start -->
|Event Name|Description|Parameters|
|---|---|---|
|showError|Show error message|Error informations|

<!-- @vuese:Widget:events:end -->


## Methods

<!-- @vuese:Widget:methods:start -->
|Method|Description|Parameters|
|---|---|---|
|executeAction|Execute an action linked to command|cmdId Command id<br/>action Action to execute|
|executeCmd|Execute command|cmdId Command Id<br/>options Command options|
|setBatteryInfo|Set battery information on widget|batteryIcon string Material icon|
|getCmdComponent|Get component of the command|cmdId Id of the command|
|setRefreshCommand|Set refresh command if exists|cmdId Id of the command to refresh|

<!-- @vuese:Widget:methods:end -->


## Computed

<!-- @vuese:Widget:computed:start -->
|Computed|Description|
|---|---|
|isLargeWidget|Test if a large widget must be used|

<!-- @vuese:Widget:computed:end -->


