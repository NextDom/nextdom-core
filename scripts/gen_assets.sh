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

# Get current directory
set_root() {
    local this=`readlink -n -f $1`
    root=`dirname $this`
}
set_root $0

function gen_css {
    COMPRESS=""
    if [ $# -eq 0 ]; then
        COMPRESS="--style compressed"
    fi
	echo " >>> Generation du CSS"

	mkdir -p public/css/adminlte
	sass assets/css/nextdom.scss public/css/nextdom.css $COMPRESS
	sass assets/css/nextdom.mob.scss public/css/nextdom.mob.css $COMPRESS
	sass assets/css/firstUse.scss public/css/firstUse.css $COMPRESS
	sass assets/css/rescue.scss public/css/rescue.css $COMPRESS
	sass assets/css/Market/market.scss public/css/market.css $COMPRESS

	# Remplacement des chemins
#	sed -i s#\"images/ui-#\"/assets/3rdparty/jquery.ui/jquery-ui-bootstrap/images/ui-#g public/css/nextdom.css
#	sed -i s#\"images/ui-#\"/assets/3rdparty/jquery.ui/jquery-ui-bootstrap/images/ui-#g public/css/nextdom.mob.css
}

function gen_js {
	echo " >>> Generation du JS"
    cat assets/3rdparty/jquery.utils/jquery.utils.js \
        assets/3rdparty/jquery.ui/jquery-ui.min.js \
        vendor/node_modules/bootstrap/dist/js/bootstrap.min.js \
        vendor/node_modules/izitoast/dist/js/iziToast.min.js \
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
        vendor/node_modules/bootbox/bootbox.min.js \
        vendor/node_modules/highcharts/highstock.js \
        vendor/node_modules/highcharts/highcharts-more.js \
        vendor/node_modules/highcharts/modules/solid-gauge.js \
        vendor/node_modules/highcharts/modules/exporting.js \
        vendor/node_modules/highcharts/modules/export-data.js \
        assets/3rdparty/jquery.at.caret/jquery.at.caret.min.js \
        vendor/node_modules/jwerty/jwerty.js \
        vendor/node_modules/packery/dist/packery.pkgd.js \
        vendor/node_modules/jquery-lazyload/jquery.lazyload.js \
        vendor/node_modules/codemirror/lib/codemirror.js \
        vendor/node_modules/codemirror/addon/edit/matchbrackets.js \
        vendor/node_modules/codemirror/mode/htmlmixed/htmlmixed.js \
        vendor/node_modules/codemirror/mode/clike/clike.js \
        vendor/node_modules/codemirror/mode/php/php.js \
        vendor/node_modules/codemirror/mode/xml/xml.js \
        vendor/node_modules/codemirror/mode/javascript/javascript.js \
        vendor/node_modules/codemirror/mode/css/css.js \
        vendor/node_modules/jstree/dist/jstree.js \
        vendor/node_modules/blueimp-file-upload/js/jquery.iframe-transport.js \
        vendor/node_modules/blueimp-file-upload/js/jquery.fileupload.js \
        assets/3rdparty/jquery.multi-column-select/multi-column-select.js \
        assets/3rdparty/jquery.sew/jquery.sew.min.js \
        vendor/node_modules/jquery-cron/dist/jquery-cron.js \
        vendor/node_modules/jquery-contextmenu/dist/jquery.contextMenu.min.js \
        vendor/node_modules/inputmask/dist/jquery.inputmask.bundle.js \
        vendor/node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js \
        vendor/node_modules/jquery-datetimepicker/jquery.datetimepicker.js  > /tmp/temp.js

if [ $# -eq 0 ]; then
    python -m jsmin /tmp/temp.js > public/js/base.js
    rm /tmp/temp.js
    php scripts/translate.php public/js/base.js

    mkdir -p public/js/adminlte
    for jsFile in assets/js/adminlte/*.js
    do
        python -m jsmin $jsFile > public/js/adminlte/${jsFile##*/}
        php scripts/translate.php public/js/adminlte/${jsFile##*/}
    done
    mkdir -p public/js/desktop
    for jsFile in assets/js/desktop/*.js
    do
        python -m jsmin $jsFile > public/js/desktop/${jsFile##*/}
        php scripts/translate.php public/js/desktop/${jsFile##*/}
    done
    mkdir -p public/js/desktop/admin
    for jsFile in assets/js/desktop/admin/*.js
    do
        python -m jsmin $jsFile > public/js/desktop/admin/${jsFile##*/}
        php scripts/translate.php public/js/desktop/admin/${jsFile##*/}
    done
    mkdir -p public/js/desktop/diagnostic
    for jsFile in assets/js/desktop/diagnostic/*.js
    do
        python -m jsmin $jsFile > public/js/desktop/diagnostic/${jsFile##*/}
        php scripts/translate.php public/js/desktop/diagnostic/${jsFile##*/}
    done
    mkdir -p public/js/desktop/params
    for jsFile in assets/js/desktop/params/*.js
    do
        python -m jsmin $jsFile > public/js/desktop/params/${jsFile##*/}
        php scripts/translate.php public/js/desktop/params/${jsFile##*/}
    done
    mkdir -p public/js/desktop/tools
    for jsFile in assets/js/desktop/tools/*.js
    do
        python -m jsmin $jsFile > public/js/desktop/tools/${jsFile##*/}
        php scripts/translate.php public/js/desktop/tools/${jsFile##*/}
    done
    mkdir -p public/js/modals
    for jsFile in assets/js/modals/*.js
    do
        python -m jsmin $jsFile > public/js/modals/${jsFile##*/}
        php scripts/translate.php public/js/modals/${jsFile##*/}
    done

    mkdir -p public/js/desktop/Market
    for jsFile in assets/js/desktop/Market/*.js
    do
        python -m jsmin $jsFile > public/js/desktop/Market/${jsFile##*/}
        php scripts/translate.php public/js/desktop/Market/${jsFile##*/}
    done
fi
}

function copy_assets {
    echo " >>> Copie des icones"
	cp -fr assets/icon public/
	echo " >>> Copie des themes"
	cp -fr assets/themes public/
	echo " >>> Copie des images"
	cp -fr assets/img public/
	echo " >>> Copie des 3rdparty"
	cp -fr assets/3rdparty public/
	gen_css
	gen_js
}

function start {
	while true; do
		FIND_CSS_RES=$(find assets/css -mmin -0.1)
		if [ -n "$FIND_CSS_RES" ]; then
			gen_css no_compress
			echo " >>> OK"
		fi
		FIND_JS_RES=$(find core/js -mmin -0.1)
		if [ -n "$FIND_JS_RES" ]; then
			gen_js no_compress
			echo " >>> OK"
		fi
		FIND_JS_RES=$(find assets/js -mmin -0.1)
		if [ -n "$FIND_JS_RES" ]; then
			gen_js no_compress
			echo " >>> OK"
		fi
		sleep 1
	done
}

cd ${root}/..

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
