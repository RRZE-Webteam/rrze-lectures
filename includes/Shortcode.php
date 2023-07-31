<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;
use function RRZE\Lectures\Config\getShortcodeSettings;
use function RRZE\Lectures\Config\getConstants;
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
        $this->settings         = getShortcodeSettings();
        $this->settings         = $this->settings['lectures'];
        $this->options          = get_option('rrze-lectures');
        $constants              = getConstants();
        $this->aAllowedColors   = $constants['colors'];
        $this->aAllowedFormats  = $constants['formats'];
        $this->aLanguages       = $constants['langcodes'];
        
        $this->RequiredAttributs= array(
            "fauorgnr",
                // Eine oder mehrere FAUORG NUmmern
            "lecture_name", "lecture_id",
                // Lehrveranstaltung(en)
            "lecturer_idm", "lecturer_identifier",
                // Dozent(en)
            "degree", "degree_his_identifier",
                // Ein oder mehrere Studiengänge
            "module_name", "module_id"
                // Ein oder mehrere Module
        );
        
        $this->use_cache = true;
        
        add_action('admin_enqueue_scripts', [$this, 'enqueueGutenberg']);
        add_action('init', [$this, 'initGutenberg']);
        add_action('enqueue_block_assets', [$this, 'enqueueBlockAssets']);
        add_filter('mce_external_plugins', [$this, 'addMCEButtons']);
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
    public function shortcodeLectures(array|string $atts, string $content = NULL): string {
        if (Functions::isMaintenanceMode()) {
            return 'Die Schnittstelle zu Campo wird im Moment gewartet. In Kürze wird die Ausgabe wieder wie gewünscht erfolgen. Es ist keinerlei Änderung Ihrerseits nötig.<br><br><a href="https://www.campo.fau.de/qisserver/pages/cm/exa/coursecatalog/showCourseCatalog.xhtml?_flowId=showCourseCatalog-flow&_flowExecutionKey=e1s1">Hier ist das Vorlesungsverzeichnis auf Campo einsehbar.</a>';
        }
        $debugmsg = '';
        $tsStart = microtime(true);

        Debug::console_log('START rrze-lectures shortcodeLectures()', $tsStart);

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
        
        $debugmsg .= Debug::get_notice("Generated Attributs:<br>".Debug::get_html_var_dump($this->atts));
        
        $cache = new Cache();
        if (($this->use_cache) && ($this->options['Transient_Output']==true)) {
            $this->atts['cachetype'] = 'html';
            $content = $cache->get_cached_data($this->atts);
            
            if (!empty($content)) {
                $debugmsg .= Debug::get_notice("Returned Cache");
                Debug::console_log('Cache found and returned', $tsStart);
                
                $this->enqueue_rrze_elements();  
                wp_enqueue_style('rrze-lectures');
                $output = $debugmsg ."\n".$content;
                return $output;

            } else {
                 $debugmsg .= Debug::get_notice("No Cache found.");
            }
        } else {
            $debugmsg .= Debug::get_notice("No Cache used");
        }

        if (!$this->isRequiredExists()) {
            return __('FAU Org Nr is missing. Either enter it in the settings of rrze-lectures or use one of the shortcode attributes: fauorgnr, lecture_name, lecturer_idm or lecturer_identifier', 'rrze-lectures');
        }

        
        $data = [];
        if ($this->use_cache) {
            $this->atts['cachetype'] = 'data';
            $data = $cache->get_cached_data($this->atts);
            
            if (!empty($data)) {
                $debugmsg .= Debug::get_notice("Returned Cache for data");
                Debug::console_log('Cache for data found and returned', $tsStart);
                
            } else {
                $debugmsg .= Debug::get_notice("No Cache for data found.");
            }
        }
        
        if (empty($data)) {           
            $debugmsg .= Debug::get_notice("Generating API Request to get new data");
            
            if ($this->atts['format'] == 'tabs') {
                // prevent HTTP 502 & too high loading time
                if (empty($this->atts['degree']) && empty($this->atts['type'])){
                    $this->atts['max'] = ($this->atts['max'] > $this->options['basic_limit_lv'] ? $this->options['basic_limit_lv'] : $this->atts['max']);
                }
            }

            $this->oDIP = new DIPAPI();         
            $dipParams = $this->oDIP->getAPIParamsPrefix($this->atts);
            $debugmsg .= Debug::get_notice("dipParams: <br><pre>".$dipParams."</pre>");
            Debug::console_log('Set params for DIP', $tsStart);
            
            $page = 1;
            $response = $this->oDIP->getResponse('educationEvents', $dipParams . '&page='.$page);
            if (!empty($response['request_string'])) {
                $debugmsg .= Debug::get_notice("Request String: <br><pre>".$response['request_string']."</pre>");     
            }
            if (!$response['valid']) {
                $output = $debugmsg .$this->atts['nodata'];
                return $output;

            } else {
                $data = $response['content']['data'];

                if ($this->atts['max'] == 100) {
                    while ($response['content']['pagination']['remaining'] > 0) {
                        $page++;
                        $response = $this->oDIP->getResponse('educationEvents', $dipParams . '&page='.$page);
                        if (!empty($response['request_string'])) {
                            $debugmsg .= Debug::get_notice("Request String: <br><pre>".$response['request_string'].'</pre>');     
                        }
                        $data = array_merge($response['content']['data'], $data);
                        // $iAllEntries += $response['content']['pagination']['count'];
                    }
                }
            }
            if (empty($data)) {
                $output = $debugmsg .$this->atts['nodata'];
                return $output;
            }
            if (isset($_GET['debug']) && $_GET['debug'] == 'screen-raw') {
                $debugmsg .= Debug::get_html_var_dump($data);
            }

            Debug::console_log('pure DIP feedback before anything else ' . json_encode($data), $tsStart);
            Sanitizer::sanitizeLectures($data, $this->aLanguages);

           $translator = new Translator($this->atts['display_language']);
           $translator->setTranslations($data);

            Debug::console_log('after Sanitize and Translator ' . json_encode($data), $tsStart);

            if (empty($data)) {            
                $output = $debugmsg .$this->atts['nodata'];
                return $output;
            }
            
            // set cache for data
            if ($this->use_cache) {
                $this->atts['cachetype'] = 'data';
                $cache->set_cached_data($data, $this->atts);
            }
        }

       

       


        // group & sort
        $aData = [];

        // group by type
        foreach ($data as $nr => $aEntries) {
            $aData[$aEntries['providerValues']['event']['eventtype']][$aEntries['identifier']] = $aEntries;
        }
        // unset($data); // free memory 
        $data = null; // free memory see: https://stackoverflow.com/questions/584960/whats-better-at-freeing-memory-with-php-unset-or-var-null

        Debug::console_log('Group by eventtype completed', $tsStart);

        // sort
        $coll = collator_create('de_DE');

        // sort group
        $aTmp = [];
        if (!empty($this->atts['type'])) {
            // sort in order of $this->atts['type']
            $aGivenTypes = array_map('trim', explode(',', $this->atts['type']));

            foreach ($aGivenTypes as $givenType) {
                if (!empty($aData[$givenType])) {
                    $aTmp[$givenType] = $aData[$givenType];
                }
            }
            $aData = $aTmp;
        } else {
            // sort alphabetically by group
            $arrayKeys = array_keys($aData);
            collator_sort($coll, $arrayKeys);

            foreach ($arrayKeys as $key) {
                $aTmp[$key] = $aData[$key];
            }
            $aData = $aTmp;
        }

        // unset($aTmp); // free memory
        $aTmp = [];

       
        // sort entries
        $iAllEntries = 0;
        // $aTmp = [];

        foreach ($aData as $group => $aDetails) {
            $aTmp2 = [];
            foreach ($aDetails as $identifier => $aEntries) {
                $name = $aEntries['name'];
                $aTmp2[$name] = $aEntries;

                $aTmp3 = [];
                foreach ($aEntries['providerValues']['courses'] as $nr => $aCourses){
                    // BK 2023-06-28 : explicitely delete cancelled parallelgroups (API ignores this parameter sometimes)
                    if ((isset($aCourses['cancelled']) && ($aCourses['cancelled'] == false))) {
                        // sort by parallelgroup
                        $parallelgroup = $aCourses['parallelgroup'];
                        $aTmp3[$parallelgroup] = $aCourses;    
                    }

                }

                $arrayKeys = array_keys($aTmp3);
                if (count($arrayKeys) > 1){
                    array_multisort($arrayKeys, SORT_NATURAL | SORT_FLAG_CASE, $aTmp3);
                }else{
                    $aTmp3[array_key_first($aTmp3)]['parallelgroup'] = '';
                }

                $aTmp2[$name]['providerValues']['courses'] = $aTmp3;
                $aTmp3 = null;    
            }

            $arrayKeys = array_keys($aTmp2);
            array_multisort($arrayKeys, SORT_NATURAL | SORT_FLAG_CASE, $aTmp2);
            $iAllEntries += count($aTmp2);
            $aTmp[$group] = $aTmp2;
            // unset($aTmp2); // free memory
            $aTmp2 = null;
        }



        $aData = $aTmp;
        // unset($aTmp); // free memory
        $aTmp = null;
    // }

        // we filter by degree after all others to keep it simple and because there cannot be any lecture that doesn't fit to given degrees
        if (!empty($this->atts['degree'])) {
            // group by degree
            $aGivenDegrees = array_map('trim', explode(',', $this->atts['degree']));

            $aTmp = [];

            foreach ($aData as $type => $aVal) {
                foreach ($aVal as $title => $aLectures) {
                    foreach ($aLectures['providerValues']['modules'] as $mNr => $aModules) {
                        foreach ($aModules['module_cos'] as $cNr => $aDetails) {
                            if (in_array($aDetails['subject'], $aGivenDegrees)) {
                                $aTmp[$aDetails['subject']][$type][$title] = $aLectures;
                            }
                        }
                    }

                }
            }
            $aDegree = $aTmp;
            $aTmp = [];

            // sort by given degrees
            foreach ($aGivenDegrees as $degree) {
                if (!empty($aDegree[$degree])) {
                    $aTmp[$degree] = $aDegree[$degree];
                }
            }

            $aDegree = $aTmp;
            // unset($aTmp);
            $aTmp = null;
        }

        Debug::console_log('Sort completed', $tsStart);

        
        $aTmp = [];

        if (empty($aData)) {
            return $this->atts['nodata'];
        }

        if (!empty($this->atts['degree'])) {
            if (empty($aDegree)) {
                return $this->atts['nodata'];
            }

            foreach ($aDegree as $degree => $aTypes) {
                $start = true;
                foreach ($aTypes as $type => $aLectures) {
                    foreach ($aLectures as $title => $aDetails) {
                        $aDegree[$degree][$type][$title]['show_degree_title'] = (empty($this->atts['hide_degree']) && $start ? true : false);
                        $aDegree[$degree][$type][$title]['do_degree_accordion'] = !$this->atts['hide_degree_accordion'];
                        $aDegree[$degree][$type][$title]['degree_title'] = ($start ? $degree : false);
                        $aDegree[$degree][$type][$title]['degree_start'] = ($aDegree[$degree][$type][$title]['degree_title'] ? true : false);
                        $aDegree[$degree][$type][$title]['degree_end'] = false;
                        $aDegree[$degree][$type][$title]['degree_hstart'] = $this->atts['degree_hstart'];
                        $start = false;
                    }
                }
                $aDegree[$degree][$type][$title]['degree_end'] = true;
            }
        } else {
            $aDegree = [];
            $aDegree[] = $aData;
        }

        $iCnt = 0;
        $first = true;

        foreach ($aDegree as $degree => $aData) {
            foreach ($aData as $type => $aEntries) {
                $i = 1;
                foreach ($aEntries as $title => $aDetails) {
                    $aDegree[$degree][$type][$title]['do_accordion'] = !($this->atts['hide_degree_accordion'] && $this->atts['hide_type_accordion']);
                    $aDegree[$degree][$type][$title]['do_type_accordion'] = !$this->atts['hide_type_accordion'];
                    $aDegree[$degree][$type][$title]['first'] = $first;
                    $aDegree[$degree][$type][$title]['last'] = false;
                    $aDegree[$degree][$type][$title]['type_title'] = ($i == 1 && empty($this->atts['hide_type']) ? $type : false);
                    $aDegree[$degree][$type][$title]['type_start'] = ($aDegree[$degree][$type][$title]['type_title'] ? true : false);
                    $aDegree[$degree][$type][$title]['type_end'] = ($i == count($aEntries) ? true : false);
                    $aDegree[$degree][$type][$title]['color'] = $this->atts['color'];
                    $aDegree[$degree][$type][$title]['type_hstart'] = $this->atts['type_hstart'];
                    $aDegree[$degree][$type][$title]['hide_lecture_name'] = (!empty($this->atts['hide_lecture_name']) ? true : false); // 2DO: improve this: make "hide" 100% dynamically for templates, too
                    
                    
                     // get Campo Link from first Course
                   $first_course = array_key_first($aDetails['providerValues']['courses']);               
                   $compo_link = $aDetails['providerValues']['courses'][$first_course]['url'];
                   $aDegree[$degree][$type][$title]['campo_url'] = $compo_link;
                    
                    $i++;
                    $first = false;
                    $iCnt++;
                }
            }
        }
        $aDegree[$degree][$type][$title]['last'] = true;

        Debug::console_log('Pre tempate', $tsStart);

        $templateparser = new Template();

        foreach ($aDegree as $degree => $aData) {
            foreach ($aData as $type => $aEntries) {
                foreach ($aEntries as $title => $aDetails) {

                   $debugmsg .= Debug::get_notice("DATA:<br>".Debug::get_html_var_dump($aDetails));
                   $content .= $templateparser->parseSetting($this->atts['format'], $aDetails, $this->atts);
                   
                }
            }
        }
        // unset($aDegree); // free memory
        $aDegree = null;

        Debug::console_log('Template parsed', $tsStart);

        if (empty($this->atts['hide_accordion']) || ($this->atts['format'] == 'tabs')) {
            // in any case tabs.php uses shortcodes
            $content = do_shortcode($content);
        }

        Debug::console_log('do_shortcode() executed', $tsStart);

        // set cache
        if (($this->use_cache) && ($this->options['Transient_Output']==true)) {
            $this->atts['cachetype'] = 'html';
            $cache->set_cached_data($content, $this->atts);
            Debug::console_log('Cache set', $tsStart);
        }


        if ($this->bLanguageSwitched){
            switch_to_locale($this->websiteLocale);
        }
        Debug::console_log('END rrze-lectures shortcodeLectures()', $tsStart);
        wp_enqueue_style('rrze-lectures');
        $output = $debugmsg ."\n".$content;
        return $output;
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

        
        if (!empty($atts['degree'])) {
            $atts['degree'] = trim($atts['degree']);
        }
        
        
        if (!empty($atts['lecture_identifier'])) {
            $atts['lecture_identifier'] = trim($atts['lecture_identifier']);
        }        
        if (!empty($atts['lecture_name'])) {
            $atts['lecture_name'] = trim($atts['lecture_name']);
        }
        if (!empty($atts['lecture_identifier'])) {
            $atts['lecture_identifier'] = trim($atts['lecture_identifier']);
        }

        // sem
        if (empty($atts['sem'])) {
            $atts['sem'] = Functions::getSemester();
        } else {
            if (preg_match("/(\d{4})([w|s])/", trim(strtolower($atts['sem'])), $matches)) {
                // YYYYs YYYYw YYYYS YYYYW
                $atts['sem'] = ($matches[2] == 'w' ? 'WiSe' : 'SoSe') . $matches[1];
            } elseif (preg_match("/(ss|ws)(\d{4})/", trim(strtolower($atts['sem'])), $matches)) {
                // wsYYYY ssYYYY WSYYYY SSYYYY
                $atts['sem'] = ($matches[1] == 'ws' ? 'WiSe' : 'SoSe') . $matches[2];
            } elseif (!preg_match("/(sose|wise)(\d{4})/", trim(strtolower($atts['sem'])), $matches)) {
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

        // no data
        // 1. we allow nodata to be empty in case users don't want any output 
        // (in this case user has to delete nodata entries in settings assigned to website's language and -if attribute is used in shortcode- assigned to display_language)
        // 2. if shortcode attribute "nodata" is given => use it
        // 3. else => nodata is set to config's nodata assigned to shortcode attribute "display_language"
        // 4. if 3 is undefined =>  nodata is set to nodata assigned to website's language
        if (empty($atts['nodata']) && !empty($this->options['basic_nodata_' . $atts['display_language']])) {
            $atts['nodata'] = $this->options['basic_nodata_' . $atts['display_language']];
        } elseif (!empty($this->options['basic_nodata_' . $this->websiteLanguage])) {
            $atts['nodata'] = $this->options['basic_nodata_' . $this->websiteLanguage];
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
        $atts['max'] = (!empty($atts['max']) && $atts['max'] < 100 ? $atts['max'] : 100);

        // Now move it all into the object
        $this->atts = $atts;
        
        // If required Paras are missing, but the backend settings contains 
        // a fauorg-value, we add this in the atts
        
        if ((!$this->isRequiredExists()) && !empty($this->options['basic_FAUOrgNr'])) { 
            $this->atts['fauorgnr'] = $this->options['basic_FAUOrgNr'];
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

    private function makeDropdown(string $id, string $label, array $aData, string $all = null): array
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

    public function fillGutenbergOptions(array $aSettings): array
    {
        $this->dip = new DIPAPI($this->DIPURL, $this->DIPOrgNr, null);

        foreach ($aSettings as $task => $settings) {
            $settings['number']['default'] = $this->DIPOrgNr;

            // Mitarbeiter
            if (isset($settings['name'])) {
                unset($settings['name']);
                if ($task != 'lectures') {
                    unset($settings['id']);
                }
                $aPersons = [];
                $data = $this->getData('personAll');
                foreach ($data as $position => $persons) {
                    foreach ($persons as $person) {
                        $aPersons[$person['person_id']] = $person['lastname'] . (!empty($person['firstname']) ? ', ' . $person['firstname'] : '');
                    }
                }
                asort($aPersons);
                $settings['lectureid'] = $this->makeDropdown('lectureid', __('Person', 'rrze-lectures'), $aPersons);

            }

            // Lectures
            if (isset($settings['id'])) {
                $aLectures = [];
                $aLectureTypes = [];
                $aLectureLanguages = [];
                $data = $this->getData('lectureByDepartment');

                foreach ($data as $type => $lecs) {
                    foreach ($lecs as $lecture) {
                        $aLectureTypes[$lecture['lecture_type']] = $type;
                        if (!empty($lecture['leclanguage_long'])) {
                            $parts = explode(' ', $lecture['leclanguage_long']);
                            $aLectureLanguages[$lecture['leclanguage']] = $parts[1];
                        }
                        $aLectures[$lecture['lecture_id']] = $lecture['name'];
                    }
                }

                asort($aLectures);
                $settings['id'] = $this->makeDropdown('id', __('Lecture', 'rrze-lectures'), $aLectures);

                asort($aLectureTypes);
                $settings['type'] = $this->makeDropdown('type', __('Type', 'rrze-lectures'), $aLectureTypes);

                asort($aLectureLanguages);
                $settings['sprache'] = $this->makeDropdown('sprache', __('Language', 'rrze-lectures'), $aLectureLanguages);

                // Semester
                if (isset($settings['sem'])) {
                    $settings['sem'] = $this->makeDropdown('sem', __('Semester', 'rrze-lectures'), [], __('-- Current semester --', 'rrze-lectures'));
                    $thisSeason = (in_array(date('n'), [10, 11, 12, 1]) ? 'w' : 's');
                    $season = ($thisSeason = 's' ? 'w' : 's');
                    $nextYear = date("Y") + 1;
                    $settings['sem']['values'][] = ['id' => $nextYear . $season, 'val' => $nextYear . $season];
                    $lastYear = $nextYear - 2;
                    $settings['sem']['values'][] = ['id' => $lastYear . $season, 'val' => $lastYear . $season];

                    $minYear = (!empty($this->options['basic_semesterMin']) ? $this->options['basic_semesterMin'] : 1971);
                    for ($i = date("Y"); $i >= $minYear; $i--) {
                        $settings['sem']['values'][] = ['id' => $i . 's', 'val' => $i . ' ' . __('SS', 'rrze-lectures')];
                        $settings['sem']['values'][] = ['id' => $i . 'w', 'val' => $i . ' ' . __('WS', 'rrze-lectures')];
                    }
                }

                unset($settings['dozentid']);
            }

            // 2DO: we need document ready() or equal on React built elements to use onChange of DIP Org Nr. to refill dropdowns
            // unset($settings['number']);
            unset($settings['show']);
            unset($settings['hide']);

            $aSettings[$task] = $settings;
        }
        return $aSettings;
    }

    public function initGutenberg()
    {
        if (!$this->isGutenberg() || empty($this->DIPURL) || empty($this->DIPOrgNr)) {
            return;
        }
        // get prefills for dropdowns
        $aSettings = $this->fillGutenbergOptions($this->settings);

        foreach ($aSettings as $task => $settings) {
            // register js-script to inject php config to call gutenberg lib
            $editor_script = $settings['block']['blockname'] . '-block';
            $js = '../js/' . $editor_script . '.js';

            wp_register_script(
                $editor_script,
                plugins_url($js, __FILE__),
                array(
                    'RRZE-Gutenberg',
                ),
                null
            );

            wp_localize_script($editor_script, $settings['block']['blockname'] . 'Config', $settings);

            // register block
            register_block_type(
                $settings['block']['blocktype'],
                array(
                    'editor_script' => $editor_script,
                    'render_callback' => [$this, 'shortcodeOutput'],
                    'attributes' => $settings,
                )
            );
        }
    }

    public function enqueueGutenberg()
    {
        if (!$this->isGutenberg()) {
            return;
        }

        wp_dequeue_script('RRZE-Gutenberg');
        // include gutenberg lib
        wp_enqueue_script(
            'RRZE-Gutenberg',
            plugins_url('../js/gutenberg.js', __FILE__),
            array(
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-editor',
            ),
            null
        );
    }

    public function enqueueBlockAssets()
    {
        wp_dequeue_script('RRZE-DIP-BlockJS');
        // include blockeditor JS
        wp_enqueue_script(
            'RRZE-DIP-BlockJS',
            plugins_url('../js/rrze-lectures-blockeditor.js', __FILE__),
            array(
                'jquery',
                'RRZE-Gutenberg',
            ),
            null
        );
    }


    public function addMCEButtons(array $pluginArray): array
    {
        if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
            $pluginArray['rrze_lecture_shortcode'] = plugins_url('../js/tinymce-shortcodes.js', plugin_basename(__FILE__));
        }
        return $pluginArray;
    }
}