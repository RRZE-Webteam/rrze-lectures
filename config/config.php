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
        // Activate/Deactivate Template Cache. If tis is false, cache will only 
        // used for DIP-Data, but not for the generated HTML
        'Transient_Output' => false,
        // Transient Time for raw data we got from the API
        'Transient_Seconds_Rawdata' =>  6 * HOUR_IN_SECONDS,
        // maximum number for limit at requesting data from api for one round
        'DIPAPI_limit_max'  => 50,
        // maximal number of total results
        'DIPAPI_totalentries_max' => 250,
        // maximal timeout in seconds we give the api
        'DIPAPI_timeout'  => 5,
        // maximum Btyes for the response
        'DIPAPI_max_response_bytes' =>  1024 * 1024,
        // Output Template Formats
        'template_formats' => [
            
            'tabs'      => [
                'name'  => 'tabs',
                    // definiert das Verzeichnis des Templates und
                    // den Filename der Basis-Template. Hier:  tabs/tabs.php
                    // Die Basistempöate wird immer geladen und ausgeführt.
                    
                'contains'  => [
                    // Wenn der Array nicht leer ist, kann man hier subtemplates 
                    // definieren, die geladen und interpretiert werden und
                    // deren Inhalt dann als Variable in dem Basistemplate 
                    // eingefügt werden.
                    'base'  => [
                        'name'      => 'base',
                            // definiert den Templatenamen im Verzeichnis
                            // und auch wie dessen Inhalte dann mit {{=variable}}
                            // in der darüber liegenden Template File addressiert 
                            // werden
                            // Darf nicht identisch sein mit dem Namen
                            // des Verzeichnisses und der Haupt-Templatefile
                        'attribut'  => 'base',
                            // Attribut zum schalten via show/hide
                            // Sollte nicht den selben Namen tragen wie andere Attribute
                            // aus der API. Aber kann durchaus :) 
                        'default'   => true,                      
                            // Definiert ob per Default sichtbar oder nicht
                    ],
                    'termine'  => [
                        'name'      => 'termine',
                        'attribut'  => 'termine',
                        'default'   => true,                      
                    ],
                     'module'  => [
                        'name'      => 'module',
                        'attribut'  => 'module',
                        'default'   => false,                      
                    ],
                    'orgunit'  => [
                        'name'      => 'orgunit',
                        'attribut'  => 'orgunit',
                        'default'   => false,                      
                    ]
                ]
            ],
            'linklist'  => [
                'name'  => 'linklist',
                'contains'  => []
            ],
            'degree-linklist'  => [
                'name'  => 'degree-linklist',
                'contains'  => []
            ]  
        ],
        'errors'    => [
            'default'   => __('No matching entries found.', 'rrze-lectures'),
            'norequired'  => __('Required attributes mssing. Either enter the FAUOrg number it in the settings of rrze-lectures or use one of the shortcode attributes: fauorgnr, lecture_name, lecturer_idm or lecturer_identifier', 'rrze-lectures'),
            'apikeymissing' => __('DIP API-Key Error! Um eine DIP API Key zu erhalten, rufen Sie bitte die Seite <code>https://gitos.rrze.fau.de/fauapi/keyman</code> auf und flgenden den dortigen Schritten.', 'rrze-lectures'),
            'oversize'  => __('We got too much data from the API. Please narrow your search filter!', 'rrze-lectures'),
            '204'       => __('No matching entries found.', 'rrze-lectures'),
            '206'       => __('The server is delivering only part of the resource, therfor no matching entries was found.', 'rrze-lectures'),
            '403'       => __('The request contained valid data and was understood by the server, but the server is refusing action. This may be due to the user not having the necessary permissions for a resource or needing an account of some sort, or attempting a prohibited action.', 'rrze-lectures'),
            '404'       => __('No matching entries found.', 'rrze-lectures'),
            '503'       => __('Die Schnittstelle zu Campo wird im Moment gewartet. In Kürze wird die Ausgabe wieder wie gewünscht erfolgen. Es ist keinerlei Änderung Ihrerseits nötig.<br><br><a href="https://www.campo.fau.de/qisserver/pages/cm/exa/coursecatalog/showCourseCatalog.xhtml?_flowId=showCourseCatalog-flow&_flowExecutionKey=e1s1">Hier ist das Vorlesungsverzeichnis auf Campo einsehbar.</a>', 'rrze-lectures'),
            '504'       => __('The server did not receive a timely response.','rrze-lectures'),
        ]
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
                'label' => __('FAUorg Number', 'rrze-lectures'),
                'desc' => __('To receive lectures from another department use the attribute <strong>fauorgnr</strong> in the shortcode. F.e. [lectures fauorgnr="1513001700"]', 'rrze-lectures'),
                'placeholder' => '',
                'type' => 'text',
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => 'AddFAUORG',
                'label' => __('Use FAUorg Number', 'rrze-lectures'),
                'desc' => __('Set if the  FAUorg number set above, is always added to any shortcode request or is only used if no other required parameter was given in the shortcode.', 'rrze-lectures'),
                'placeholder' => 'add',
                'type' => 'radio',
                'default' => 'add',
                'options'   => array(
                    'add'  => __('Always add FAUorg number to query, unless the shortcode-parameter fauorg was filled', 'rrze-lectures'),
                    'ifrequired'  => __('Add FAUorg number only if other required search fields are missing', 'rrze-lectures'),
                ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            
            
            
            [
                'name' => 'limit_lv',
                'label' => __('Maximum number of lectures', 'rrze-lectures'),
                'desc' => __('Warning! If you increase this > 15 be aware that the website\'s loading time will increase dramatically and might lead to an HTTP 502 error.', 'rrze-lectures'),
                'placeholder' => '',
                'type' => 'text',
                'default' => '15',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ];

    
      /* 
         *  Der untere Teil nun doch nach Überlegungen deaktiviert auch auch die Settings im Backend für Deutsch und Englishc weggemacht.
         *   Erwägungsgründe:
         *   - Wenn wir überall egal was ist, immer dieselbe Antwort im Fehlerfall geben, können wir
         *     niht kenntlich machen, ob keine Daten kommen, weil es zu viele Daten waren, weil die Anfrage falsch war 
         *     oder die API überlastet ist u.a. 
         *     Der Webmaster hat somit keine einfache Möglichkeit den Shortcode zu reparieren oder die ANfrage zu verfeinern, 
         *    wenn er nicht weiß´aus welcher Richtung das Problem kam.
         *   - Wenn wir aber jeden Fehlerfall mit eigenen de/en - Fehlermeldungen anspeichern lassen, wird allein dadurch das
         *     Setting voll und somit für den unbedarften Anwender erscheint das alles komplexer als es ist.
         *     ZUdem sind das Fehlermeldungen, die normalerweise ohnehin nicht nach aussen sollten.
         *   - Nach aussen hin, zum Leser der Website wäre in der Tat nur eine Meldung azseichend.
         *     Aber diese kann durchaus so bleiben wie sie ist.
         * 
         * Daher:
         *    Wir entfernen doch lieber die Settings aus dem Backend  
         *    Es gibt verschiedene Fehlermeldungen die wir per Default vorgeben 
         *    Wenn der Webmaster  für den Leser der Website eine eigene Meldung vorgeben möchte,
         *     dann kann und soll er das individuell pro Shortcode machen.
         *     Dann entfällt auch er AUfwand das zweisprachig zu sichern, denn jeder Shortcode wird ja 
         *     bereits in einem definierten SPrachkontext geführt.
         * 
         * Unabhängig davon: Der vorherige Ansatz und die Lösung das mit den Settings so zu machen mit der
         * Sprachabhängigkeit war genial und high sophisticated.

      
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
   */
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
                'label' => __('FAUorg Number', 'rrze-lectures'),
                'type' => 'string',
            ],
            'lecture_name' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lectures name', 'rrze-lectures'),
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
                'label' => __('Type of the event. For example: Vorlesung', 'rrze-lectures'),
                'type' => 'string',
            ],
            'degree' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Degree', 'rrze-lectures'),
                // Studiengang
                'type' => 'string',
            ],
            'degree_key' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Degree Key. Example: 65|079|-|-|H|2010|E|P|V|7| ', 'rrze-lectures'),
                // Studiengang HIS Schlüssel, bspw. "65|079|-|-|H|2010|E|P|V|7|"
                'type' => 'string',
            ],
            'orgunit' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Filter for Campo Orgunit', 'rrze-lectures'),
                'type' => 'string',
            ],
            'sem' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Semester for example SoSe2023 or WiSe2024', 'rrze-lectures'),
                'type' => 'string',
            ],
            'teaching_language' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Teaching language (for example "en" or "de" or "en, de, fr"', 'rrze-lectures'),
                'type' => 'string',
            ],
            'display_language' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Display language (for example "en" or "de" or "fr". If this attribute is not given, websites language is used. In every case fallback is "de".)', 'rrze-lectures'),
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
                        'val' => __('do not filter', 'rrze-lectures'),
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
            'show' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Show', 'rrze-lectures'),
                'type' => 'string',
            ],
            'hstart' => [
                'default' => 2,
                'field_type' => 'text',
                'label' => __('Headlines size', 'rrze-lectures'),
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
                        'val' => __('FAU', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'med',
                        'val' => __('Med', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'nat',
                        'val' => __('Nat', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'phil',
                        'val' => __('Phil', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'rw',
                        'val' => __('RW', 'rrze-lectures'),
                    ],
                    [
                        'id' => 'tf',
                        'val' => __('TF', 'rrze-lectures'),
                    ],
                ],
            ],
            'max' => [
                'default' => '',
                'min' => 1,
                'max' => 10,
                'step' => '1',
                'field_type' => 'number',
            ],
            'nodata' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Own errormessage in case an error occurs', 'rrze-lectures'),
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