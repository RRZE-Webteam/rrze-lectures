<?php

namespace RRZE\Lectures\Config;

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
    );

    $aTmp = getShortcodeSettings();

    foreach($aTmp['lectures']['color']['values'] as $aVals){
        if (!empty($aVals['id'])){
            $options['colors'][] = $aVals['id'];
        }
    }

    foreach($aTmp['lectures']['format']['values'] as $aVals){
        $options['formats'][] = $aVals['id'];
    }

    return $options;
}

/**
 * Gibt die Einstellungen des Menus zurück.
 * @return array [description]
 */
function getMenuSettings()
{
    return [
        'page_title' => __('RRZE Lectures', 'rrze-lectures'),
        'menu_title' => __('RRZE Lectures', 'rrze-lectures'),
        'capability' => 'manage_options',
        'menu_slug' => 'rrze-lectures',
        'title' => __('RRZE Lectures Settings', 'rrze-lectures'),
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
            'title' => __('Lectures Settings', 'rrze-lectures'),
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
            // [
            //     'name' => 'url',
            //     'label' => __('Link to DIP', 'rrze-lectures'),
            //     'desc' => __('Hier fehlt noch der Link zur Doku oder doch zum Vorlesungsverzeichnis. Dieser Link wird nur als Anzeige verwendet, nicht als API', 'rrze-lectures'),
            //     'placeholder' => 'https://api.fau.de/pub/v1/vz/',
            //     'type' => 'text',
            //     'default' => 'https://api.fau.de/pub/v1/vz/',
            //     'sanitize_callback' => 'sanitize_url',
            // ],
            // [
            //     'name' => 'linkTxt',
            //     'label' => __('Text for the link to DIP (oder zum Vorlesungsverzeichnis', 'rrze-lectures'),
            //     'desc' => __('', 'rrze-lectures'),
            //     'placeholder' => __('', 'rrze-lectures'),
            //     'type' => 'text',
            //     'default' => __('Link to DIP', 'rrze-lectures'),
            //     'sanitize_callback' => 'sanitize_text_field',
            // ],
            [
                'name' => 'ApiKey',
                'label' => __('DIP API-Key', 'rrze-lectures'),
                'desc' => '',
                'placeholder' => '',
                'type' => 'text',
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => 'FAUOrgNr',
                'label' => __('FAU Org Number', 'rrze-lectures'),
                'desc' => __('To receive lectures from another department use the attribute <strong>fauorgnr</strong> in the shortcode. F.e. [lectures fauorgnr="123"]', 'rrze-lectures'),
                'placeholder' => '',
                'type' => 'text',
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => 'nodata',
                'label' => __('No data', 'rrze-lectures'),
                'desc' => __('This sentence will be returned by default if shortcode couln\'t find any data. You can use different messages in each shortcode by using the attribut nodata. F.e. [lectures nodata="No lectures found."]', 'rrze-lectures'),
                'placeholder' => '',
                'type' => 'text',
                'default' => __('No matching entries found.', 'rrze-lectures'),
                'sanitize_callback' => 'sanitize_text_field',
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
            'fauorgnr' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('FAU Org Nr', 'rrze-lectures'),
                'type' => 'string',
            ],
            'lecture_name' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecture\'s name', 'rrze-lectures'),
                'type' => 'string',
            ],
            // 'lecturer_name' => [
            //     'default' => '',
            //     'field_type' => 'text',
            //     'label' => __('Firstname, Lastname', 'rrze-lectures'),
            //     'type' => 'string',
            // ],
            'lecturer_idm' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecturer IdM', 'rrze-lectures'),
                'type' => 'string',
            ],
            'lecturer_identifier' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecturer identifier', 'rrze-lectures'),
                'type' => 'string',
            ],
            'type' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Type f.e. Vorlesung', 'rrze-lectures'),
                'type' => 'string',
            ],
            'degree' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Degree', 'rrze-lectures'), // Studiengang
                'type' => 'string',
            ],
            // 'order' => [
            //     'default' => '',
            //     'field_type' => 'text',
            //     'label' => __('Sort by type f.e. "vorl,ueb"', 'rrze-lectures'),
            //     'type' => 'string',
            // ],
            'sem' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Semester f.e. SoSe2023 or WiSe2024', 'rrze-lectures'),
                'type' => 'string',
            ],
            'lang' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Language', 'rrze-lectures'),
                'type' => 'string',
            ],
            // 'show' => [
            //     'default' => '',
            //     'field_type' => 'text',
            //     'label' => __('Show', 'rrze-lectures'),
            //     'type' => 'string',
            // ],
            'guest' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Visiting students', 'rrze-lectures'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => '',
                        'val' => __('don\'t filter', 'rrze-lectures'),
                    ],
                    [
                        'id' => 1,
                        'val' => __('Suitable for visiting students', 'rrze-lectures'),
                    ],
                    [
                        'id' => 0,
                        'val' => __('Not suitable for visiting students', 'rrze-lectures'),
                    ],
                ],
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
            'format' => [
                'default' => 'linklist',
                'field_type' => 'text',
                'label' => __('Format', 'rrze-lectures'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => 'linklist',
                        'val' => 'linklist',
                    ],
                    // [
                    //     'id' => 'table',
                    //     'val' => 'table',
                    // ],
                ],
            ],
            'color' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Color of accordions', 'rrze-lectures'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => '',
                        'val' => __('Kein', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'med',
                        'val' => __('Med: Blau', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'phil',
                        'val' => __('Phil: Ocker', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'tf',
                        'val' => __('TF: Silbern', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'nat',
                        'val' => __('Nat: Meeresgrün', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'rw',
                        'val' => __('RW: Bordeaurot', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'fau',
                        'val' => __('FAU: Dunkelblau', 'rrze-lectures'),
                    ],
                ],
            ],
            'max' => [
                'default' => '',
                'min' => 1,
                'max' => 100,
                'step' => '1',
                'field_type' => 'number',
            ],

            'nodata' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Show', 'rrze-lectures'),
                'type' => 'string',
            ],
        ],
    ];
}

function getSanitizerMap(){
    return [
        'startdate' => 'date',
        'enddate' => 'date',
        'starttime' => 'time',
        'endtime' => 'time',
    ];
}
