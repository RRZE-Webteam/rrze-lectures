<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;
use function RRZE\Lectures\Config\getShortcodeSettings;
use function RRZE\Lectures\Config\getConstants;
use RRZE\Lectures\Template;


/**
 * Shortcode
 */
class Shortcode
{
    /**
     * Der vollständige Pfad- und Dateiname der Plugin-Datei.
     * @var string
     */
    protected $pluginFile;
    protected $options;
    protected $show = [];
    protected $hide = [];
    protected $atts;
    protected $oDIP;
    private $settings = '';
    private $aAllowedColors = [];
    private $aAllowedFormats = [];
    protected $noCache = false;


    /**
     * Variablen Werte zuweisen.
     * @param string $pluginFile Pfad- und Dateiname der Plugin-Datei
     */
    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = getShortcodeSettings();
        $this->settings = $this->settings['lectures'];
        $this->options = get_option('rrze-lectures');
        $constants = getConstants();
        $this->aAllowedColors = $constants['colors'];
        $this->aAllowedFormats = $constants['formats'];

        add_action('admin_enqueue_scripts', [$this, 'enqueueGutenberg']);
        add_action('init', [$this, 'initGutenberg']);
        add_action('enqueue_block_assets', [$this, 'enqueueBlockAssets']);
        add_filter('mce_external_plugins', [$this, 'addMCEButtons']);
    }

    /**
     * Er wird ausgeführt, sobald die Klasse instanziiert wird.
     * @return void
     */
    public function onLoaded()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_shortcode('lectures', [$this, 'shortcodeLectures']);
    }

    public function enqueueScripts()
    {
        wp_register_style('rrze-lectures', plugins_url('css/rrze-lectures.css', plugin_basename($this->pluginFile)));
        if (file_exists(WP_PLUGIN_DIR . '/rrze-elements/assets/css/rrze-elements.css')) {
            wp_register_style('rrze-elements', plugins_url() . '/rrze-elements/assets/css/rrze-elements.css');
        }
    }

    /**
     * Generieren Sie die Shortcode-Ausgabe
     * @param  array   $atts Shortcode-Attribute
     * @return string Gib den Inhalt zurück
     */
    public function shortcodeLectures($atts, $content = NULL)
    {
        $tsStart = microtime(true);
        // show link to DIP only
        // if (in_array('link', $this->show)) {
        //     return sprintf('<a href="%1$s">%2$s</a>', $this->options['basic_url'], $this->options['basic_linkTxt']);
        // }

        Functions::console_log('START rrze-lectures shortcodeLectures()', $tsStart);

        if (!empty($atts['nocache'])) {
            $this->noCache = true;
        }

        // merge given attributes with default ones
        $atts_default = array();
        foreach ($this->settings as $k => $v) {
            if ($k != 'block') {
                $atts_default[$k] = $v['default'];
            }
        }
        $this->atts = shortcode_atts($atts_default, $atts);

        // get cache
        if (!$this->noCache) {
            $content = Functions::getDataFromCache($this->atts);

            if (!empty($content)) {
                Functions::console_log('Cache found and returned', $tsStart);
                return $content;
            }
        }

        // either lecture_id or fauorgnr or basic_FAUOrgNr (options) must be given 
        if (empty($this->atts['lecture_id']) && empty($this->atts['fauorgnr'])) {
            // try to get it from the plugin's options
            if (!empty($this->options['basic_FAUOrgNr'])) {
                $this->atts['fauorgnr'] = $this->options['basic_FAUOrgNr'];
            } else {
                return __('FAU Org Nr is missing. Either enter it in the settings of rrze-lectures or use the shortcode attribute fauorgnr', 'rrze-lectures');
            }
        }

        // dynamically generate hide vars
        $aHide = explode(',', str_replace(' ', '', $this->atts['hide']));
        foreach ($aHide as $val) {
            ${'hide_' . $val} = 1;
        }

        // check atts
        $this->atts['format'] = (in_array($this->atts['format'], $this->aAllowedFormats) ? $this->atts['format'] : 'linklist');
        $this->atts['color'] = (in_array($this->atts['color'], $this->aAllowedColors) ? $this->atts['color'] : 'fau');
        $this->atts['max'] = (!empty($this->atts['max']) && $this->atts['max'] < 100 ? $this->atts['max'] : 100);


        switch ($this->atts['format']) {
            case 'linklist':
                $attrs = 'identifier;url;providerValues.event.title;providerValues.event.eventtype';
                break;
            default:
                // $attrs = 'identifier;url;providerValues.event.title;providerValues.event_orgunit.orgunit;providerValues.event.eventtype;providerValues.event_responsible;description;maximumAttendeeCapacity;minimumAttendeeCapacity;providerValues.planned_dates;providerValues.module';
                $attrs = ''; // TEST
        }

        // $attrs = ''; // TEST

        $aLQ = [];

        if (!empty($this->atts['lecture_name'])) {
            $aLQ['name'] = urlencode($this->atts['lecture_name']); // funktioniert mit neuer API nicht, auch nicht mit urlencode() - 2DO: test mit q
        } else {
            $aLQ['providerValues.event_orgunit.fauorg'] = $this->atts['fauorgnr'];

            if (!empty($this->atts['lecturer_id'])) {
                $aLQ['providerValues.courses.course_responsible.idm_uid'] = $this->atts['lecturer_id'];
            }

            if (!empty($this->atts['type'])) {
                $aLQ['providerValues.event.eventtype'] = $this->atts['type']; // funktioniert bei Übung nicht
            }

            if (isset($this->atts['guest']) && $this->atts['guest'] != '') {
                // we cannot use empty() because it can contain 0
                $aLQ['providerValues.event.guest'] = (int) $this->atts['guest'];
            }

            if (!empty($this->atts['degree'])) {
                $aLQ['providerValues.module.module_cos.subject'] = $this->atts['degree']; // funktioniert nicht - liefert Module, die nicht zu subject passen
            }

    
        }


        // we cannot use API parameter "sort" because it sorts per page not the complete dataset
        $dipParams = '?limit=' . $this->atts['max'] . (!empty($attrs) ? '&attrs=' . urlencode($attrs) : ''). '&lq=' . urlencode(Functions::makeLQ($aLQ)) . '&page=';

        // echo $dipParams;
        // exit;

        // [lectures degree="Zahnmedizin, Medizin Erlangen/Bayreuth" nocache="1"]
        // Mathematik, Wirtschaftsmathematik, Technomathematik, Data Science

        Functions::console_log('Set params for DIP', $tsStart);

        $data = [];
        // $iAllEntries = 0;

        if (empty($data)) {
            $page = 1;

            $this->oDIP = new DIPAPI();
            $response = $this->oDIP->getResponse($dipParams . $page);

            if (!$response['valid']) {
                return $this->atts['nodata'];
            } else {

                $data = $response['content']['data'];

                // $iAllEntries += $response['content']['pagination']['count'];

                if ($this->atts['max'] == 100) {
                    while ($response['content']['pagination']['remaining'] > 0) {
                        $page++;
                        $response = $this->oDIP->getResponse($dipParams . $page);
                        $data = array_merge($response['content']['data'], $data);
                        // $iAllEntries += $response['content']['pagination']['count'];
                    }
                }
            }
        }

        // 2DO: API does not deliver all entries for planned_dates, see: https://www.campo.fau.de:443/qisserver/pages/startFlow.xhtml?_flowId=detailView-flow&unitId=108022&navigationPosition=studiesOffered,searchCourses
        Sanitizer::sanitizeLectures($data);

        // echo '<pre>';
        // var_dump($data);
        // exit;

        Functions::console_log('Fetched data from DIP', $tsStart);

        if (empty($data)) {
            return $this->atts['nodata'];
        }

        // group & sort
        $aData = [];

        foreach ($data as $nr => $aEntries) {
            $aData[$aEntries['providerValues']['event']['eventtype']][$aEntries['identifier']] = $aEntries;
        }
        unset($data); // free memory

        Functions::console_log('Group by eventtype completed', $tsStart);

        // sort
        $coll = collator_create('de_DE');

        if (!empty($hide_accordion)) {
            // combine all entries and sort them
            $aTmp = [];
            foreach ($aData as $group => $aDetails) {
                foreach ($aDetails as $aEntries) {
                    $aTmp[$aEntries['providerValues']['event']['title']] = $aEntries;
                }
            }
            unset($aData); // free memory

            $arrayKeys = array_keys($aTmp);
            collator_sort($coll, $arrayKeys);
            $aTmp2 = [];
            foreach ($arrayKeys as $key) {
                $aTmp2[$key] = $aTmp[$key];
            }
            unset($aTmp); // free memory
            $aData = [];
            $aData[] = $aTmp2;
            $iAllEntries = count($aTmp2);
            unset($aTmp2); // free memory
        } else {
            // sort group
            if (!empty($this->atts['type'])) {
                // sort in order of $this->atts['type']
                $aTmp = [];
                $aGivenTypes = array_map('trim', explode(',', $this->atts['type']));

                foreach ($aGivenTypes as $givenType) {
                    if (!empty($aData[$givenType])) {
                        $aTmp[$givenType] = $aData[$givenType];
                    }
                }
                $aData = $aTmp;
                unset($aTmp); // free memory
            } else {
                // sort alphabetically by group
                $arrayKeys = array_keys($aData);
                collator_sort($coll, $arrayKeys);
                $aTmp = [];
                foreach ($arrayKeys as $key) {
                    $aTmp[$key] = $aData[$key];
                }
                $aData = $aTmp;
                unset($aTmp); // free memory
            }

            // sort entries
            $iAllEntries = 0;
            $aTmp = [];
            foreach ($aData as $group => $aDetails) {
                $aTmp2 = [];
                foreach ($aDetails as $identifier => $aEntries) {
                    $name = $aEntries['providerValues']['event']['title'];
                    $aTmp2[$name] = $aEntries;
                }

                $arrayKeys = array_keys($aTmp2);
                array_multisort($arrayKeys, SORT_NATURAL | SORT_FLAG_CASE, $aTmp2);
                $iAllEntries += count($aTmp2);
                $aTmp[$group] = $aTmp2;
                unset($aTmp2); // free memory
            }

            $aData = $aTmp;
            unset($aTmp); // free memory
        }

        Functions::console_log('Sort completed', $tsStart);

        $template = 'shortcodes/' . $this->atts['format'] . '.html';

        $aTmp = [];
        $start = true;
        $iCnt = 1;

        if (empty($aData)) {
            return $this->atts['nodata'] . ' C';
        }

        foreach ($aData as $title => $aEntries) {
            $i = 1;

            foreach ($aEntries as $tmp => $data) {
                if (empty($hide_accordion)) {
                    $data['accordion'] = true;
                    $data['collapsibles_start'] = $start;
                    $data['collapse_title'] = ($i == 1 ? $title : false);
                    $data['collapsibles_end'] = ($iCnt == $iAllEntries ? true : false);
                    $data['collapse_start'] = ($data['collapse_title'] ? true : false);
                    $data['collapse_end'] = ($i == count($aEntries) ? true : false);
                    $data['color'] = $this->atts['color'];
                } else {
                    $data['first'] = $start;
                    $data['last'] = ($iCnt == $iAllEntries ? true : false);
                }

                $aTmp[] = $data;
                $i++;
                $start = false;
                $iCnt++;
            }
        }
        $aData = $aTmp;
        unset($aTmp); // free memory

        Functions::console_log('Accordion & first/last values set for template', $tsStart);

        foreach ($aData as $data) {
            $content .= Template::getContent($template, $data);
        }
        unset($aData); // free memory

        Functions::console_log('Template parsed', $tsStart);

        if (empty($hide_accordion)) {
            $content = do_shortcode($content);
        }

        Functions::console_log('do_shortcode() executed', $tsStart);

        // set cache
        Functions::setDataToCache($content, $this->atts);

        Functions::console_log('Cache set', $tsStart);
        Functions::console_log('END rrze-lectures shortcodeLectures()', $tsStart);

        return $content;
    }


    public function isGutenberg()
    {
        $postID = get_the_ID();
        if ($postID && !use_block_editor_for_post($postID)) {
            return false;
        }
        return true;
    }

    private function makeDropdown($id, $label, $aData, $all = null)
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

    private function makeToggle($label)
    {
        return [
            'label' => $label,
            'field_type' => 'toggle',
            'default' => true,
            'checked' => true,
            'type' => 'boolean',
        ];
    }

    public function fillGutenbergOptions($aSettings)
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


    public function addMCEButtons($pluginArray)
    {
        if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
            $pluginArray['rrze_lecture_shortcode'] = plugins_url('../js/tinymce-shortcodes.js', plugin_basename(__FILE__));
        }
        return $pluginArray;
    }
}