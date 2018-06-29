#!/bin/sh

pip install csscompressor
pip install jsmin

mkdir -p css
mkdir -p js

sed -i 's/url[(]"images/url^("\/3rdparty\/jquery\.ui\/jquery-ui-bootstrap\/images/g' css/nextdom.css
sed -i 's/url[(]"images/url^("\/3rdparty\/jquery\.ui\/jquery-ui-bootstrap\/images/g' css/nextdom.min.css

sed -i 's/NotoSans-Regular\.ttf/\/3rdparty\/font-noto\/NotoSans-Regular\.ttf/g' css/nextdom.css
sed -i 's/NotoSans-Italic\.ttf/\/3rdparty\/font-noto\/NotoSans-Italic\.ttf/g' css/nextdom.css
sed -i 's/NotoSans-Bold\.ttf/\/3rdparty\/font-noto\/NotoSans-Bold\.ttf/g' css/nextdom.css
sed -i 's/NotoSans-Regular\.ttf/\/3rdparty\/font-noto\/NotoSans-Regular\.ttf/g' css/nextdom.min.css
sed -i 's/NotoSans-Italic\.ttf/\/3rdparty\/font-noto\/NotoSans-Italic\.ttf/g' css/nextdom.min.css
sed -i 's/NotoSans-Bold\.ttf/\/3rdparty\/font-noto\/NotoSans-Bold\.ttf/g' css/nextdom.min.css

cat 3rdparty/jquery.utils/jquery.utils.js \
    core/js/core.js \
    3rdparty/bootstrap/bootstrap.min.js \
    3rdparty/jquery.ui/jquery-ui.min.js \
    3rdparty/jquery.ui/jquery.ui.datepicker.fr.js \
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
    3rdparty/bootbox/bootbox.min.js \
    3rdparty/highstock/highstock.js \
    3rdparty/highstock/highcharts-more.js \
    3rdparty/highstock/modules/solid-gauge.js \
    3rdparty/highstock/modules/exporting.js \
    3rdparty/highstock/modules/export-data.js \
    desktop/js/utils.js \
    3rdparty/jquery.toastr/jquery.toastr.min.js \
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
    3rdparty/jquery.tablesorter/jquery.tablesorter.min.js \
    3rdparty/jquery.tablesorter/jquery.tablesorter.widgets.min.js \
    3rdparty/datetimepicker/jquery.datetimepicker.js \
    3rdparty/jquery.cron/jquery.cron.min.js \
    3rdparty/jquery.contextMenu/jquery.contextMenu.min.js \
    3rdparty/autosize/autosize.min.js > js/temp.js

python -m jsmin js/temp.js > js/base.js

rm js/temp.js
