<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;
use RRZE\Lectures\Cache;
// use RRZE\Lectures\Translator;
use RRZE\Lectures\Template;

/**
 * Shortcode
 */
class Shortcode {
    /**
     * Der vollständige Pfad- und Dateiname der Plugin-Datei.
     * @var string
     */

    protected $websiteLocale;
    protected $websiteLanguage;
    protected $bLanguageSwitched = false;
    protected $pluginFile;
    protected $options;
    protected $atts;
    protected $oDIP;
    protected $Transient_Output;
    protected $DPIAPI_limit_max;
    protected $DPIAPI_totalentries_max;
    protected $RequiredAttributs;

    public $use_cache;

    private $settings = '';
    private $aAllowedColors = [];
    private $aAllowedFormats = [];

    private $aLanguages = [];



    /**
     * Variablen Werte zuweisen.
     * @param string $pluginFile Pfad- und Dateiname der Plugin-Datei
     */
    public function __construct($pluginFile, $settings) {
        $this->websiteLocale    = get_locale();
        $this->websiteLanguage  = substr($this->websiteLocale, 0, 2);
        $this->pluginFile       = $pluginFile;
        $this->settings         = Config::getShortcodeSettings();
        $this->settings         = $this->settings['lectures'];
        $this->options          = get_option('rrze-lectures');
        $constants              = Config::getConstants();
        $this->aAllowedColors   = $constants['colors'];
        $this->aAllowedFormats  = $constants['formats'];
        $this->aLanguages       = $constants['langcodes'];
        $this->Transient_Output = $constants['Transient_Output'];
        $this->DPIAPI_limit_max = $constants['DIPAPI_limit_max'];
        $this->DPIAPI_totalentries_max = $constants['DIPAPI_totalentries_max'];
        $this->RequiredAttributs= array(
            "fauorgnr",
                // Eine oder mehrere FAUORG NUmmern
            "lecture_name", "lecture_id",
                // Lehrveranstaltung(en)
            "lecturer_idm", "lecturer_identifier",
                // Dozent(en)
            "degree", "degree_key",
                // Ein oder mehrere Studiengänge
            "module_name", "module_id"
                // Ein oder mehrere Module
        );

        $this->use_cache = true;

        add_action('admin_enqueue_scripts', [$this, 'enqueueGutenberg']);
        add_action('init', [$this, 'initGutenberg']);
    }

    /**
     * Er wird ausgeführt, sobald die Klasse instanziiert wird.
     * @return void
     */
    public function onLoaded() {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_shortcode('lectures', [$this, 'shortcodeLectures']);
    }

    public function enqueueScripts() {
        wp_register_style('rrze-lectures', plugins_url('css/rrze-lectures.css', plugin_basename($this->pluginFile)));
    }


    /*
     * Enqueue Scripts und CSS von RRZE-Elements
     * Diese Funktion soll aufgerufen werden, wenn wir erfolgreich Content
     * erhalten haben und dieses zurück liefern.
     * Zwar wird bei do_shortcode() das entsprechende automatisch enqueued und
     * der HTML-Code erzeugt; Dies gilt allerdings nicht bei Ausgaben aus
     * dem Cache. Hier muss das notwendige enqueue extra aufgerufen werden.
     */
    private function enqueue_rrze_elements() {
        wp_enqueue_style('fontawesome');
        wp_enqueue_style('rrze-elements');
        wp_enqueue_script('rrze-accordions');


        if ($this->atts['format'] == 'tabs') {
            wp_enqueue_script('rrze-tabs');
        }
        return;
    }


    /**
     * Generieren Sie die Shortcode-Ausgabe
     * @param  array   $atts Shortcode-Attribute
     * @return string Gib den Inhalt zurück
     */
    public function shortcodeLectures(array|string $atts, ?string $content = null): string {
        if (Functions::isMaintenanceMode()) {
            return Functions::getErrorMessage("503");
        }
        $tsStart = microtime(true);

        $this->logInfo('START rrze-lectures shortcodeLectures()', $tsStart);

        if ((!empty($atts['nocache'])) || (isset($_GET['nocache']))) {
            $this->use_cache = false;
        }

        // merge given attributes with default ones
        $atts_default = array();
        foreach ($this->settings as $k => $v) {
            if ($k != 'block') {
                $atts_default[$k] = $v['default'];
            }
        }
        $this->normalize(shortcode_atts($atts_default, $atts));

        Functions::log('info', 'Shortcode attributes normalized', ['attributes' => $this->atts]);

        $cache = new Cache();
        if (($this->use_cache) && ($this->Transient_Output==true)) {
            $this->atts['cachetype'] = 'html';
            $content = $cache->get_cached_data($this->atts);

            if (!empty($content)) {
                $this->enqueue_rrze_elements();
                wp_enqueue_style('rrze-lectures');
                $output = "\n" . $content;
                return $output;

            } else {
                Functions::log('notice', 'No HTML cache found', ['attributes' => $this->atts]);
            }
        } else {
            Functions::log('notice', 'No HTML cache used', ['attributes' => $this->atts]);
        }

        if (!$this->isRequiredExists()) {
            return Functions::getErrorMessage("norequired");
        }


        $data = [];
        if ($this->use_cache) {
            $this->atts['cachetype'] = 'data';
            $data = $cache->get_cached_data($this->atts);

            if (!empty($data)) {
                Functions::log('notice', 'Returned cache for data', ['attributes' => $this->atts]);
                $this->logInfo('Cache for data found and returned', $tsStart);

            } else {
                Functions::log('notice', 'No cache for data found', ['attributes' => $this->atts]);
            }
        }

        if (empty($data)) {
            Functions::log('info', 'Generating API request to get new data', ['attributes' => $this->atts]);


                // Hinweis zur Umsetzung:
                // Wenn wir nach Kursen zu Studiengängen suchen, degree="..."
                // dann haben wir große Probleme bei der Rückgabe nach
                // Degrees zu suchen, diese sauber zu filtern, zu sorieren etc.
                // Dies verursacht signifikanten RAM-Verbrauch und Performance.
                // Im Worst Case führt dies zu FATAL Errors oder sehr lange
                // laufenden Prozessen.
                //
                // Daher werden wir erstmal der Einfachheit halber bei dem Attribut
                // degree="" nur ein Wert erlauben.
                //
                // Wir bauen daher hier die Shortcodes der EInzelaufrufe und führen
                // sie mit do_shortcode aus. Dies erspart uns auch ein Workaround
                // mit einem Selbstaufruf der Shortcode-Funktion.
                //
                // Jeder API Request wird je Degree gecacht. Wenn also irgendwo
                // auf der Website schonmal nur nach den einen Degree gesucht
                // wurde (z.v. weil wir hier nur ein Index haben wollen und woanders
                // dann Detaildaten), dann haben wir den schon im Cache
                // Somit entspricht eine eingabe zweier oder mehrere Degrees
                // den hintereinander ausführen deselben Shortcodes mit
                // unterschiedlichen einzelnen degree=""-Einträgen.
                //
                // Somit ersparen wir uns auch die Sortierung.
                // Die Reihenfolge der Ausgaben wird bestimmt durch Reihenfolge
                // im degree=""-Attribut.


                if (!empty($this->atts['degree'])) {
                       // group by degree
                       $aGivenDegrees = array_map('trim', explode(',', $this->atts['degree']));

                       if (count($aGivenDegrees)>1) {
                            $output = '';
                            $foundshow = false;
                            foreach ($aGivenDegrees as $searchdegree) {

                                $output .= '<h2>'.$searchdegree.'</h2>';

                                $shortcode = '[lectures';
                                foreach ($atts as $name => $value) {
                                    if ($name !== 'degree') {
                                        $shortcode .= ' '.$name.'="'.$value.'"';
                                    }
                                    if ($name == 'show') {
                                       $value .= ',degree';
                                       $foundshow = true;
                                    }
                                }
                                if (! $foundshow) {
                                    $shortcode .= ' show="degree"';
                                }
                                $shortcode .= ' degree="'.$searchdegree.'"';
                                $shortcode .= ']';
                                $output .= $shortcode. "\n";
                            }


                            return do_shortcode($output);
                       }
                }

            $this->logInfo('Set params for DIP', $tsStart);
            $this->oDIP = new DIPAPI();

            // First we check for the amount of data we may get.
            // If its too much, we break here
            $datacount = $this->oDIP->getDataCount('educationEvents',$this->atts);
            if (!$datacount['valid']) {
                Functions::log(
                    'warning',
                    'Invalid data count response',
                    [
                        'content' => $datacount['content'],
                        'code' => $datacount['code'],
                        'request_string' => $datacount['request_string'] ?? '',
                    ]
                );
                $output = Functions::getErrorMessage($datacount['code'],$this->atts['nodata']);
                return $output;
            } else {
                if ((is_array($datacount['content'])) && (isset($datacount['content']['pagination'])) ) {

                    if (($datacount['content']['pagination']['total'] > $this->DPIAPI_totalentries_max)) {
                        Functions::log(
                            'warning',
                            'Too many results',
                            [
                                'total' => $datacount['content']['pagination']['total'],
                                'max' => $this->DPIAPI_totalentries_max,
                                'request_string' => $datacount['request_string'] ?? '',
                            ]
                        );
                        $output = Functions::getErrorMessage('oversize',$this->atts['nodata']);
                        return $output;
                    }
                }
            }

            // ok, the test-querys showed a valid response, so that we can now
            // ask for the whole data vault :)

            $dipParams = $this->oDIP->getAPIParamsPrefix($this->atts);


            $page = 1;
            $response = $this->oDIP->getResponse('educationEvents', $dipParams . '&page='.$page);

            if (!empty($response['request_string'])) {
                Functions::log('info', 'DIP request sent', ['request_string' => $response['request_string']]);
            }


            if (!$response['valid']) {
                // The query for the first page failed...
                Functions::log(
                    'warning',
                    'Invalid response',
                    [
                        'content' => $response['content'],
                        'code' => $response['code'],
                        'request_string' => $response['request_string'] ?? '',
                    ]
                );
                $output = Functions::getErrorMessage($response['code'],$this->atts['nodata']);
                return $output;

            } else {
                $data = $response['content']['data'];

                if ((is_array($response['content'])) && (isset($response['content']['pagination'])) && (isset($response['content']['pagination']['remaining']))) {
                    $countentries = intval($this->atts['max']);
                    if ($response['content']['pagination']['remaining'] > 0) {


                        while (($response['content']['pagination']['remaining'] > 0) && ($countentries < $this->DPIAPI_totalentries_max)) {
                            $page++;
                            $response = $this->oDIP->getResponse('educationEvents', $dipParams . '&page='.$page);


                            if (isset($response['content']['data'])) {
                                $data = array_merge($response['content']['data'], $data);
                                $countentries +=  intval($response['content']['pagination']['count']);

                            }
                        }

                        if (($countentries >= $this->DPIAPI_totalentries_max) && ($response['content']['pagination']['remaining'] > 0)) {
                             $remain = $response['content']['pagination']['remaining'];
                            Functions::log(
                                'warning',
                                'Too many paged results, ignoring remaining entries',
                                [
                                    'pagination' => $response['content']['pagination'],
                                    'count_entries' => $countentries,
                                    'max' => $this->DPIAPI_totalentries_max,
                                    'remaining' => $remain,
                                ]
                            );
                        }

                    }
                }

            }
            if (empty($data)) {
                Functions::log('notice', 'Empty data response', ['response' => $response]);
                $output = Functions::getErrorMessage(204,$this->atts['nodata']);
                return $output;
            }

            if (empty($data)) {
                $output = Functions::getErrorMessage(204,$this->atts['nodata']);
                return $output;
            }

            // set cache for data
            if ($this->use_cache) {
                $this->atts['cachetype'] = 'data';
                $cache->set_cached_data($data, $this->atts);
            }
        }

        // Ok, nun endlich haben wir alle Daten, sie sind plausibel, sie
        // sind vollständig und hoffentlich nutzbar.
        // Also machen wir was draus.

        Functions::log('info', 'Data from API loaded', ['data' => $data]);

        // Sanitize Data Fields to avoid surprising gifts from the api
        Sanitizer::sanitizeLectures($data, $this->aLanguages);

        // Init Data Formater
        $formatData = new FormatData($this->atts['display_language']);

        // Set translateable fields  to the desired output language
        $formatData->setTranslations($data);

        // Group Data by Event-Types
        $data = $formatData->groupbyEventType($data);
        Functions::log('info', 'Data grouped by type', ['data' => $data]);

        // Remove duplicate Courses
        $data = $formatData->removeDuplicateCourses($data);

        // Sortiere nach den EventTypen
        $data = $formatData->sortEventTypeArraybyEvent($data);

        // Sortiere innerhalb der EventTypen
        $data = $formatData->sortEventTypeArraybyAttribut($data);

        // Suche generische URLs für den Event aus den Coursedaten
        $data = $formatData->searchPortalURLsforEvent($data);
        Functions::log('info', 'Data prepared', ['data' => $data]);

        $this->logInfo('Group by eventtype completed', $tsStart);


        if (empty($data)) {
            // Hierhin sollten wir normalerweise nicht kommen; Wenn doch, dann
            // stimmte etwas mit den Daten von der API nicht;
            // Zum Beispiel waren sie nicht vollständig.

            Functions::log('warning', 'Partly data after formatting', ['data' => $data]);
            $output = Functions::getErrorMessage(206,$this->atts['nodata']);
            return $output;
        }

        Functions::log('info', 'Data valid, rendering template');


        $aDegree = [];
        $aDegree[] = $data;


        $iCnt = 0;
        $first = true;
        $compo_link = '';
        foreach ($aDegree as $degree => $aData) {
            foreach ($aData as $type => $aEntries) {
                $i = 1;
                foreach ($aEntries as $title => $aDetails) {

                    $aDegree[$degree][$type][$title]['first'] = $first;
                    $aDegree[$degree][$type][$title]['last'] = false;
                    $aDegree[$degree][$type][$title]['type_title'] = ($i == 1 && empty($this->atts['hide_type']) ? $type : false);
                    $aDegree[$degree][$type][$title]['type_start'] = ($aDegree[$degree][$type][$title]['type_title'] ? true : false);
                    $aDegree[$degree][$type][$title]['type_end'] = ($i == count($aEntries) ? true : false);


                     // get Campo Link from first Course
      //             $first_course = array_key_first($aDetails['providerValues']['courses']);
      //             if (isset($aDetails['providerValues']['courses'][$first_course]['url'])) {
      //                  $compo_link = $aDetails['providerValues']['courses'][$first_course]['url'];
      //             }
      //             $aDegree[$degree][$type][$title]['campo_url'] = $compo_link;



                    $i++;
                    $first = false;
                    $iCnt++;
                }
            }
        }
        $aDegree[$degree][$type][$title]['last'] = true;

        $this->logInfo('Pre template', $tsStart);

        $templateparser = new Template();
        Functions::log('info', 'Data for template', ['format' => $this->atts['format'], 'data' => $aDegree]);


        foreach ($aDegree as $degree => $aData) {
            foreach ($aData as $type => $aEntries) {
                foreach ($aEntries as $title => $aDetails) {


                    $content .= $templateparser->parseSetting($this->atts['format'], $aDetails, $this->atts);

                }
            }
        }
        // unset($aDegree); // free memory
        $aDegree = null;

        $this->logInfo('Template parsed', $tsStart);

        if (empty($this->atts['hide_accordion']) || ($this->atts['format'] == 'tabs')) {
            // in any case tabs.php uses shortcodes
            $content = do_shortcode($content);
        }

        $this->logInfo('do_shortcode() executed', $tsStart);

        // set cache
        if (($this->use_cache) && ($this->Transient_Output==true)) {
            $this->atts['cachetype'] = 'html';
            $cache->set_cached_data($content, $this->atts);
            $this->logInfo('Cache set', $tsStart);
        }


        if ($this->bLanguageSwitched) {
            switch_to_locale($this->websiteLocale);
        }
        $this->logInfo('END rrze-lectures shortcodeLectures()', $tsStart);
        wp_enqueue_style('rrze-lectures');
        $output = "\n" . $content;
        return $output;
//             return wpautop($output);
    }

    private function logInfo(string $message, float $tsStart = 0): void {
        $context = [];

        if ($tsStart > 0) {
            $context['execution_time'] = sprintf('%.2f', microtime(true) - $tsStart) . ' s';
        }

        Functions::log('info', $message, $context);
    }



    /*
     * Check if at least one of the required parameters was set
     * Otherwiese this function will return false
     */
    private function isRequiredExists(): bool {
        $required = $this->RequiredAttributs;

        $found = false;
        foreach ($required as $field) {
            if (!empty($this->atts[$field])) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    /*
     * Sanitize und normalisiere Attribute
     * Wenn nötig befülle diese mit Defaults
     * TODO: Move in Sanitizer.php
     */
    private function normalize(array $atts): array  {
        // sanatize all fields
        foreach ($atts as $key => $val) {
            $atts[$key] = sanitize_text_field($val);
        }

        // set display_language / default: website's language
        if (empty($atts['display_language'])) {
            $atts['display_language'] = $this->websiteLanguage;
        } else {
            $atts['display_language'] = strtolower(substr($atts['display_language'], 0, 2));

            // this plugin supports GERMAN and ENGLISH (see .mo/.po)
            switch ($atts['display_language']) {
                case 'de':
                    switch_to_locale('de_DE');
                    break;
                default:
                    switch_to_locale('en_US');
            }
            $this->bLanguageSwitched = true;
        }

        // dynamically generate hide vars
        $atts['hide_accordion'] = false;
        $atts['hide_degree_accordion'] = false;
        $atts['hide_type_accordion'] = false;

        if (!empty($atts['hide'])) {
            $aHide = explode(',', str_replace(' ', '', $atts['hide']));

            foreach ($aHide as $val) {
                $atts['hide_' . $val] = true;
            }
            if ($atts['hide_accordion']) {
                $atts['hide_degree_accordion'] = true;
                $atts['hide_type_accordion'] = true;
            }
            if ($atts['hide_degree_accordion'] && $atts['hide_type_accordion']) {
                $atts['hide_accordion'] = true;
            }
             unset($atts['hide']);
        }

        if (!empty($atts['show'])) {
            $aHide = explode(',', str_replace(' ', '', $atts['show']));

            foreach ($aHide as $val) {
                $atts['show_' . $val] = true;
            }
            unset($atts['show']);
        }


        if (!empty($atts['degree_key'])) {
            $atts['degree_key'] = trim($atts['degree_key']);
        }
        if (!empty($atts['degree'])) {
            $atts['degree'] = trim($atts['degree']);
        }


        if (!empty($atts['lecture_identifier'])) {
            $atts['lecture_identifier'] = trim($atts['lecture_identifier']);
        }
        if (!empty($atts['lecture_name'])) {
            $atts['lecture_name'] = trim($atts['lecture_name']);
        }
        if (!empty($atts['lecturer_identifier'])) {
            $atts['lecturer_identifier'] = trim($atts['lecturer_identifier']);
        }

        // sem
        if (empty($atts['sem'])) {
            $atts['sem'] = Functions::getSemester();
        } else {
            if (preg_match("/(\d{4})([w|s])/i", trim(strtolower($atts['sem'])), $matches)) {
                // YYYYs YYYYw YYYYS YYYYW
                $atts['sem'] = ($matches[2] == 'w' ? 'WiSe' : 'SoSe') . $matches[1];
            } elseif (preg_match("/(ss|ws)(\d{4})/i", trim(strtolower($atts['sem'])), $matches)) {
                // wsYYYY ssYYYY WSYYYY SSYYYY
                $atts['sem'] = ($matches[1] == 'ws' ? 'WiSe' : 'SoSe') . $matches[2];
            } elseif (!preg_match("/(sose|wise)(\d{4})/i", trim(strtolower($atts['sem'])), $matches)) {
                $aAllowedSem = ['-2', '-1', '+1', '1', '+2', '2'];
                if (in_array($atts['sem'], $aAllowedSem)) {
                    $atts['sem'] = (int) $atts['sem'];
                    $atts['sem'] = Functions::getSemester($atts['sem']);

                } else {
                    // invalid input
                    $atts['sem'] = Functions::getSemester();
                }
            }
        }



        if (!empty($atts['nodata']))  {
             $atts['nodata'] = esc_html($atts['nodata']);
        }



        // hstart
        $hstart = (empty($atts['hstart']) ? 2 : intval($atts['hstart']));
        $atts['degree_hstart'] = 0;
        $atts['type_hstart'] = 0;

        if ($atts['hide_degree_accordion']) {
            $atts['degree_hstart'] = $hstart;
            if (($atts['degree_hstart'] < 1) || ($atts['degree_hstart'] > 6)) {
                $atts['degree_hstart'] = 2;
            }
        }

        if ($atts['hide_type_accordion']) {
            $atts['type_hstart'] = ($atts['hide_degree_accordion'] ? $hstart + 1 : $hstart);
            if (($atts['type_hstart'] < 1) || ($atts['type_hstart'] > 6)) {
                $atts['type_hstart'] = ($atts['hide_degree_accordion'] ? 2 : 3);
            }
        }


        $atts['format'] = (in_array($atts['format'], $this->aAllowedFormats) ? $atts['format'] : 'linklist');
        $atts['color'] = (in_array($atts['color'], $this->aAllowedColors) ? $atts['color'] : 'fau');

        if (($atts['format']=='linklist') && (!empty($atts['degree'])) && (!empty($atts['degree_key']))) {
            $atts['format'] = 'degree-linklist';
        }


        $atts['max'] = (!empty($atts['max']) && $atts['max'] < $this->DPIAPI_limit_max ? $atts['max'] : $this->DPIAPI_limit_max);
         // prevent HTTP 502 & too high loading time
        if (empty($atts['degree']) && empty($atts['degree_key']) && empty($atts['type'])){
             $atts['max'] = ( $atts['max'] > $this->options['basic_limit_lv'] ? $this->options['basic_limit_lv'] :  $atts['max']);
        }


        // Now move it all into the object
        $this->atts = $atts;

        // If required Paras are missing, but the backend settings contains
        // a fauorg-value, we add this in the atts


        if ((!$this->isRequiredExists()) && !empty($this->options['basic_FAUOrgNr'])) {
            $this->atts['fauorgnr'] = $this->options['basic_FAUOrgNr'];
        } elseif (!empty($this->options['basic_FAUOrgNr'])
            && $this->options['basic_AddFAUORG'] !== 'ifrequired'
            && (empty($this->atts['fauorgnr']))) {
               $this->atts['fauorgnr'] = $this->options['basic_FAUOrgNr'];
        }
        if ($this->atts['fauorgnr'] == '-') {
            $this->atts['fauorgnr'] = '';
        }




        return $this->atts;
    }


    public function isGutenberg(): bool
    {
        $postID = get_the_ID();
        if ($postID && !use_block_editor_for_post($postID)) {
            return false;
        }
        return true;
    }

    private function makeDropdown(string $id, string $label, array $aData, ?string $all = null): array
    {
        $ret = [
            'id' => $id,
            'label' => $label,
            'field_type' => 'select',
            'default' => '',
            'type' => 'string',
            'items' => ['type' => 'text'],
            'values' => [['id' => '', 'val' => (empty($all) ? __('-- All --', 'rrze-lectures') : $all)]],
        ];

        foreach ($aData as $id => $name) {
            $ret['values'][] = [
                'id' => $id,
                'val' => htmlspecialchars(str_replace('"', "", str_replace("'", "", $name)), ENT_QUOTES, 'UTF-8'),
            ];
        }

        return $ret;
    }

    private function makeToggle(string $label): array
    {
        return [
            'label' => $label,
            'field_type' => 'toggle',
            'default' => true,
            'checked' => true,
            'type' => 'boolean',
        ];
    }

    public function initGutenberg() {
        if (!$this->isGutenberg()) {
            return;
        }

        $settings = $this->settings;
        unset($settings['show'], $settings['hide']);

        wp_register_script(
            'RRZE-Gutenberg',
            plugins_url('js/rrze-lectures-admin.js', $this->pluginFile),
            [
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-block-editor',
                'wp-editor',
                'wp-server-side-render',
            ],
            RRZE_PLUGIN_VERSION
        );

        wp_localize_script('RRZE-Gutenberg', $settings['block']['blockname'] . 'Config', $settings);

        $attributes = $settings;
        unset($attributes['block']);

        register_block_type(
            $settings['block']['blocktype'],
            [
                'editor_script' => 'RRZE-Gutenberg',
                'render_callback' => [$this, 'shortcodeOutput'],
                'attributes' => $attributes,
            ]
        );
    }

    public function shortcodeOutput(array $attributes): string {
        return $this->shortcodeLectures($attributes);
    }

    public function enqueueGutenberg() {
        if (!$this->isGutenberg()) {
            return;
        }

        wp_enqueue_script('RRZE-Gutenberg');
    }

}
