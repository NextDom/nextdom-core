# Rooms

Navigate in rooms tree

## Props

<!-- @vuese:Rooms:props:start -->
|Name|Description|Type|Required|Default|
|---|---|---|---|---|
|roomId|Current room Id|—|`false`|undefined|

<!-- @vuese:Rooms:props:end -->


## Events

<!-- @vuese:Rooms:events:start -->
|Event Name|Description|Parameters|
|---|---|---|
|setCurrentView|Update tabs and URL|New URL|

<!-- @vuese:Rooms:events:end -->


## Methods

<!-- @vuese:Rooms:methods:start -->
|Method|Description|Parameters|
|---|---|---|
|initRoomConfig|Init visibility and get data|-|
|changeRoomVisibility|Change the visibility of the room in the summary|-|
|initEqLogicVisibility|Init eqLogic visibility in local storage and data|eqLogicId Id of the eqLogic to init|
|changeEqLogicVisibility|Method called on visibility update click|eqLogicId Id of the eqLogic with a visibility to change|

<!-- @vuese:Rooms:methods:end -->


## Computed

<!-- @vuese:Rooms:computed:start -->
|Computed|Description|
|---|---|
|showFatherLink|Test if father link can be showed|
|fatherLink|Get father link|
|viewLink|Get dashboard link|

<!-- @vuese:Rooms:computed:end -->


