<?php

namespace RRZE\DIP\Config;

defined('ABSPATH') || exit;

/**
 * Gibt der Name der Option zurück.
 * @return array [description]
 */
function getOptionName()
{
    return 'rrze-lectures';
}

function getConstants()
{
    $options = array(
        'fauthemes' => [
            'FAU-Einrichtungen',
            'FAU-Einrichtungen-BETA',
            'FAU-Medfak',
            'FAU-RWFak',
            'FAU-Philfak',
            'FAU-Techfak',
            'FAU-Natfak',
            'FAU-Blog',
            'FAU-Jobs',
        ],
        'rrzethemes' => [
            'RRZE 2019',
        ],
        'langcodes' => [
            "de" => __('German', 'rrze-lectures'),
            "en" => __('English', 'rrze-lectures'),
            "es" => __('Spanish', 'rrze-lectures'),
            "fr" => __('French', 'rrze-lectures'),
            "ru" => __('Russian', 'rrze-lectures'),
            "zh" => __('Chinese', 'rrze-lectures'),
        ],
        'colors' => [
            'med',
            'nat',
            'rw',
            'phil',
            'tk',
        ],
    );
    return $options;
}

/**
 * Gibt die Einstellungen des Menus zurück.
 * @return array [description]
 */
function getMenuSettings()
{
    return [
        'page_title' => __('RRZE DIP', 'rrze-lectures'),
        'menu_title' => __('RRZE DIP', 'rrze-lectures'),
        'capability' => 'manage_options',
        'menu_slug' => 'rrze-lectures',
        'title' => __('RRZE DIP Settings', 'rrze-lectures'),
    ];
}


/**
 * Gibt die Einstellungen der Optionsbereiche zurück.
 * @return array [description]
 */
function getSections()
{
    return [
        [
            'id' => 'basic',
            'title' => __('DIP Settings', 'rrze-lectures'),
        ],
    ];
}

/**
 * Gibt die Einstellungen der Optionsfelder zurück.
 * @return array [description]
 */
function getFields()
{
    return [
        'basic' => [
            [
                'name' => 'url',
                'label' => __('Link to DIP', 'rrze-lectures'),
                'desc' => __('Hier fehlt noch der Link zur Doku oder doch zum Vorlesungsverzeichnis. Dieser Link wird nur als Anzeige verwendet, nicht als API', 'rrze-lectures'),
                'placeholder' => 'https://api.fau.de/pub/v1/vz/',
                'type' => 'text',
                'default' => 'https://api.fau.de/pub/v1/vz/',
                'sanitize_callback' => 'sanitize_url',
            ],
            [
                'name' => 'linkTxt',
                'label' => __('Text for the link to DIP (oder zum Vorlesungsverzeichnis', 'rrze-lectures'),
                'desc' => __('', 'rrze-lectures'),
                'placeholder' => __('', 'rrze-lectures'),
                'type' => 'text',
                'default' => __('Link to DIP', 'rrze-lectures'),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => 'ApiKey',
                'label' => __('DIP ApiKey', 'rrze-lectures'),
                'desc' => __('If you are not using a multisite installation of Wordpress, contact rrze-integration@fau.de to receive this key.', 'rrze-settings'),
                'placeholder' => '',
                'type' => 'text',
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            // [
            //     'name' => 'DIPID',
            //     'label' => __('DIP ID', 'rrze-lectures'),
            //     'desc' => __('To receive lectures from another department use the attribute <strong>DIPID</strong> in the shortcode. F.e. [lectures DIPID="123"]', 'rrze-lectures'),
            //     'placeholder' => '',
            //     'type' => 'text',
            //     'default' => '',
            //     'sanitize_callback' => 'sanitize_text_field',
            // ],
            [
                'name' => 'hstart',
                'label' => __('Headline\'s size', 'rrze-lectures'),
                'desc' => __('Headlines start at this size.', 'rrze-lectures'),
                'min' => 2,
                'max' => 10,
                'step' => '1',
                'type' => 'number',
                'default' => '2',
                'sanitize_callback' => 'floatval',
            ],
        ],
    ];
}

/**
 * Gibt die Einstellungen der Parameter für Shortcode für den klassischen Editor und für Gutenberg zurück.
 * @return array [description]
 */

function getShortcodeSettings()
{
    return [
        'lectures' => [
            'block' => [
                'blocktype' => 'rrze-lectures/lecturelectures',
                'blockname' => 'lecturelectures',
                'title' => 'RRZE-DIP',
                'category' => 'widgets',
                'icon' => 'bank',
                'tinymce_icon' => 'paste',
            ],
            'id' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecture ID', 'rrze-lectures'),
                'type' => 'string',
            ],
            'name' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Firstname, Lastname', 'rrze-lectures'),
                'type' => 'string',
            ],
            'lectureid' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Person ID', 'rrze-lectures'),
                'type' => 'string',
            ],
            'lecturerID' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecturer ID', 'rrze-lectures'),
                'type' => 'string',
            ],
            'type' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Type f.e. vorl (=Vorlesung)', 'rrze-lectures'),
                'type' => 'string',
            ],
            'order' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Sort by type f.e. "vorl,ueb"', 'rrze-lectures'),
                'type' => 'string',
            ],
            'sem' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Semester f.e. 2020w', 'rrze-lectures'),
                'type' => 'string',
            ],
            'lang' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Language', 'rrze-lectures'),
                'type' => 'string',
            ],
            'show' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Show', 'rrze-lectures'),
                'type' => 'string',
            ],
            'hide' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Hide', 'rrze-lectures'),
                'type' => 'string',
            ],
            'hstart' => [
                'default' => 2,
                'field_type' => 'text',
                'label' => __('Headline\'s size', 'rrze-lectures'),
                'type' => 'number',
            ],
            'nodata' => [
                'default' => __('No matching entries found.', 'rrze-lectures'),
                'field_type' => 'text',
                'label' => __('Show', 'rrze-lectures'),
                'type' => 'string',
            ],
        ],
    ];
}