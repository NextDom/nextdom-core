#!/bin/bash

function gen_css {
	echo " >>> Generation du CSS"
	sass assets/css/nextdom.scss public/css/nextdom.css --style compressed
	sass assets/css/nextdom.mob.scss public/css/nextdom.mob.css --style compressed
	sass assets/css/firstUse.scss public/css/firstUse.css --style compressed
	# Remplacement des chemins
	# TODO: A optimiser
	# sed ne prend pas en charge le non-greey
	sed -i 's/[\"]Roboto-Light\.ttf/"\/3rdparty\/roboto\/Roboto-Light\.ttf/g' public/css/nextdom.css
	sed -i 's/[\"]Roboto-LightItalic\.ttf/"\/3rdparty\/roboto\/Roboto-LightItalic\.ttf/g' public/css/nextdom.css
	sed -i 's/[\"]Roboto-Regular\.ttf/"\/3rdparty\/roboto\/Roboto-Regular\.ttf/g' public/css/nextdom.css
	sed -i 's/[\"]Roboto-Italic\.ttf/"\/3rdparty\/roboto\/Roboto-Italic\.ttf/g' public/css/nextdom.css
	sed -i 's/[\"]Roboto-Bold\.ttf/"\/3rdparty\/roboto\/Roboto-Bold\.ttf/g' public/css/nextdom.css
	sed -i 's/[\"]Roboto-BoldItalic\.ttf/"\/3rdparty\/roboto\/Roboto-BoldItalic\.ttf/g' public/css/nextdom.css
	sed -i 's/\.\.\/fonts\/glyphicons-halflings/\/3rdparty\/bootstrap\/fonts\/glyphicons-halflings/g' public/css/nextdom.css
	sed -i 's/images\/ui-bg_glass_75_ffffff_1x400\.png/\/3rdparty\/jquery\.ui\/jquery-ui-bootstrap\/images\/ui-bg_glass_75_ffffff_1x400.png/g' public/css/nextdom.css
	sed -i 's/images\/ui-icons_222222_256x240\.png/\/3rdparty\/jquery\.ui\/jquery-ui-bootstrap\/images\/ui-icons_222222_256x240.png/g' public/css/nextdom.css
	sed -i 's/[\"]Roboto-Light\.ttf/"\/3rdparty\/roboto\/Roboto-Light\.ttf/g' public/css/nextdom.mob.css
	sed -i 's/[\"]Roboto-LightItalic\.ttf/"\/3rdparty\/roboto\/Roboto-LightItalic\.ttf/g' public/css/nextdom.mob.css
	sed -i 's/[\"]Roboto-Regular\.ttf/"\/3rdparty\/roboto\/Roboto-Regular\.ttf/g' public/css/nextdom.mob.css
	sed -i 's/[\"]Roboto-Italic\.ttf/"\/3rdparty\/roboto\/Roboto-Italic\.ttf/g' public/css/nextdom.mob.css
	sed -i 's/[\"]Roboto-Bold\.ttf/"\/3rdparty\/roboto\/Roboto-Bold\.ttf/g' public/css/nextdom.mob.css
	sed -i 's/[\"]Roboto-BoldItalic\.ttf/"\/3rdparty\/roboto\/Roboto-BoldItalic\.ttf/g' public/css/nextdom.mob.css
	sed -i 's/images\/ui-bg_glass_75_ffffff_1x400\.png/\/3rdparty\/jquery\.ui\/jquery-ui-bootstrap\/images\/ui-bg_glass_75_ffffff_1x400.png/g' public/css/nextdom.mob.css
	sed -i 's/images\/ui-icons_222222_256x240\.png/\/3rdparty\/jquery\.ui\/jquery-ui-bootstrap\/images\/ui-icons_222222_256x240.png/g' public/css/nextdom.mob.css
}

function gen_js {
	echo " >>> Generation du JS"
    cat 3rdparty/jquery.utils/jquery.utils.js \
        3rdparty/iziToast/js/iziToast.min.js \
        assets/js/desktop/utils.js \
        core/js/core.js \
        3rdparty/bootstrap/js/bootstrap.js \
        3rdparty/jquery.ui/jquery-ui.min.js \
        core/js/nextdom.class.js \
        core/js/private.class.js \
        core/js/eqLogic.class.js \
        core/js/cmd.class.js \
        core/js/object.class.js \
        core/js/scenario.class.js \
        core/js/plugin.class.js \
        core/js/message.class.js \
        core/js/view.class.js \
        core/js/config.class.js \
        core/js/history.class.js \
        core/js/cron.class.js \
        core/js/security.class.js \
        core/js/update.class.js \
        core/js/user.class.js \
        core/js/backup.class.js \
        core/js/interact.class.js \
        core/js/update.class.js \
        core/js/plan.class.js \
        core/js/log.class.js \
        core/js/repo.class.js \
        core/js/network.class.js \
        core/js/dataStore.class.js \
        core/js/cache.class.js \
        core/js/report.class.js \
        core/js/jeedom.class.js \
        3rdparty/bootbox/bootbox.min.js \
        3rdparty/highstock/highstock.js \
        3rdparty/highstock/highcharts-more.js \
        3rdparty/highstock/modules/solid-gauge.js \
        3rdparty/highstock/modules/exporting.js \
        3rdparty/highstock/modules/export-data.js \
        3rdparty/jquery.at.caret/jquery.at.caret.min.js \
        3rdparty/jwerty/jwerty.js \
        3rdparty/jquery.packery/jquery.packery.js \
        3rdparty/jquery.lazyload/jquery.lazyload.js \
        3rdparty/codemirror/lib/codemirror.js \
        3rdparty/codemirror/addon/edit/matchbrackets.js \
        3rdparty/codemirror/mode/htmlmixed/htmlmixed.js \
        3rdparty/codemirror/mode/clike/clike.js \
        3rdparty/codemirror/mode/php/php.js \
        3rdparty/codemirror/mode/xml/xml.js \
        3rdparty/codemirror/mode/javascript/javascript.js \
        3rdparty/codemirror/mode/css/css.js \
        3rdparty/jquery.tree/jstree.min.js \
        3rdparty/jquery.fileupload/jquery.ui.widget.js \
        3rdparty/jquery.fileupload/jquery.iframe-transport.js \
        3rdparty/jquery.fileupload/jquery.fileupload.js \
        3rdparty/jquery.multi-column-select/multi-column-select.js \
        3rdparty/jquery.sew/jquery.sew.min.js \
        3rdparty/jquery.cron/jquery.cron.min.js \
        3rdparty/jquery.contextMenu/jquery.contextMenu.min.js \
        3rdparty/autosize/autosize.min.js \
        3rdparty/select2/js/select2.js \
        3rdparty/inputmask/jquery.inputmask.bundle.js \
        3rdparty/bootstrap-colorpicker/js/bootstrap-colorpicker.js \
        3rdparty/datetimepicker/jquery.datetimepicker.js \
        3rdparty/ion.rangeSlider/js/ion.rangeSlider.js \
        3rdparty/AdminLTE/js/adminlte.js \
        3rdparty/AdminLTE/js/adminlte_nextdom.js \
        3rdparty/icheck/icheck.js > /tmp/temp.js
    python -m jsmin /tmp/temp.js > public/js/base.js
    rm /tmp/temp.js
    php script/translate.php public/js/base.js

    mkdir -p public/js/desktop/Market

    for jsFile in assets/js/desktop/*.js
    do
        python -m jsmin $jsFile > public/js/desktop/${jsFile##*/}
        php script/translate.php public/js/desktop/${jsFile##*/}
    done
    for jsFile in assets/js/desktop/Market/*.js
    do
        python -m jsmin $jsFile > public/js/desktop/Market/${jsFile##*/}
        php script/translate.php public/js/desktop/Market/${jsFile##*/}
    done
}

function init_dependencies {
	sass --version > /dev/null 2>&1
	if [ $? -ne 0 ]; then
		echo " >>> Installation de node et npm"
		wget https://deb.nodesource.com/setup_10.x -O install_npm.sh
		bash install_npm.sh
		apt install -y nodejs
		echo " >>> Installation de sass"
		npm install -g sass
	fi
	python -c "import jsmin" 2>&1 /dev/null
	if [ $? -ne 0 ]; then
	    . /etc/os-release
	    if [[ "$NAME" == *Debian* ]]; then
	        apt install -y python-jsmin;
	    else
	        pip install jsmin;
	    fi
	fi
}

function copy_assets {
    echo " >>> Copie des icones"
	cp -fr assets/icon public/
	echo " >>> Copie des themes"
	cp -fr assets/themes public/
	echo " >>> Copie des images"
	cp -fr assets/img public/
	gen_css
	gen_js
}

function start {
	while true; do
		FIND_CSS_RES=$(find assets/css -mmin -0.1)
		if [ -n "$FIND_CSS_RES" ]; then
			gen_css
			echo " >>> OK"
		fi
		FIND_JS_RES=$(find core/js -mmin -0.1)
		if [ -n "$FIND_JS_RES" ]; then
			gen_js
			echo " >>> OK"
		fi
		FIND_JS_RES=$(find assets/js -mmin -0.1)
		if [ -n "$FIND_JS_RES" ]; then
			gen_js
			echo " >>> OK"
		fi
		sleep 1
	done
}

init_dependencies
if [ "$#" == 0 ]; then
    echo "Pour lancer la génération automatique, ajouter l'option --watch"
	mkdir -p public/css
	mkdir -p public/js
	copy_assets;
elif [ "$1" == "--watch" ]; then
	start;
fi
