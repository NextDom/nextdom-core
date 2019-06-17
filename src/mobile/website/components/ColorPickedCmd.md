# ColorPickedCmd

Show color picker in a popup

## Props

<!-- @vuese:ColorPickedCmd:props:start -->
|Name|Description|Type|Required|Default|
|---|---|---|---|---|
|cmd|Command object|—|`false`|-|

<!-- @vuese:ColorPickedCmd:props:end -->


## Events

<!-- @vuese:ColorPickedCmd:events:start -->
|Event Name|Description|Parameters|
|---|---|---|
|executeCmd|Send event to Widget component that execute command on NextDom|Id of the command to execute.<br/> Json object with the attribut color that contains the new color|

<!-- @vuese:ColorPickedCmd:events:end -->


## Methods

<!-- @vuese:ColorPickedCmd:methods:start -->
|Method|Description|Parameters|
|---|---|---|
|onColorChange|Send the new color to NextDom|String Hexadecimal code of the new color (with #)|
|openColorChoice|Open the color choice popup|-|
|closeColorChoice|Close the color choice popup|-|

<!-- @vuese:ColorPickedCmd:methods:end -->


