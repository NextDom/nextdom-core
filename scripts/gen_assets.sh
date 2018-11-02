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

	mkdir -p $nextdomDir/public/css/adminlte
	sass $nextdomDir/assets/css/nextdom.scss $nextdomDir/public/css/nextdom.css --style $COMPRESS
	sass $nextdomDir/assets/css/nextdom.mob.scss $nextdomDir/public/css/nextdom.mob.css --style $COMPRESS
	sass $nextdomDir/assets/css/firstUse.scss $nextdomDir/public/css/firstUse.css --style $COMPRESS
	sass $nextdomDir/assets/css/rescue.scss $nextdomDir/public/css/rescue.css --style $COMPRESS
	sass $nextdomDir/assets/css/Market/market.scss $nextdomDir/public/css/market.css --style $COMPRESS

	# Remplacement des chemins
#	sed -i s#url\(\"Roboto-#url\(\"/3rdparty/roboto/Roboto-#g public/css/nextdom.css
#	sed -i s#\.\./fonts/glyphicons-#/vendor/twitter/bootstrap/fonts/glyphicons-#g public/css/nextdom.css
	sed -i s#\"images/ui-#\"/assets/css/jquery-ui-bootstrap/images/ui-#g $nextdomDir/public/css/nextdom.css
#	sed -i 's/[\"]32px\.png/"\/3rdparty\/jquery\.tree\/themes\/default\/32px.png/g' public/css/nextdom.css
#	sed -i 's/[\"]throbber\.gif/"\/3rdparty\/jquery\.tree\/themes\/default\/throbber\.gif/g' public/css/nextdom.css

#	sed -i s#url\(\"Roboto-#url\(\"/3rdparty/roboto/Roboto-# public/css/nextdom.mob.css
	sed -i s#\"images/ui-#\"/assets/css/jquery-ui-bootstrap/images/ui-#g $nextdomDir/public/css/nextdom.mob.css
#	sed -i 's/[\"]32px\.png/\/3rdparty\/jquery\.tree\/themes\/default\/32.png/g' public/css/nextdom.mob.css
#	sed -i 's/[\"]throbber\.gif/"\/3rdparty\/jquery\.tree\/themes\/default\/throbber\.gif/g' public/css/nextdom.mob.css
}

function gen_js {
	echo " >>> Generation du JS"
    cat $nextdomDir/3rdparty/jquery.utils/jquery.utils.js \
        $nextdomDir/vendor/node_modules/bootstrap/dist/js/bootstrap.min.js \
        $nextdomDir/vendor/node_modules/jquery-ui-dist/jquery-ui.min.js \
        $nextdomDir/vendor/node_modules/izitoast/dist/js/iziToast.min.js \
        $nextdomDir/assets/js/desktop/utils.js \
        $nextdomDir/core/js/core.js \
        $nextdomDir/core/js/nextdom.class.js \
        $nextdomDir/core/js/private.class.js \
        $nextdomDir/core/js/eqLogic.class.js \
        $nextdomDir/core/js/cmd.class.js \
        $nextdomDir/core/js/object.class.js \
        $nextdomDir/core/js/scenario.class.js \
        $nextdomDir/core/js/plugin.class.js \
        $nextdomDir/core/js/message.class.js \
        $nextdomDir/core/js/view.class.js \
        $nextdomDir/core/js/config.class.js \
        $nextdomDir/core/js/history.class.js \
        $nextdomDir/core/js/cron.class.js \
        $nextdomDir/core/js/security.class.js \
        $nextdomDir/core/js/update.class.js \
        $nextdomDir/core/js/user.class.js \
        $nextdomDir/core/js/backup.class.js \
        $nextdomDir/core/js/interact.class.js \
        $nextdomDir/core/js/update.class.js \
        $nextdomDir/core/js/plan.class.js \
        $nextdomDir/core/js/log.class.js \
        $nextdomDir/core/js/repo.class.js \
        $nextdomDir/core/js/network.class.js \
        $nextdomDir/core/js/dataStore.class.js \
        $nextdomDir/core/js/cache.class.js \
        $nextdomDir/core/js/report.class.js \
        $nextdomDir/core/js/note.class.js \
        $nextdomDir/core/js/jeedom.class.js \
        $nextdomDir/vendor/node_modules/bootbox/bootbox.min.js \
        $nextdomDir/vendor/node_modules/highcharts/highstock.js \
        $nextdomDir/vendor/node_modules/highcharts/highcharts-more.js \
        $nextdomDir/vendor/node_modules/highcharts/modules/solid-gauge.js \
        $nextdomDir/vendor/node_modules/highcharts/modules/exporting.js \
        $nextdomDir/vendor/node_modules/highcharts/modules/export-data.js \
        $nextdomDir/3rdparty/jquery.at.caret/jquery.at.caret.min.js \
        $nextdomDir/vendor/node_modules/jwerty/jwerty.js \
        $nextdomDir/vendor/node_modules/packery/dist/packery.pkgd.js \
        $nextdomDir/vendor/node_modules/lazyload/lazyload.js \
        $nextdomDir/vendor/node_modules/codemirror/lib/codemirror.js \
        $nextdomDir/vendor/node_modules/codemirror/addon/edit/matchbrackets.js \
        $nextdomDir/vendor/node_modules/codemirror/mode/htmlmixed/htmlmixed.js \
        $nextdomDir/vendor/node_modules/codemirror/mode/clike/clike.js \
        $nextdomDir/vendor/node_modules/codemirror/mode/php/php.js \
        $nextdomDir/vendor/node_modules/codemirror/mode/xml/xml.js \
        $nextdomDir/vendor/node_modules/codemirror/mode/javascript/javascript.js \
        $nextdomDir/vendor/node_modules/codemirror/mode/css/css.js \
        $nextdomDir/vendor/node_modules/jstree/dist/jstree.js \
        $nextdomDir/vendor/node_modules/blueimp-file-upload/js/jquery.iframe-transport.js \
        $nextdomDir/vendor/node_modules/blueimp-file-upload/js/jquery.fileupload.js \
        $nextdomDir/3rdparty/jquery.multi-column-select/multi-column-select.js \
        $nextdomDir/3rdparty/jquery.sew/jquery.sew.min.js \
        $nextdomDir/vendor/node_modules/jquery-cron/dist/jquery-cron.js \
        $nextdomDir/vendor/node_modules/jquery-contextmenu/dist/jquery.contextMenu.min.js \
        $nextdomDir/vendor/node_modules/inputmask/dist/jquery.inputmask.bundle.js \
        $nextdomDir/vendor/node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js \
        $nextdomDir/vendor/node_modules/jquery-datetimepicker/jquery.datetimepicker.js  > /tmp/temp.js
        
if [ $# -eq 0 ]; then
    python -m jsmin /tmp/temp.js > public/js/base.js
    rm /tmp/temp.js
    php scripts/translate.php public/js/base.js

    mkdir -p $nextdomDir/public/js/adminlte
    for jsFile in $nextdomDir/assets/js/adminlte/*.js
    do
        python -m jsmin $jsFile > $nextdomDir/public/js/adminlte/${jsFile##*/}
        php $nextdomDir/scripts/translate.php $nextdomDir/public/js/adminlte/${jsFile##*/}
    done
    mkdir -p $nextdomDir/public/js/desktop
    for jsFile in $nextdomDir/assets/js/desktop/*.js
    do
        python -m jsmin $jsFile > $nextdomDir/public/js/desktop/${jsFile##*/}
        php $nextdomDir/scripts/translate.php $nextdomDir/public/js/desktop/${jsFile##*/}
    done
    mkdir -p $nextdomDir/public/js/modals
    for jsFile in $nextdomDir/assets/js/modals/*.js
    do
        python -m jsmin $jsFile > $nextdomDir/public/js/modals/${jsFile##*/}
        php $nextdomDir/scripts/translate.php $nextdomDir/public/js/modals/${jsFile##*/}
    done

    mkdir -p $nextdomDir/public/js/desktop/Market
    for jsFile in $nextdomDir/assets/js/desktop/Market/*.js
    do
        python -m jsmin $jsFile > $nextdomDir/public/js/desktop/Market/${jsFile##*/}
        php $nextdomDir/scripts/translate.php $nextdomDir/public/js/desktop/Market/${jsFile##*/}
    done
fi
}

function copy_assets {
    echo " >>> Copie des icones"
	cp -fr $nextdomDir/assets/icon $nextdomDir/public/
	echo " >>> Copie des themes"
	cp -fr $nextdomDir/assets/themes $nextdomDir/public/
	echo " >>> Copie des images"
	cp -fr $nextdomDir/assets/img $nextdomDir/public/
	gen_css
	gen_js
}

function start {
	while true; do
		FIND_CSS_RES=$(find $nextdomDir/assets/css -mmin -0.1)
		if [ -n "$FIND_CSS_RES" ]; then
			gen_css no_compress
			echo " >>> OK"
		fi
		FIND_JS_RES=$(find $nextdomDir/core/js -mmin -0.1)
		if [ -n "$FIND_JS_RES" ]; then
			gen_js no_compress
			echo " >>> OK"
		fi
		FIND_JS_RES=$(find $nextdomDir/assets/js -mmin -0.1)
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
	mkdir -p $nextdomDir/public/css
	mkdir -p $nextdomDir/public/js
	copy_assets;
elif [ "$1" == "--watch" ]; then
	start;
elif [ "$1" == "--css" ]; then
	gen_css
elif [ "$1" == "--js" ]; then
	gen_js
fi
