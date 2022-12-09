<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

// use function RRZE\Lectures\Config\getConstants;
use RRZE\Lectures\Settings;
use RRZE\Lectures\Shortcode;
use RRZE\Lectures\Templates;


/**
 * Hauptklasse (Main)
 */
class Main
{
    /**
     * Der vollständige Pfad- und Dateiname der Plugin-Datei.
     * @var string
     */
    protected $pluginFile;
    protected $widget;

    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
        add_action('init', 'RRZE\Lectures\add_endpoint');
        add_action('template_redirect', [$this, 'getSingleEntry']);
    }

    public function onLoaded()
    {
        $functions = new Functions($this->pluginFile);
        $functions->onLoaded();

        $settings = new Settings($this->pluginFile);
        $settings->onLoaded();

        $this->settings = $settings;

        $templates = new Template($this->pluginFile, $settings);
        $templates->onLoaded();

        $shortcode = new Shortcode($this->pluginFile, $settings);
        $shortcode->onLoaded();

        // Widget
        $this->widget = new LectureWidget($this->pluginFile, $settings);
        add_action('widgets_init', [$this, 'loadWidget']);
        add_theme_support('widgets-block-editor');
        apply_filters('gutenberg_use_widgets_block_editor', get_theme_support('widgets-block-editor'));
    }

    public function loadWidget()
    {
        register_widget($this->widget);
    }


    public function getSingleEntry()
    {
        global $wp_query;

        if (isset($wp_query->query_vars['lv_id'])) {
            $data = do_shortcode('[lecture task="lectures-single" lv_id="' . $wp_query->query_vars['lv_id'] . '" ]');
        } elseif (isset($wp_query->query_vars['lectureid'])) {
            $sShortcodeParams = '';
            $aParts = explode('_', $wp_query->query_vars['lectureid']);
            if (!empty($aParts[1])) {
                parse_str($aParts[1], $aParams);
                $sShortcodeParams = 'show="' . $aParams['show'] . '" hide="' . $aParams['hide'] . '"';
            }
        } else {
            return;
        }

        include plugin_dir_path($this->pluginFile) . 'templates/single-lecture.php';
        exit;
    }

    public static function getThemeGroup()
    {
        $constants = getConstants();
        $ret = '';
        $active_theme = wp_get_theme();
        $active_theme = $active_theme->get('Name');

        if (in_array($active_theme, $constants['fauthemes'])) {
            $ret = 'fauthemes';
        } elseif (in_array($active_theme, $constants['rrzethemes'])) {
            $ret = 'rrzethemes';
        }
        return $ret;
    }

}
