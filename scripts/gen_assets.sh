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
    sass --update --stop-on-error assets/css/compiled:public/css $COMPRESS

  	# Remplacement des chemins
  	sed -i s#\"images/ui-#\"/assets/css/vendors/jquery-ui-bootstrap/images/ui-#g public/css/nextdom.css
  	sed -i s#\"images/ui-#\"/assets/css/vendors/jquery-ui-bootstrap/images/ui-#g public/css/nextdom.mob.css
}

function gen_js {
	echo " >>> Generate JS"
  jsFiles=(assets/3rdparty/jquery.utils/jquery.utils.js \
           vendor/node_modules/jquery-ui-dist/jquery-ui.min.js \
           vendor/node_modules/bootstrap/dist/js/bootstrap.min.js \
           vendor/node_modules/admin-lte/dist/js/adminlte.min.js \
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
           core/js/listener.class.js \
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
           vendor/node_modules/jquery-cron/dist/jquery-cron.js \
           vendor/node_modules/jquery-contextmenu/dist/jquery.contextMenu.min.js \
           vendor/node_modules/inputmask/dist/jquery.inputmask.bundle.js \
           vendor/node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js \
           vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.min.js \
           vendor/node_modules/tablesorter/dist/js/jquery.tablesorter.widgets.min.js \
           vendor/node_modules/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js \
           vendor/node_modules/snapsvg/dist/snap.svg-min.js)

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
                   js/modals)
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
	echo " >>> Copy themes"
	cp -fr assets/themes public/
	echo " >>> Copy images"
	cp -fr assets/img public/
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
