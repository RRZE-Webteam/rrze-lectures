<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

// use function RRZE\Lectures\Config\getConstants;
use RRZE\Lectures\Settings;
use RRZE\Lectures\Shortcode;

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

    /* Settings 
    * @var Object    
    */
    protected $settings;
    
    
    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
    }

    public function onLoaded() {
        $functions = new Functions($this->pluginFile);
        $functions->onLoaded();

        $settings = new Settings($this->pluginFile);
        $settings->onLoaded();

        $this->settings = $settings;

        $templates = new Template($this->pluginFile, $settings);
        $templates->onLoaded();

        $sanitizer = new Sanitizer($this->pluginFile, $settings);
        $sanitizer->onLoaded();

        $shortcode = new Shortcode($this->pluginFile, $settings);
        $shortcode->onLoaded();
    }

    public static function getThemeGroup() {
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
