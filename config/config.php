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

function getAvailableLanguages()
{
    if (class_exists('\RRZE\Multilang\Locale')) {
        // rrze-multilang is used
        return \RRZE\Multilang\Locale::getAvailableLanguages();
    } else {
        if (!function_exists('wp_get_available_translations')) {
            require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        }
        $locale = get_locale();
        $translations = wp_get_available_translations();

        return [
            $locale => $translations[$locale]['native_name'],
        ];
    }
}


// getLanguageNativeName
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
      	'Transient_Prefix' => 'rrze_lectures',
        // Default Transient time
        'Transient_Seconds' =>  3 * HOUR_IN_SECONDS,
        // Transient Time for generated Outpzut. Smaller as all. 10 - 60 minutes would fit
        'Transient_Seconds_Output' =>  15 * 60,
        // Transient Time for raw data we got from the API
        'Transient_Seconds_Rawdata' =>  6 * HOUR_IN_SECONDS,
    );

    $aTmp = getShortcodeSettings();

    foreach ($aTmp['lectures']['color']['values'] as $aVals) {
        if (!empty($aVals['id'])) {
            $options['colors'][] = $aVals['id'];
        }
    }

    foreach ($aTmp['lectures']['format']['values'] as $aVals) {
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
    $aRet = [
        'basic' => [
            [
                'name' => 'ApiKey',
                'label' => __('DIP API-Key', 'rrze-lectures'),
                'desc' => __('If you use the CMS offer by RRZE, you do not need to enter the DIP API key.', 'rrze-lectures'),
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
                'name' => 'limit_lv',
                'label' => __('Maximum number of lectures', 'rrze-lectures'),
                'desc' => __('Warning! If you increase this > 25 be aware that the website\'s loading time will increase dramatically and might lead to an HTTP 502 error.', 'rrze-lectures'),
                'placeholder' => '',
                'type' => 'text',
                'default' => '25',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ];

    // generate fields for nodata by available languages
    $aNodata = [];

    foreach (getAvailableLanguages() as $local => $lang) {
        $aNodata[] = [
            'name' => 'nodata_' . substr($local, 0, 2),
            'label' => __('No data', 'rrze-lectures') . ' (' . trim(preg_replace('/\((.+?)\)/', '', $lang)) . ')',
            'desc' => __('This sentence will be returned by default if shortcode couldn\'t find any data. You can use different messages in each shortcode by using the attribute nodata. F.e. [lectures nodata="No lectures found."]', 'rrze-lectures'),
            'placeholder' => '',
            'type' => 'text',
            'default' => __('No matching entries found.', 'rrze-lectures'),
            'sanitize_callback' => 'sanitize_text_field',
        ];
    }

    $aRet['basic'] = array_merge($aRet['basic'], $aNodata);

    return $aRet;
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
                'blocktype' => 'rrze-lectures/lectures',
                'blockname' => 'lectures',
                'title' => 'RRZE-Lectures',
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
            'lecture_identifier' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecture identifier', 'rrze-lectures'),
                'type' => 'string',
            ],
            'lecturer_identifier' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecturer identifier', 'rrze-lectures'),
                'type' => 'string',
            ],
            'lecturer_idm' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecturer IdM', 'rrze-lectures'),
                'type' => 'string',
            ],
            'type' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Type f.e. Lecture', 'rrze-lectures'),
                'type' => 'string',
            ],
            'degree' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Degree', 'rrze-lectures'),
                // Studiengang
                'type' => 'string',
            ],
            'sem' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Semester f.e. SoSe2023 or WiSe2024', 'rrze-lectures'),
                'type' => 'string',
            ],
            'teaching_language' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Teaching language (f.e. "en" or "de" or "en, de, fr"', 'rrze-lectures'),
                'type' => 'string',
            ],
            'display_language' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Display language (f.e. "en" or "de" or "fr". If this attribute is not given, website\'s language is used. In every case fallback is "de".)', 'rrze-lectures'),
                'type' => 'string',
            ],
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
                    [
                        'id' => 'tabs',
                        'val' => 'tabs',
                    ],
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
                        'val' => __('Default', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'fau',
                        'val' => __('FAU: Dunkelblau', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'med',
                        'val' => __('Med: Blau', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'nat',
                        'val' => __('Nat: Meeresgrün', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'phil',
                        'val' => __('Phil: Ocker', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'rw',
                        'val' => __('RW: Bordeaurot', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'tf',
                        'val' => __('TF: Silbern', 'rrze-lectures'),
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

function getSanitizerMap()
{
    return [
        'startdate' => 'date',
        'enddate' => 'date',
        'starttime' => 'time',
        'endtime' => 'time',
    ];
}