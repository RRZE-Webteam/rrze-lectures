<?php

namespace RRZE\Lectures;

use function RRZE\Lectures\Config\getConstants;

defined('ABSPATH') || exit;

/**
 * Define Template
 */
class Template
{

    protected $pluginFile;
    private $settings = '';
    private $isFauTheme = false;

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }

    public function onLoaded()
    {
        $this->isFauTheme = self::isFAUTheme();
        // add_filter('single_template', array($this, 'include_single_template'));
        // add_filter('archive_template', array($this, 'include_archive_template'));
    }

    private static function isFAUTheme() {
        $active_theme = wp_get_theme();
        return in_array($active_theme->get('Name'), getConstants('fauthemes'));
    }
    

    public function include_single_template($template_path)
    {
        global $post;

        if (!$post->post_type == 'lecture'){
            return $template_path;
        }

        if ($this->isFauTheme) {
                $template_path = '/templates/single/single-lecture-fau-theme.php';
        } else {
                $template_path = '/templates/single/single-lecture.php';
        }

        return dirname($this->pluginFile) . $template_path;
    }

    public function include_archive_template($template_path)
    {
        global $post;

        if (!$post->post_type == 'lecture'){
            return $template_path;
        }

        if ($this->isFauTheme) {
                $template_path = '/templates/single/archive-lecture-fau-theme.php';
        } else {
                $template_path = '/templates/single/archive-lecture.php';
        }

        return dirname($this->pluginFile) . $template_path;
    }

    public static function getContent(string $template = '', array $data = []): string
    {
        return self::parseContent($template, $data);
    }

    protected static function parseContent(string $template, array $data): string
    {
        $content = self::getTemplate($template);
        if (empty($content)) {
            return '';
        }
        if (empty($data)) {
            return $content;
        }

        $parser = new Parser();
        return $parser->parse($content, $data);
    }

    protected static function getTemplate(string $template): string
    {
        $content = '';
        $templateFile = sprintf(
            '%1$stemplates/%2$s',
            plugin()->getDirectory(),
            $template
        );

        if (is_readable($templateFile)) {
            ob_start();
            include($templateFile);
            $content = ob_get_contents();
            @ob_end_clean();
        }else{
            echo $templateFile . ' not readable';
            exit;
        }
        return $content;
    }
}
