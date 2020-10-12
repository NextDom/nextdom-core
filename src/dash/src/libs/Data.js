/**
 * Données de l'application
 */
export default {
    /**
     * Données par défaut d'un widget
     */
    widgetDefaultData: {
        id: -1,
        cmdId: -1,
        scenarioId: -1,
        pos: {
            top: 0,
            left: 0,
        },
        type: "InfoBinary",
        title: "Titre",
        state: true,
        unit: "°C",
        icon: "door",
        picture: "v1",
        action: {
            type: 'cmd',
            targetId: 1

        },
        style: {
            width: 200,
            height: 150,
            titleSize: 20,
            contentSize: 30
        }
    },
    iconGroups: {
        door: {
            on: 'fa-door-open',
            off: 'fa-door-closed'
        },
        lamp: {
            on: 'far fa-lightbulb',
            off: 'fa-lightbulb'
        },
        smiley: {
            on: 'fa-grin',
            off: 'fa-frown-open'
        },
        bell: {
            on: 'fa-bell',
            off: 'far fa-bell-slash'
        },
        check: {
            on: 'fa-check',
            off: 'fa-times'
        },
        bolt: {
            on: 'fa-bolt',
            off: 'fa-times'
        },
        eye: {
            on: 'fa-eye',
            off: 'fa-eye-slash'
        },
        running: {
            on: 'fa-running',
            off: 'fa-expand'
        },
        volume: {
            on: 'fa-volume-up',
            off: 'fa-volume-mute'
        },
        play: {
            on: 'fa-play',
            off: 'fa-stop'
        }
    }
}