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

global $NEXTDOM_INTERNAL_CONFIG;
$NEXTDOM_INTERNAL_CONFIG = array(
    'eqLogic' => array(
        'category' => array(
            'heating' => array('name' => 'Chauffage', 'icon' => 'fa fa-fire', 'color' => '#2980b9', 'mcolor' => '#2980b9', 'cmdColor' => '#3498db', 'mcmdColor' => '#3498db'),
            'security' => array('name' => 'Sécurité', 'icon' => 'fa fa-lock', 'color' => '#745cb0', 'mcolor' => '#745cb0', 'cmdColor' => '#ac92ed', 'mcmdColor' => '#ac92ed'),
            'energy' => array('name' => 'Energie', 'icon' => 'fa fa-bolt', 'color' => '#2eb04b', 'mcolor' => '#2eb04b', 'cmdColor' => '#69e283', 'mcmdColor' => '#69e283'),
            'light' => array('name' => 'Lumière', 'icon' => 'fa fa-lightbulb-o', 'color' => '#f39c12', 'mcolor' => '#f39c12', 'cmdColor' => '#f1c40f', 'mcmdColor' => '#f1c40f'),
            'automatism' => array('name' => 'Automatisme', 'icon' => 'fa fa-magic', 'color' => '#808080', 'mcolor' => '#808080', 'cmdColor' => '#c2beb8', 'mcmdColor' => '#c2beb8'),
            'multimedia' => array('name' => 'Multimedia', 'icon' => 'fa fa-sliders', 'color' => '#34495e', 'mcolor' => '#34495e', 'cmdColor' => '#576E84', 'mcmdColor' => '#576E84'),
            'default' => array('name' => 'Autre', 'icon' => 'fa fa-circle-o', 'color' => '#19bc9c', 'mcolor' => '#19bc9c', 'cmdColor' => '#4CDFC2', 'mcmdColor' => '#4CDFC2'),
        ),
        'style' => array(
            'noactive' => '-webkit-filter: grayscale(100%);-moz-filter: grayscale(100);-o-filter: grayscale(100%);-ms-filter: grayscale(100%);filter: grayscale(100%); opacity: 0.35;',
        ),
        'displayType' => array(
            'dashboard' => array('name' => 'Dashboard'),
            'plan' => array('name' => 'Design'),
            'view' => array('name' => 'Vue')
        ),
    ),
    'interact' => array(
        'test' => array(
            '>' => array('superieur', '>', 'plus de', 'depasse'),
            '<' => array('inferieur', '<', 'moins de', 'descends en dessous'),
            '=' => array('egale', '=', 'vaut'),
            '!=' => array('different'),
        ),
    ),
    'nextdom' => array(
        'sources' => array(
            array('name' => 'NextDom Stable', 'type' => 'json', 'code' => 'nextdom_stable', 'data' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/results/nextdom-stable.json'),
            array('name' => 'NextDom Draft', 'type' => 'json', 'code' => 'nextdom_draft', 'data' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/results/nextdom-draft.json'),
            array('name' => 'NextDom Exclusivity', 'type' => 'json', 'code' => 'nextdom_exclusivity', 'data' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/results/nextdom-exclusivity.json')
        )
    ),
    'plugin' => array(
        'category' => array(
            'security' => array('name' => 'Sécurité', 'icon' => 'fa-lock'),
            'automation protocol' => array('name' => 'Protocole domotique', 'icon' => 'fa-rss'),
            'home automation protocol' => array('name' => 'Passerelle domotique', 'icon' => 'fa-asterisk'),
            'programming' => array('name' => 'Programmation', 'icon' => 'fa-code'),
            'organization' => array('name' => 'Organisation', 'icon' => 'fa-calendar', 'alias' => array('travel', 'finance')),
            'weather' => array('name' => 'Météo', 'icon' => 'fa-sun-o'),
            'communication' => array('name' => 'Communication', 'icon' => 'fa-comment'),
            'devicecommunication' => array('name' => 'Objets connectés', 'icon' => 'fa-language'),
            'multimedia' => array('name' => 'Multimédia', 'icon' => 'fa-sliders'),
            'wellness' => array('name' => 'Confort', 'icon' => 'fa-user'),
            'monitoring' => array('name' => 'Monitoring', 'icon' => 'fa-tachometer-alt'),
            'health' => array('name' => 'Santé', 'icon' => 'fa-briefcase-medical'),
            'nature' => array('name' => 'Nature', 'icon' => 'fa-leaf'),
            'automatisation' => array('name' => 'Automatisme', 'icon' => 'fa fa-magic'),
            'energy' => array('name' => 'Energie', 'icon' => 'fa fa-bolt'),
            'travel' => array('name' => 'Voyage', 'icon' => 'fa fa-plane'),
            'other' => array('name' => 'Autre', 'icon' => 'fa-bars'),
        ),
    ),
    'alerts' => array(
        'timeout' => array('name' => 'Timeout', 'icon' => 'fa fa-clock-o', 'level' => 6, 'check' => false, 'color' => '#FF0000'),
        'batterywarning' => array('name' => 'Batterie en Warning', 'icon' => 'fa fa-battery-quarter', 'level' => 2, 'check' => false, 'color' => '#FFAB00'),
        'batterydanger' => array('name' => 'Batterie en Danger', 'icon' => 'fa fa-battery-empty', 'level' => 3, 'check' => false, 'color' => '#FF0000'),
        'warning' => array('name' => 'Warning', 'icon' => 'fa fa-bell', 'level' => 4, 'check' => true, 'color' => '#FFAB00'),
        'danger' => array('name' => 'Danger', 'icon' => 'fa fa-exclamation', 'level' => 5, 'check' => true, 'color' => '#FF0000'),
    ),
    'cmd' => array(
        'generic_type' => array(
            'LIGHT_TOGGLE' => array('name' => 'Lumière Toggle', 'family' => 'Lumière', 'type' => 'Action'),
            'LIGHT_STATE' => array('name' => 'Lumière Etat', 'family' => 'Lumière', 'type' => 'Info'),
            'LIGHT_ON' => array('name' => 'Lumière Bouton On', 'family' => 'Lumière', 'type' => 'Action'),
            'LIGHT_OFF' => array('name' => 'Lumière Bouton Off', 'family' => 'Lumière', 'type' => 'Action'),
            'LIGHT_SLIDER' => array('name' => 'Lumière Slider', 'family' => 'Lumière', 'type' => 'Action'),
            'LIGHT_COLOR' => array('name' => 'Lumière Couleur', 'family' => 'Lumière', 'type' => 'Info'),
            'LIGHT_SET_COLOR' => array('name' => 'Lumière Couleur', 'family' => 'Lumière', 'type' => 'Action'),
            'LIGHT_MODE' => array('name' => 'Lumière Mode', 'family' => 'Lumière', 'type' => 'Action'),
            'LIGHT_TOGGLE' => array('name' => 'Lumière Toggle', 'family' => 'Lumière', 'type' => 'Action'),
            'LIGHT_STATE_BOOL' => array('name' => 'Lumière Etat (Binaire)', 'family' => 'Lumière', 'type' => 'Info', 'noapp' => true),
            'LIGHT_COLOR_TEMP' => array('name' => 'Lumière Température Couleur', 'family' => 'Lumière', 'type' => 'Info', 'noapp' => true),
            'LIGHT_SET_COLOR_TEMP' => array('name' => 'Lumière Température Couleur', 'family' => 'Lumière', 'type' => 'Action', 'noapp' => true),
            'ENERGY_STATE' => array('name' => 'Prise Etat', 'family' => 'Prise', 'type' => 'Info'),
            'ENERGY_ON' => array('name' => 'Prise Bouton On', 'family' => 'Prise', 'type' => 'Action'),
            'ENERGY_OFF' => array('name' => 'Prise Bouton Off', 'family' => 'Prise', 'type' => 'Action'),
            'ENERGY_SLIDER' => array('name' => 'Prise Slider', 'family' => 'Prise', 'type' => 'Action'),
            'FLAP_STATE' => array('name' => 'Volet Etat', 'family' => 'Volet', 'type' => 'Info'),
            'FLAP_UP' => array('name' => 'Volet Bouton Monter', 'family' => 'Volet', 'type' => 'Action'),
            'FLAP_DOWN' => array('name' => 'Volet Bouton Descendre', 'family' => 'Volet', 'type' => 'Action'),
            'FLAP_STOP' => array('name' => 'Volet Bouton Stop', 'family' => 'Volet', 'type' => 'Action'),
            'FLAP_SLIDER' => array('name' => 'Volet Bouton Slider', 'family' => 'Volet', 'type' => 'Action'),
            'FLAP_BSO_STATE' => array('name' => 'Volet BSO Etat', 'family' => 'Volet', 'type' => 'Info'),
            'FLAP_BSO_UP' => array('name' => 'Volet BSO Bouton Up', 'family' => 'Volet', 'type' => 'Action'),
            'FLAP_BSO_DOWN' => array('name' => 'Volet BSO Bouton Down', 'family' => 'Volet', 'type' => 'Action'),
            'HEATING_ON' => array('name' => 'Chauffage fil pilote Bouton ON', 'family' => 'Chauffage', 'type' => 'Action'),
            'HEATING_OFF' => array('name' => 'Chauffage fil pilote Bouton OFF', 'family' => 'Chauffage', 'type' => 'Action'),
            'HEATING_STATE' => array('name' => 'Chauffage fil pilote Etat', 'family' => 'Chauffage', 'type' => 'Info'),
            'HEATING_OTHER' => array('name' => 'Chauffage fil pilote Bouton', 'family' => 'Chauffage', 'type' => 'Action'),
            'LOCK_STATE' => array('name' => 'Serrure Etat', 'family' => 'Ouvrant', 'type' => 'Info'),
            'LOCK_OPEN' => array('name' => 'Serrure Bouton Ouvrir', 'family' => 'Ouvrant', 'type' => 'Action'),
            'LOCK_CLOSE' => array('name' => 'Serrure Bouton Fermer', 'family' => 'Ouvrant', 'type' => 'Action'),
            'SIREN_STATE' => array('name' => 'Sirène Etat', 'family' => 'Sécurité', 'type' => 'Info'),
            'SIREN_OFF' => array('name' => 'Sirène Bouton Off', 'family' => 'Sécurité', 'type' => 'Action'),
            'SIREN_ON' => array('name' => 'Sirène Bouton On', 'family' => 'Sécurité', 'type' => 'Action'),
            'THERMOSTAT_STATE' => array('name' => 'Thermostat Etat (BINAIRE) (pour Plugin Thermostat uniquement)', 'family' => 'Thermostat', 'type' => 'Info'),
            'THERMOSTAT_TEMPERATURE' => array('name' => 'Thermostat Température ambiante', 'family' => 'Thermostat', 'type' => 'Info'),
            'THERMOSTAT_SET_SETPOINT' => array('name' => 'Thermostat consigne ', 'family' => 'Thermostat', 'type' => 'Action'),
            'THERMOSTAT_SETPOINT' => array('name' => 'Thermostat consigne', 'family' => 'Thermostat', 'type' => 'Info'),
            'THERMOSTAT_SET_MODE' => array('name' => 'Thermostat Mode (pour Plugin Thermostat uniquement)', 'family' => 'Thermostat', 'type' => 'Action'),
            'THERMOSTAT_MODE' => array('name' => 'Thermostat Mode (pour Plugin Thermostat uniquement)', 'family' => 'Thermostat', 'type' => 'Info'),
            'THERMOSTAT_SET_LOCK' => array('name' => 'Thermostat Verrouillage (pour Plugin Thermostat uniquement)', 'family' => 'Thermostat', 'type' => 'Action'),
            'THERMOSTAT_SET_UNLOCK' => array('name' => 'Thermostat Déverrouillage (pour Plugin Thermostat uniquement)', 'family' => 'Thermostat', 'type' => 'Action'),
            'THERMOSTAT_LOCK' => array('name' => 'Thermostat Verrouillage (pour Plugin Thermostat uniquement)', 'family' => 'Thermostat', 'type' => 'Info'),
            'THERMOSTAT_TEMPERATURE_OUTDOOR' => array('name' => 'Thermostat Température Exterieur (pour Plugin Thermostat uniquement)', 'family' => 'Thermostat', 'type' => 'Info'),
            'THERMOSTAT_STATE_NAME' => array('name' => 'Thermostat Etat (HUMAIN) (pour Plugin Thermostat uniquement)', 'family' => 'Thermostat', 'type' => 'Info'),
            'CAMERA_UP' => array('name' => 'Mouvement caméra vers le haut', 'family' => 'Caméra', 'type' => 'Action'),
            'CAMERA_DOWN' => array('name' => 'Mouvement caméra vers le bas', 'family' => 'Caméra', 'type' => 'Action'),
            'CAMERA_LEFT' => array('name' => 'Mouvement caméra vers le gauche', 'family' => 'Caméra', 'type' => 'Action'),
            'CAMERA_RIGHT' => array('name' => 'Mouvement caméra vers le droite', 'family' => 'Caméra', 'type' => 'Action'),
            'CAMERA_ZOOM' => array('name' => 'Zoom caméra vers l\'avant', 'family' => 'Caméra', 'type' => 'Action'),
            'CAMERA_DEZOOM' => array('name' => 'Zoom caméra vers l\'arrière', 'family' => 'Caméra', 'type' => 'Action'),
            'CAMERA_STOP' => array('name' => 'Stop caméra', 'family' => 'Caméra', 'type' => 'Action'),
            'CAMERA_PRESET' => array('name' => 'Preset caméra', 'family' => 'Caméra', 'type' => 'Action'),
            'CAMERA_URL' => array('name' => 'URL caméra', 'family' => 'Caméra', 'type' => 'info'),
            'CAMERA_RECORD_STATE' => array('name' => 'État enregistrement caméra', 'family' => 'Caméra', 'type' => 'info'),
            'CAMERA_RECORD' => array('name' => 'Enregistrement caméra', 'family' => 'Caméra', 'type' => 'Action'),
            'CAMERA_TAKE' => array('name' => 'Snapshot caméra', 'family' => 'Caméra', 'type' => 'Action'),
            'MODE_STATE' => array('name' => 'Mode', 'family' => 'Mode', 'type' => 'Info'),
            'MODE_SET_STATE' => array('name' => 'Mode', 'family' => 'Mode', 'type' => 'Action'),
            'ALARM_STATE' => array('name' => 'Alarme état', 'family' => 'Sécurité', 'type' => 'Info', 'noapp' => true),
            'ALARM_MODE' => array('name' => 'Alarme mode', 'family' => 'Sécurité', 'type' => 'Info', 'noapp' => true),
            'ALARM_ENABLE_STATE' => array('name' => 'Alarme état activée', 'family' => 'Sécurité', 'type' => 'Info', 'noapp' => true),
            'ALARM_ARMED' => array('name' => 'Alarme armée', 'family' => 'Sécurité', 'type' => 'Action', 'noapp' => true),
            'ALARM_RELEASED' => array('name' => 'Alarme libérée', 'family' => 'Sécurité', 'type' => 'Action', 'noapp' => true),
            'ALARM_SET_MODE' => array('name' => 'Alarme Mode', 'family' => 'Sécurité', 'type' => 'Action', 'noapp' => true),
            'WEATHER_TEMPERATURE' => array('name' => 'Météo Température', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_HUMIDITY' => array('name' => 'Météo Humidité', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_PRESSURE' => array('name' => 'Météo Pression', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_WIND_SPEED' => array('name' => 'Météo vitesse du vent', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_WIND_DIRECTION' => array('name' => 'Météo direction du vent', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_SUNSET' => array('name' => 'Météo lever de soleil', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_SUNRISE' => array('name' => 'Météo coucher de soleil', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MIN' => array('name' => 'Météo Température min', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MAX' => array('name' => 'Météo Température max', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION' => array('name' => 'Météo condition', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION_ID' => array('name' => 'Météo condition (id)', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MIN_1' => array('name' => 'Météo Température min j+1', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MAX_1' => array('name' => 'Météo Température max j+1', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION_1' => array('name' => 'Météo condition j+1', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION_ID_1' => array('name' => 'Météo condition (id) j+1', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MIN_2' => array('name' => 'Météo Température min j+2', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MAX_2' => array('name' => 'Météo condition j+1 max j+2', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION_2' => array('name' => 'Météo condition j+2', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION_ID_2' => array('name' => 'Météo condition (id) j+2', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MIN_3' => array('name' => 'Météo Température min j+3', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MAX_3' => array('name' => 'Météo Température max j+3', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION_3' => array('name' => 'Météo condition j+3', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION_ID_3' => array('name' => 'Météo condition (id) j+3', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MIN_4' => array('name' => 'Météo Température min j+4', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_TEMPERATURE_MAX_4' => array('name' => 'Météo Température max j+4', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION_4' => array('name' => 'Météo condition j+4', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WEATHER_CONDITION_ID_4' => array('name' => 'Météo condition (id) j+4', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'GB_OPEN' => array('name' => 'Portail ou garage bouton d\'ouverture', 'family' => 'Ouvrant', 'type' => 'Action'),
            'GB_CLOSE' => array('name' => 'Portail ou garage bouton de fermeture', 'family' => 'Ouvrant', 'type' => 'Action'),
            'GB_TOGGLE' => array('name' => 'Portail ou garage bouton toggle', 'family' => 'Ouvrant', 'type' => 'Action'),
            'BARRIER_STATE' => array('name' => 'Portail état ouvrant', 'family' => 'Ouvrant', 'type' => 'Info'),
            'GARAGE_STATE' => array('name' => 'Garage état ouvrant', 'family' => 'Ouvrant', 'type' => 'Info'),
            'POWER' => array('name' => 'Puissance Electrique', 'family' => 'Generic', 'type' => 'Info'),
            'CONSUMPTION' => array('name' => 'Consommation Electrique', 'family' => 'Generic', 'type' => 'Info', 'noapp' => true),
            'TEMPERATURE' => array('name' => 'Température', 'family' => 'Environnement', 'type' => 'Info'),
            'AIR_QUALITY' => array('name' => 'Qualité de l\'air', 'family' => 'Environnement', 'type' => 'Info'),
            'DEPTH' => array('name' => 'Profondeur', 'family' => 'Generic', 'type' => 'Info'),
            'BRIGHTNESS' => array('name' => 'Luminosité', 'family' => 'Environnement', 'type' => 'Info'),
            'PRESENCE' => array('name' => 'Présence', 'family' => 'Environnement', 'type' => 'Info'),
            'BATTERY' => array('name' => 'Batterie', 'family' => 'Batterie', 'type' => 'Info', 'noapp' => true),
            'BATTERY_CHARGING' => array('name' => 'Batterie en charge', 'family' => 'Batterie', 'type' => 'Info', 'noapp' => true),
            'SMOKE' => array('name' => 'Détection de fumée', 'family' => 'Environnement', 'type' => 'Info'),
            'FLOOD' => array('name' => 'Inondation', 'family' => 'Sécurité', 'type' => 'Info'),
            'HUMIDITY' => array('name' => 'Humidité', 'family' => 'Environnement', 'type' => 'Info'),
            'UV' => array('name' => 'UV', 'family' => 'Environnement', 'type' => 'Info', 'noapp' => true),
            'OPENING' => array('name' => 'Porte', 'family' => 'Ouvrant', 'type' => 'Info'),
            'OPENING_WINDOW' => array('name' => 'Fenêtre', 'family' => 'Ouvrant', 'type' => 'Info'),
            'SABOTAGE' => array('name' => 'Sabotage', 'family' => 'Sécurité', 'type' => 'Info'),
            'CO2' => array('name' => 'CO2 (ppm)', 'family' => 'Environnement', 'type' => 'Info', 'noapp' => true),
            'CO' => array('name' => 'CO (ppm)', 'family' => 'Environnement', 'type' => 'Info', 'noapp' => true),
            'VOLTAGE' => array('name' => 'Tension', 'family' => 'Electricité', 'type' => 'Info', 'noapp' => true),
            'NOISE' => array('name' => 'Son (dB)', 'family' => 'Environnement', 'type' => 'Info', 'noapp' => true),
            'PRESSURE' => array('name' => 'Pression', 'family' => 'Environnement', 'type' => 'Info', 'noapp' => true),
            'RAIN_CURRENT' => array('name' => 'Pluie (mm/h)', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'RAIN_TOTAL' => array('name' => 'Pluie (accumulation)', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WIND_SPEED' => array('name' => 'Vent (vitesse)', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'WIND_DIRECTION' => array('name' => 'Vent (direction)', 'family' => 'Météo', 'type' => 'Info', 'noapp' => true),
            'SHOCK' => array('name' => 'Choc', 'family' => 'Sécurité', 'type' => 'Info'),
            'DISTANCE' => array('name' => 'Distance', 'family' => 'Generic', 'type' => 'Info'),
            'BUTTON' => array('name' => 'Bouton', 'family' => 'Generic', 'type' => 'Info'),
            'VOLUME' => array('name' => 'Volume', 'family' => 'Multimédia', 'type' => 'Info'),
            'SET_VOLUME' => array('name' => 'Volume', 'family' => 'Multimédia', 'type' => 'Action'),
            'CHANNEL' => array('name' => 'Chaine', 'family' => 'Multimédia', 'type' => 'Info'),
            'SET_CHANNEL' => array('name' => 'Chaine', 'family' => 'Multimédia', 'type' => 'Action'),
            'UPLOAD' => array('name' => 'Fichier', 'family' => 'Generic', 'type' => 'Action'),
            'GENERIC_INFO' => array('name' => ' Générique', 'family' => 'Generic', 'type' => 'Info'),
            'GENERIC_ACTION' => array('name' => ' Générique', 'family' => 'Generic', 'type' => 'Action'),
            'DONT' => array('name' => 'Ne pas tenir compte de cette commande', 'family' => 'Generic', 'type' => 'All'),
        ),
        'type' => array(
            'info' => array(
                'name' => 'Info',
                'subtype' => array(
                    'numeric' => array(
                        'name' => 'Numérique',
                        'configuration' => array(
                            'minValue' => array('visible' => true),
                            'maxValue' => array('visible' => true),
                            'listValue' => array('visible' => false)),
                        'unite' => array('visible' => true),
                        'isHistorized' => array('visible' => true, 'timelineOnly' => false, 'canBeSmooth' => true),
                        'display' => array(
                            'invertBinary' => array('visible' => false),
                            'icon' => array('visible' => true, 'parentVisible' => true),
                        ),
                    ),
                    'binary' => array(
                        'name' => 'Binaire',
                        'configuration' => array(
                            'minValue' => array('visible' => false),
                            'maxValue' => array('visible' => false),
                            'listValue' => array('visible' => false)),
                        'unite' => array('visible' => false),
                        'isHistorized' => array('visible' => true, 'timelineOnly' => false, 'canBeSmooth' => false),
                        'display' => array(
                            'invertBinary' => array('visible' => true, 'parentVisible' => true),
                            'icon' => array('visible' => true, 'parentVisible' => true),
                        ),
                    ),
                    'string' => array(
                        'name' => 'Autre',
                        'configuration' => array(
                            'minValue' => array('visible' => false),
                            'maxValue' => array('visible' => false),
                            'listValue' => array('visible' => false)),
                        'unite' => array('visible' => true),
                        'isHistorized' => array('visible' => true, 'timelineOnly' => true, 'canBeSmooth' => false),
                        'display' => array(
                            'invertBinary' => array('visible' => false),
                            'icon' => array('visible' => true, 'parentVisible' => true),
                        ),
                    ),
                ),
            ),
            'action' => array(
                'name' => 'Action',
                'subtype' => array(
                    'other' => array(
                        'name' => 'Défaut',
                        'configuration' => array(
                            'minValue' => array('visible' => false),
                            'maxValue' => array('visible' => false),
                            'listValue' => array('visible' => false)),
                        'unite' => array('visible' => false),
                        'isHistorized' => array('visible' => false),
                        'display' => array(
                            'invertBinary' => array('visible' => false),
                            'icon' => array('visible' => true, 'parentVisible' => true),
                        ),
                    ),
                    'slider' => array(
                        'name' => 'Curseur',
                        'configuration' => array(
                            'minValue' => array('visible' => true),
                            'maxValue' => array('visible' => true),
                            'listValue' => array('visible' => false)),
                        'unite' => array('visible' => false),
                        'isHistorized' => array('visible' => false),
                        'display' => array(
                            'invertBinary' => array('visible' => false),
                            'icon' => array('visible' => true, 'parentVisible' => true),
                        ),
                    ),
                    'message' => array(
                        'name' => 'Message',
                        'configuration' => array(
                            'minValue' => array('visible' => false),
                            'maxValue' => array('visible' => false),
                            'listValue' => array('visible' => false)),
                        'unite' => array('visible' => false),
                        'isHistorized' => array('visible' => false),
                        'display' => array(
                            'invertBinary' => array('visible' => false),
                            'icon' => array('visible' => true, 'parentVisible' => true),
                        ),
                    ),
                    'color' => array(
                        'name' => 'Couleur',
                        'configuration' => array(
                            'minValue' => array('visible' => false),
                            'maxValue' => array('visible' => false),
                            'listValue' => array('visible' => false)),
                        'unite' => array('visible' => false),
                        'isHistorized' => array('visible' => false),
                        'display' => array(
                            'invertBinary' => array('visible' => false),
                            'icon' => array('visible' => true, 'parentVisible' => true),
                        ),
                    ),
                    'select' => array(
                        'name' => 'Liste',
                        'configuration' => array(
                            'minValue' => array('visible' => false),
                            'maxValue' => array('visible' => false),
                            'listValue' => array('visible' => true)),
                        'unite' => array('visible' => false),
                        'isHistorized' => array('visible' => false),
                        'display' => array(
                            'invertBinary' => array('visible' => false),
                            'icon' => array('visible' => true, 'parentVisible' => true),
                        ),
                    ),
                    'upload' => array(
                        'name' => 'Fichier',
                        'configuration' => array(
                            'minValue' => array('visible' => false),
                            'maxValue' => array('visible' => false),
                            'listValue' => array('visible' => true)),
                        'unite' => array('visible' => false),
                        'isHistorized' => array('visible' => false),
                        'display' => array(
                            'invertBinary' => array('visible' => false),
                            'icon' => array('visible' => true, 'parentVisible' => true),
                        ),
                    )
                ),
            ),
        ),
        'widget' => array(
            'action' => array(
                'other' => array(
                    'light' => array('template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_yellow icon nextdom-lumiere-on\'></i>', '#_icon_off_#' => '<i class=\'icon nextdom-lumiere-off\'></i>')),
                    'circle' => array('template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'fas fa-circle\'></i>', '#_icon_off_#' => '<i class=\'far fa-circle\'></i>')),
                    'fan' => array('template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon nextdom-ventilo\'></i>', '#_icon_off_#' => '<i class=\'fas fa-times\'></i>')),
                    'garage' => array('template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_green icon nextdom-garage-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-garage-ouvert\'></i>')),
                    'lock' => array('template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_green icon nextdom-lock-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon_orange icon nextdom-lock-ouvert\'></i>')),
                    'prise' => array('template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon nextdom-prise\'></i>', '#_icon_off_#' => '<i class=\'fas fa-times\'></i>')),
                    'sprinkle' => array('template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_blue icon nature-watering1\'></i>', '#_icon_off_#' => '<i class=\'fas fa-times\'></i>')),
                    'timeLight' => array('template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon_yellow icon nextdom-lumiere-on\'></i>', '#_icon_off_#' => '<i class=\'icon jeedom-lumiere-off\'></i>')),
                    'timeCircle' => array('template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'fas fa-circle\'></i>', '#_icon_off_#' => '<i class=\'fas fa-circle-thin\'></i>')),
                    'timeFan' => array('template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon nextdom-ventilo\'></i>', '#_icon_off_#' => '<i class=\'fas fa-times\'></i>')),
                    'timeGarage' => array('template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon_green icon nextdom-garage-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon jeedom-garage-ouvert\'></i>')),
                    'timeLock' => array('template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon nextdom-lock-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon nextdom-lock-ouvert\'></i>')),
                    'timePrise' => array('template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon nextdom-prise\'></i>', '#_icon_off_#' => '<i class=\'fas fa-times\'></i>')),
                    'timeSprinkle' => array('template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon_blue icon nature-watering1\'></i>', '#_icon_off_#' => '<i class=\'fas fa-times\'></i>')),
                ),
                'slider' => array(
                    'light' => array('template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_yellow icon nextdom-lumiere-on\'></i>', '#_icon_off_#' => '<i class=\'icon nextdom-lumiere-off\'></i>')),
                    'timeLight' => array('template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon_yellow icon nextdom-lumiere-on\'></i>', '#_icon_off_#' => '<i class=\'icon jeedom-lumiere-off\'></i>')),
                )
            ),
            'info' => array(
                'binary' => array(
                    'line' => array('template' => 'tmpliconline', 'replace' => array('#_icon_on_#' => '<i class=\'icon_green fas fa-check\'></i>', '#_icon_off_#' => '<i class=\'icon_red fas fa-times\'></i>')),
                    'alert' => array('icon' => '🚨', 'template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_green fas fa-check\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-alerte2\'></i>')),
                    'door' => array('icon' => '🚪', 'template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_green icon nextdom-porte-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-porte-ouverte\'></i>')),
                    'heat' => array('icon' => '🔥', 'template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_red icon nextdom-feu\'></i>', '#_icon_off_#' => '<i class=\'fas fa-times\'></i>')),
                    'light' => array('icon' => '💡', 'template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_yellow icon nextdom-lumiere-on\'></i>', '#_icon_off_#' => '<i class=\'icon nextdom-lumiere-off\'></i>')),
                    'lock' => array('icon' => '🔒', 'template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon nextdom-lock-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-lock-ouvert\'></i>')),
                    'presence' => array('icon' => '🙋', 'template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_green fas fa-check\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-mouvement\'></i>')),
                    'prise' => array('icon' => '🔌', 'template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon nextdom-prise\'></i>', '#_icon_off_#' => '<i class=\'fas fa-times\'></i>')),
                    'window' => array('icon' => '◫', 'template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_green icon nextdom-fenetre-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-fenetre-ouverte\'></i>')),
                    'shutter' => array('template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_green icon nextdom-volet-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-volet-ouvert\'></i>')),
                    'flood' => array('icon' => '💧', 'template' => 'tmplicon', 'replace' => array('#_icon_on_#' => '<i class=\'icon_green fas fa-tint-slash\'></i>', '#_icon_off_#' => '<i class=\'icon_blue fas fa-tint\'></i>')),
                    'timeDoor' => array('icon' => '🚪🕑', 'template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon_green icon nextdom-porte-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-porte-ouverte\'></i>')),
                    'timePresence' => array('icon' => '🙋🕑', 'template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon_green fas fa-check\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-mouvement\'></i>')),
                    'timeWindow' => array('icon' => '◫🕑', 'template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon_green icon nextdom-fenetre-ferme\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-fenetre-ouverte\'></i>')),
                    'timeAlert' => array('icon' => '🚨🕑', 'template' => 'tmplicon', 'replace' => array('#_time_widget_#' => '1', '#_icon_on_#' => '<i class=\'icon_green fas fa-check\'></i>', '#_icon_off_#' => '<i class=\'icon_red icon nextdom-alerte2\'></i>')),
                ),
                'numeric' => array(
                    'heatPiloteWire' => array('template' => 'tmplmultistate',
                        'test' => array(
                            array('operation' => '#value# == 3', 'state_light' => '<i class=\'icon nextdom-pilote-eco\'></i>'),
                            array('operation' => '#value# == 2', 'state_light' => '<i class=\'icon nextdom-pilote-off\'></i>'),
                            array('operation' => '#value# == 1', 'state_light' => '<i class=\'icon nextdom-pilote-hg\'></i>'),
                            array('operation' => '#value# == 0', 'state_light' => '<i class=\'icon nextdom-pilote-conf\'></i>')
                        )),
                    'timeHeatPiloteWire' => array('template' => 'tmplmultistate',
                        'replace' => array('#_time_widget_#' => '1'),
                        'test' => array(
                            array('operation' => '#value# == 3', 'state_light' => '<i class=\'icon nextdom-pilote-eco\'></i>'),
                            array('operation' => '#value# == 2', 'state_light' => '<i class=\'icon nextdom-pilote-off\'></i>'),
                            array('operation' => '#value# == 1', 'state_light' => '<i class=\'icon nextdom-pilote-hg\'></i>'),
                            array('operation' => '#value# == 0', 'state_light' => '<i class=\'icon nextdom-pilote-conf\'></i>')
                        )),
                    'heatPiloteWireQubino' => array('template' => 'tmplmultistate',
                        'test' => array(
                            array('operation' => '#value# >= 51 && #value# <= 99', 'state_light' => '<i class=\'icon nextdom-pilote-conf\'></i>'),
                            array('operation' => '#value# >= 41 && #value# <= 50', 'state_light' => '<i class=\'icon nextdom-pilote-conf\'></i><sup style=\'font-size: 0.3em; margin-left: 1px\'>-1</sup>'),
                            array('operation' => '#value# >= 31 && #value# <= 40', 'state_light' => '<i class=\'icon nextdom-pilote-conf\'></i><sup style=\'font-size: 0.3em; margin-left: 1px\'>-2</sup>'),
                            array('operation' => '#value# >= 21 && #value# <= 30', 'state_light' => '<i class=\'icon nextdom-pilote-eco\'></i>'),
                            array('operation' => '#value# >= 11 && #value# <= 20', 'state_light' => '<i class=\'icon nextdom-pilote-hg\'></i>'),
                            array('operation' => '#value# >= 0 && #value# <= 10', 'state_light' => '<i class=\'icon nextdom-pilote-off\'></i>'),
                        )),
                    'timeHeatPiloteWireQubino' => array('template' => 'tmplmultistate',
                        'replace' => array('#_time_widget_#' => '1'),
                        'test' => array(
                            array('operation' => '#value# >= 51 && #value# <= 99', 'state_light' => '<i class=\'icon nextdom-pilote-conf\'></i>'),
                            array('operation' => '#value# >= 41 && #value# <= 50', 'state_light' => '<i class=\'icon nextdom-pilote-conf\'></i><sup style=\'font-size: 0.3em; margin-left: 1px\'>-1</sup>'),
                            array('operation' => '#value# >= 31 && #value# <= 40', 'state_light' => '<i class=\'icon nextdom-pilote-conf\'></i><sup style=\'font-size: 0.3em; margin-left: 1px\'>-2</sup>'),
                            array('operation' => '#value# >= 21 && #value# <= 30', 'state_light' => '<i class=\'icon nextdom-pilote-eco\'></i>'),
                            array('operation' => '#value# >= 11 && #value# <= 20', 'state_light' => '<i class=\'icon nextdom-pilote-hg\'></i>'),
                            array('operation' => '#value# >= 0 && #value# <= 10', 'state_light' => '<i class=\'icon nextdom-pilote-off\'></i>'),
                        )
                    )
                )
            )
        )
    )
);
global $JEEDOM_INTERNAL_CONFIG;
$JEEDOM_INTERNAL_CONFIG = $NEXTDOM_INTERNAL_CONFIG;
