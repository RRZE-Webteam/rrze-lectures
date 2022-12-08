<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;
use function RRZE\DIP\Config\getShortcodeSettings;
use function RRZE\DIP\Config\getConstants;


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
    protected $lecture;
    private $settings = '';
    private $aAllowedColors = [];

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
        wp_enqueue_style('rrze-lectures');
    }

    /**
     * Generieren Sie die Shortcode-Ausgabe
     * @param  array   $atts Shortcode-Attribute
     * @return string Gib den Inhalt zurück
     */
    public function shortcodeLectures($atts, $content = NULL)
    {
        // show link to DIP only
        if (in_array('link', $this->show)){
            return sprintf('<a href="%1$s">%2$s</a>', $this->options['basic_url'], $this->options['basic_linkTxt']);
        }

        // merge given attributes with default ones
        $atts_default = array();
        foreach ($this->settings as $k => $v) {
            if ($k != 'block') {
                $atts_default[$k] = $v['default'];
            }
        }
        $this->atts = $this->normalize(shortcode_atts($atts_default, $atts));

        // dynamically generate hide vars
        $aHide = explode(',', str_replace(' ', '', $this->atts['hide']));
        foreach($aHide as $val){
            ${'hide_'.$val} = 1;
        }

        // set accordions' colors
        $this->atts['color'] = implode('', array_intersect($this->show, $this->aAllowedColors));
        $this->atts['color_courses'] = explode('_', implode('', array_intersect($this->show, preg_filter('/$/', '_courses', $this->aAllowedColors))));
        $this->atts['color_courses'] = $this->atts['color_courses'][0];

        // get data
        $data = '';
        $this->hide = ['cache'];
        if (!in_array('cache', $this->hide)){
            $data = Functions::getDataFromCache($this->atts);
        }

        if (empty($data)){
            $this->lecture = new DIPAPI($this->atts);


            $this->atts['id'] = '63fe386185';

            $data = $this->lecture->getResponse($this->atts['id']);
            Functions::setDataToCache($data, $this->atts);
        }

// echo 'asdf';
// exit;

        var_dump($data);
        exit;

        if (!empty($this->atts['id'])){
            // lectureByID
            $data = $this->getData('lectureByID', $this->atts['id']);
        }elseif (!empty($this->atts['name'])){
            // lectureByLecturerName
            $data = $this->getData('lectureByLecturer', $this->atts['name']);
        }elseif (!empty($this->atts['lecturerID'])){
            // lectureByLecturerID
            $data = $this->getData('lectureByLecturerID', $this->atts['lecturerID']);
        }else{
            // all lectures
            $data = $this->getData('lecture');
        }

        if ($data && is_array($data)) {
            // $data = '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';
            // var_dump($data);
            // exit;

            // is it an enclosing shortcode?
            if (preg_match_all('(\$\w+)', $content, $matches)) {
                foreach($data as $entry){
                    $ret .= str_replace(array_keys($entry), array_values($entry), $content);
                }
                return $ret;
            }else{
                if (count($data) > 1){
                    $filename = trailingslashit(dirname(__FILE__)) . '../templates/lectures-all.php';
                }else{
                    $filename = trailingslashit(dirname(__FILE__)) . '../templates/lectures-single.php';
                }

                if (is_file($filename)) {
                    ob_start();
                    include $filename;
                    return str_replace("\n", " ", ob_get_clean());
                }
            }
    

        } else {
            return $this->atts['nodata'];
        }
    }

    public function normalize($atts)
    {
        // normalize given attributes according to rrze-lectures version 2
        if (!empty($atts['number'])) {
            $this->DIPOrgNr = $atts['number'];
        } elseif (!empty($atts['id'])) {
            $this->DIPOrgNr = $atts['id'];
        }
        if (!empty($atts['dozentid'])) {
            $atts['id'] = $atts['dozentid'];
        }
        if (!empty($atts['dozentname'])) {
            $atts['name'] = $atts['dozentname'];
        }
        if (empty($atts['show'])) {
            $atts['show'] = '';
        }
        if (empty($atts['hide'])) {
            $atts['hide'] = '';
        }
        if (!empty($atts['sprache'])) {
            $atts['lang'] = $atts['sprache'];
        }
        if (isset($atts['show_phone'])) {
            if ($atts['show_phone']) {
                $atts['show'] .= ',telefon';
            } else {
                $atts['hide'] .= ',telefon';
            }
        }
        if (isset($atts['show_mail'])) {
            if ($atts['show_mail']) {
                $atts['show'] .= ',mail';
            } else {
                $atts['hide'] .= ',mail';
            }
        }
        if (isset($atts['show_jumpmarks'])) {
            if ($atts['show_jumpmarks']) {
                $atts['show'] .= ',sprungmarken';
            } else {
                $atts['hide'] .= ',sprungmarken';
            }
        }
        if (isset($atts['ics'])) {
            if ($atts['ics']) {
                $atts['show'] .= ',ics';
            } else {
                $atts['hide'] .= ',ics';
            }
        }
        if (isset($atts['call'])) {
            if ($atts['call']) {
                $atts['show'] .= ',call';
            } else {
                $atts['hide'] .= ',call';
            }
        }
        if (!empty($atts['show'])) {
            $this->show = array_map('trim', explode(',', strtolower($atts['show'])));
        }
        if (!empty($atts['hide'])) {
            $this->hide = array_map('trim', explode(',', strtolower($atts['hide'])));
        }
        if (!empty($atts['sem'])) {
            if (is_int($atts['sem'])) {
                $year = date("Y") + $atts['sem'];
                $thisSeason = (in_array(date('n'), [10, 11, 12, 1]) ? 'w' : 's');
                $season = ($thisSeason = 's' ? 'w' : 's');
                $atts['sem'] = $year . $season;
            }
        }
        if (empty($atts['hstart'])) {
            $atts['hstart'] = $this->options['basic_hstart'];
        }

        return $atts;
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
        $this->lecture = new DIPAPI($this->DIPURL, $this->DIPOrgNr, null);

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
            register_block_type($settings['block']['blocktype'], array(
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