function notifyError(text) {
    notify('{{Erreur}}', text, 'error');
}

function notify(_title, _text, _class_name) {
    if (typeof notify_status != 'undefined' && isset(notify_status) && notify_status == 1) {
        var _backgroundColor = "";
        var _icon = "";

        if (_title == "") {
            _title = "Core";
        }
        if (_text == "") {
            _text = "Erreur inconnue";
        }
        if (_class_name == "success") {
            _backgroundColor = '#00a65a';
            _icon = 'far fa-check-circle fa-3x';
        } else if (_class_name == "warning") {
            _backgroundColor = '#f39c12';
            _icon = 'fas fa-exclamation-triangle fa-3x';
        } else if (_class_name == "error") {
            _backgroundColor = '#dd4b39';
            _icon = 'fas fa-times fa-3x';
        } else {
            _backgroundColor = '#33B8CC';
            _icon = 'fas fa-info fa-3x';
        }

        iziToast.show({
            id: null,
            class: '',
            title: _title,
            titleColor: 'white',
            titleSize: '1.5em',
            titleLineHeight: '30px',
            message: _text,
            messageColor: 'white',
            messageSize: '',
            messageLineHeight: '',
            theme: 'dark', // dark
            iconText: '',
            backgroundColor: _backgroundColor,
            icon: _icon,
            iconColor: 'white',
            iconUrl: null,
            image: '',
            imageWidth: 50,
            maxWidth: jQuery(window).width() - 500,
            zindex: null,
            layout: 2,
            balloon: false,
            close: true,
            closeOnEscape: false,
            closeOnClick: false,
            displayMode: 0, // once, replace
            position: notify_position, // bottomRight, bottomLeft, topRight, topLeft, topCenter, bottomCenter, center
            target: '',
            targetFirst: true,
            timeout: notify_timeout * 1000,
            rtl: false,
            animateInside: true,
            drag: true,
            pauseOnHover: true,
            resetOnHover: false,
            progressBar: true,
            progressBarColor: '',
            progressBarEasing: 'linear',
            overlay: false,
            overlayClose: false,
            overlayColor: 'rgba(0, 0, 0, 0.6)',
            transitionIn: 'fadeInUp',
            transitionOut: 'fadeOut',
            transitionInMobile: 'fadeInUp',
            transitionOutMobile: 'fadeOutDown',
            buttons: {},
            inputs: {},
            onOpening: function () {
            },
            onOpened: function () {
            },
            onClosing: function () {
            },
            onClosed: function () {
            }
        });
    }
}

