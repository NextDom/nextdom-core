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

set -e

function gen_css {
    COMPRESS=""
    if [ $# -eq 0 ]; then
        COMPRESS="--style compressed"
    fi
  	echo " >>> Generate CSS"
    mkdir -p public/css/pages
    mkdir -p public/css/modals
    mkdir -p public/css/themes
    sass --update --stop-on-error assets/css/compiled:public/css $COMPRESS
    echo " >>> Generate Themes"
    for theme_file in assets/css/themes/output/*.scss; do
        sass --update --stop-on-error ${theme_file}:public/css/themes/$(basename "${theme_file}" .scss).css $COMPRESS
    done
  	# Path replace
  	sed -i s#\"images/ui-#\"/assets/css/vendors/jquery-ui-bootstrap/images/ui-#g public/css/nextdom.css
}

function gen_js {
	echo " >>> Generate JS"
  # js files to merge in base.js, respect this order to prevent conflits, can find details in PrepareView
  jsFiles=(vendor/node_modules/jquery-ui-dist/jquery-ui.min.js \
           vendor/node_modules/bootstrap/dist/js/bootstrap.min.js \
           vendor/node_modules/pace-js/pace.min.js \
           vendor/node_modules/admin-lte/dist/js/adminlte.min.js \
           vendor/node_modules/izitoast/dist/js/iziToast.min.js \
           assets/3rdparty/jquery.utils/jquery.utils.js \
           assets/3rdparty/jquery.at.caret/jquery.at.caret.min.js \
           assets/3rdparty/jquery.multi-column-select/multi-column-select.js \
           assets/3rdparty/jquery.ui-touch-punch/jquery.ui.touch-punch.min.js \
           assets/js/core/core.js \
           assets/js/core/nextdom.class.js \
           assets/js/core/private.class.js \
           assets/js/core/eqLogic.class.js \
           assets/js/core/cmd.class.js \
           assets/js/core/object.class.js \
           assets/js/core/scenario.class.js \
           assets/js/core/plugin.class.js \
           assets/js/core/message.class.js \
           assets/js/core/view.class.js \
           assets/js/core/config.class.js \
           assets/js/core/history.class.js \
           assets/js/core/cron.class.js \
           assets/js/core/security.class.js \
           assets/js/core/update.class.js \
           assets/js/core/user.class.js \
           assets/js/core/backup.class.js \
           assets/js/core/interact.class.js \
           assets/js/core/update.class.js \
           assets/js/core/plan.class.js \
           assets/js/core/log.class.js \
           assets/js/core/repo.class.js \
           assets/js/core/network.class.js \
           assets/js/core/dataStore.class.js \
           assets/js/core/cache.class.js \
           assets/js/core/report.class.js \
           assets/js/core/note.class.js \
           assets/js/core/listener.class.js \
           assets/js/core/jeedom.class.js \
           assets/js/desktop/conflicts.js \
           assets/js/desktop/loads.js \
           assets/js/desktop/inits.js \
           assets/js/desktop/gui.js \
           assets/js/desktop/utils.js \
           assets/js/desktop/search.js \
           assets/js/desktop/jeedomCompatibility.js \
           vendor/node_modules/bootbox/dist/bootbox.min.js \
           vendor/node_modules/highcharts/highstock.js \
           vendor/node_modules/highcharts/highcharts-more.js \
           vendor/node_modules/highcharts/modules/solid-gauge.js \
           vendor/node_modules/highcharts/modules/exporting.js \
           vendor/node_modules/highcharts/modules/export-data.js \
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
           vendor/node_modules/blueimp-file-upload/js/jquery.iframe-transport.js \
           vendor/node_modules/blueimp-file-upload/js/jquery.fileupload.js \
           vendor/node_modules/jquery-cron/dist/jquery-cron.js \
           vendor/node_modules/jquery-contextmenu/dist/jquery.contextMenu.min.js \
           vendor/node_modules/inputmask/dist/jquery.inputmask.bundle.js \
           vendor/node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js \
           vendor/node_modules/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js \
           vendor/node_modules/snapsvg/dist/snap.svg-min.js \
           vendor/node_modules/moment/min/moment.min.js \
           vendor/node_modules/vivagraphjs/dist/vivagraph.min.js \
           assets/js/factory/NextDomElement.js \
           assets/js/factory/NextDomEnum.js \
           assets/js/factory/NextDomUIDGenerator.js \
           assets/js/factory/elements/A.js \
           assets/js/factory/elements/Br.js \
           assets/js/factory/elements/Button.js \
           assets/js/factory/elements/Div.js \
           assets/js/factory/elements/DivWithTooltip.js \
           assets/js/factory/elements/HorizontalLayout.js \
           assets/js/factory/elements/IFA.js \
           assets/js/factory/elements/InputText.js \
           assets/js/factory/elements/Label.js \
           assets/js/factory/elements/Space.js \
           assets/js/factory/elements/Table.js \
           assets/js/factory/elements/Tbody.js \
           assets/js/factory/elements/Td.js \
           assets/js/factory/elements/TextNode.js \
           assets/js/factory/elements/Th.js \
           assets/js/factory/elements/Thead.js \
           assets/js/factory/elements/Tr.js \
           assets/js/factory/elements/VerticalLayout.js)

  tmpfile=$(mktemp)
  for c_file in ${jsFiles[*]}; do
    cat ${c_file} >> ${tmpfile}
    echo '' >> ${tmpfile}
  done

  if [ $# -eq 0 ]; then
      python -m jsmin ${tmpfile} > public/js/base.js
      rm ${tmpfile}
      php scripts/translate.php public/js/base.js

      directories=(js/desktop \
                   js/desktop/admin \
                   js/desktop/diagnostic \
                   js/desktop/pages \
                   js/desktop/params \
                   js/desktop/tools \
                   js/desktop/tools/markets \
                   js/desktop/tools/osdb \
                   js/modals \
                   js/factory \
                   js/factory/elements)
      for c_dir in ${directories[*]}; do
        mkdir -p public/${c_dir}
        for c_file in assets/${c_dir}/*.js; do
          python -m jsmin ${c_file} > public/${c_dir}/${c_file##*/}
          php scripts/translate.php public/${c_dir}/${c_file##*/}
        done
      done
  fi
}

function copy_assets {
  echo " >>> Copy icons"
	cp -fr assets/icon public/
	echo " >>> Copy images"
	cp -fr assets/img public/
  echo " >>> Copy html"
	cp -fr assets/*.html public/
	gen_css
	gen_js
}

function clean_cache {
	echo " >>> Cleaning Caches"
	rm -rf var/cache/twig/*
	rm -rf var/i18n/*
	rm -fr var/cache/i18n/*
}

function start {
	while true; do
		FIND_CSS_RES=$(find assets/css -mmin -0.1)
		if [ -n "$FIND_CSS_RES" ]; then
			gen_css no_compress
      clean_cache
			echo " >>> OK"
		fi
		FIND_JS_RES=$(find core/js -mmin -0.1)
		if [ -n "$FIND_JS_RES" ]; then
			gen_js no_compress
      clean_cache
			echo " >>> OK"
		fi
		FIND_JS_RES=$(find assets/js -mmin -0.1)
		if [ -n "$FIND_JS_RES" ]; then
			gen_js no_compress
      clean_cache
			echo " >>> OK"
		fi
		sleep 1
	done
}


cd ${root}/..
if [ "$#" == 0 ]; then
    echo "To start automatic generation, add the --watch option"
	  mkdir -p public/css
	  mkdir -p public/js
	  copy_assets;
	  clean_cache
elif [ "$1" == "--watch" ]; then
	  start;
elif [ "$1" == "--css" ]; then
	  gen_css
elif [ "$1" == "--js" ]; then
	  gen_js
fi
