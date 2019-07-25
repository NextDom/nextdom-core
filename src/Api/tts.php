<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
require_once __DIR__ . "/../../src/core.php";
if (!nextdom::apiModeResult(config::byKey('api::core::tts::mode', 'core', 'enable'))) {
    echo __('Vous n\'êtes pas autorisé à effectuer cette action', __FILE__);
    die();
}
if (!nextdom::apiAccess(init('apikey'))) {
    echo __('Vous n\'êtes pas autorisé à effectuer cette action', __FILE__);
    die();
}
log::add('tts', 'debug', 'Call tts api : ' . print_r($_GET, true));
if (class_exists('gcp')) {
    $engine = 'gcp';
} else {
    $engine = init('engine', 'pico');
    if ($engine == 'gcp') {
        $engine = 'pico';
    }
}
$text = init('text');
if ($text == '') {
    echo __('Aucun texte à dire', __FILE__);
    die();
}
$text = str_replace(array('[', ']', '#', '{', '}'), '', $text);
$md5 = md5($text);
$tts_dir = nextdom::getTmpFolder('tts');
$filename = $tts_dir . '/' . $md5 . '.mp3';
if (file_exists($filename)) {
    log::add('tts', 'debug', 'Use cache for ' . $filename . ' (' . $text . ')');
    if (init('path') == 1) {
        echo $filename;
        die();
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $md5 . '.mp3');
    readfile($filename);
    die();
}
log::add('tts', 'debug', 'Generate tts for ' . $filename . ' (' . $text . ')');
if ($engine == 'pico' && exec('which pico2wave | wc -l') == 0) {
    $engine = 'espeak';
}
try {
    switch ($engine) {
        case 'gcp':
            gcp::tts($text);
            break;
        case 'espeak':
            $voice = init('voice', 'fr+f4');
            shell_exec('espeak -v' . $voice . ' "' . $text . '" --stdout | avconv -i - -ar 44100 -ac 2 -ab 192k -f mp3 ' . $filename . ' > /dev/null 2>&1');
            break;
        case 'pico':
            $volume = '-af "volume=' . init('volume', '6') . 'dB"';
            $lang = init('lang', 'fr-FR');
            shell_exec('pico2wave -l=' . $lang . ' -w=' . $md5 . '.wav "' . $text . '" > /dev/null 2>&1;avconv -i ' . $md5 . '.wav -ar 44100 ' . $volume . ' -ac 2 -ab 192k -f mp3 ' . $filename . ' > /dev/null 2>&1;rm ' . $md5 . '.wav');
            break;
        default:
            echo __('Moteur de voix inconnu : ', __FILE__) . $engine;
            die();
            break;
    }
} catch (Exception $e) {
    $volume = '-af "volume=' . init('volume', '6') . 'dB"';
    $lang = init('lang', 'fr-FR');
    shell_exec('pico2wave -l=' . $lang . ' -w=' . $md5 . '.wav "' . $text . '" > /dev/null 2>&1;avconv -i ' . $md5 . '.wav -ar 44100 ' . $volume . ' -ac 2 -ab 192k -f mp3 ' . $filename . ' > /dev/null 2>&1;rm ' . $md5 . '.wav');
}

if (init('path') == 1) {
    echo $filename;
} else {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $md5 . '.mp3');
    readfile($filename);
}
try {
    while (getDirectorySize($tts_dir) > (10 * 1024 * 1024)) {
        $older = array('file' => null, 'datetime' => null);
        foreach (ls($tts_dir . '/', '*') as $file) {
            if ($older['datetime'] == null || $older['datetime'] > filemtime($tts_dir . '/' . $file)) {
                $older['file'] = $tts_dir . '/' . $file;
                $older['datetime'] = filemtime($tts_dir . '/' . $file);
            }
        }
        if ($older['file'] == null) {
            log::add('tts', 'error', __('Erreur aucun fichier trouvé à supprimer alors que le répertoire fait : ', __FILE__) . getDirectorySize($tts_dir));
        }
        unlink($older['file']);
    }
} catch (Exception $e) {
    log::add('tts', 'error', $e->getMessage());
}
