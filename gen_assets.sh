#!/bin/bash
# This file is part of NextDom Software.
#
# NextDom is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# NextDom Software is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.


function gen_css {
	echo " >>> Generation du CSS"
	mkdir -p public/css/adminlte
	sass assets/css/nextdom.scss public/css/nextdom.css --style compressed
	sass assets/css/nextdom.mob.scss public/css/nextdom.mob.css --style compressed
	sass assets/css/firstUse.scss public/css/firstUse.css --style compressed
	sass assets/css/rescue.scss public/css/rescue.css --style compressed
	sass assets/css/Market/market.scss public/css/market.css --style compressed

	# Remplacement des chemins
	sed -i s#url\(\"Roboto-#url\(\"/3rdparty/roboto/Roboto-#g public/css/nextdom.css
#	sed -i s#\.\./fonts/glyphicons-#/vendor/twitter/bootstrap/fonts/glyphicons-#g public/css/nextdom.css
	sed -i s#\"images/ui-#\"/assets/css/jquery-ui-bootstrap/images/ui-#g public/css/nextdom.css
	sed -i 's/[\"]32px\.png/"\/3rdparty\/jquery\.tree\/themes\/default\/32px.png/g' public/css/nextdom.css
	sed -i 's/[\"]throbber\.gif/"\/3rdparty\/jquery\.tree\/themes\/default\/throbber\.gif/g' public/css/nextdom.css

	sed -i s#url\(\"Roboto-#url\(\"/3rdparty/roboto/Roboto-# public/css/nextdom.mob.css
	sed -i s#\"images/ui-#\"/assets/css/jquery-ui-bootstrap/images/ui-#g public/css/nextdom.mob.css
	sed -i 's/[\"]32px\.png/\/3rdparty\/jquery\.tree\/themes\/default\/32.png/g' public/css/nextdom.mob.css
	sed -i 's/[\"]throbber\.gif/"\/3rdparty\/jquery\.tree\/themes\/default\/throbber\.gif/g' public/css/nextdom.mob.css
}

function gen_js {
	echo " >>> Generation du JS"
    cat 3rdparty/jquery.utils/jquery.utils.js \
        3rdparty/iziToast/js/iziToast.min.js \
        vendor/twitter/bootstrap/dist/js/bootstrap.js \
        vendor/components/jqueryui/jquery-ui.min.js \
        assets/js/desktop/utils.js \
        core/js/core.js \
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
        core/js/note.class.js \
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
        vendor/vakata/jstree/dist/jstree.min.js \
        3rdparty/jquery.fileupload/jquery.ui.widget.js \
        3rdparty/jquery.fileupload/jquery.iframe-transport.js \
        3rdparty/jquery.fileupload/jquery.fileupload.js \
        3rdparty/jquery.multi-column-select/multi-column-select.js \
        3rdparty/jquery.sew/jquery.sew.min.js \
        3rdparty/jquery.cron/jquery.cron.min.js \
        3rdparty/jquery.contextMenu/jquery.contextMenu.min.js \
        3rdparty/autosize/autosize.min.js \
        3rdparty/inputmask/jquery.inputmask.bundle.js \
        vendor/itsjavi/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js \
        3rdparty/datetimepicker/jquery.datetimepicker.js  > /tmp/temp.js
    python -m jsmin /tmp/temp.js > public/js/base.js
    rm /tmp/temp.js
    php script/translate.php public/js/base.js

    mkdir -p public/js/adminlte
    for jsFile in assets/js/adminlte/*.js
    do
        python -m jsmin $jsFile > public/js/adminlte/${jsFile##*/}
        php script/translate.php public/js/adminlte/${jsFile##*/}
    done
    mkdir -p public/js/desktop
    for jsFile in assets/js/desktop/*.js
    do
        python -m jsmin $jsFile > public/js/desktop/${jsFile##*/}
        php script/translate.php public/js/desktop/${jsFile##*/}
    done

    mkdir -p public/js/desktop/Market
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
elif [ "$1" == "--css" ]; then
	gen_css
elif [ "$1" == "--js" ]; then
	gen_js
fi
