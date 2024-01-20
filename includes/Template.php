<?php

namespace RRZE\Lectures;

use function RRZE\Lectures\Config\getConstants;
defined('ABSPATH') || exit;

/**
 * Define Template
 */
class Template {
    public $template_cache;
    public $formatlist;
    
    public function __construct()  {       
           $constants = getConstants();
           $this->formatlist = $constants['template_formats'];
           $this->template_cache = [];
            // we dont want to load the same templates thousand times,
            // therfor we cache them
    }

    public function onLoaded() {
        return true;
    }

   
    /*
     * Parses a Template Setting by its name
     * A Template Setting can contain also subtemplates and is defined in 
     * the config
     *
     * Beispiel einer Format-Setting in der Config:
     * 'tabs'      => [
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
                            // und auch wie dessen Inhalte dann mit {{=subtemplate_base}}
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
                    ]
                ]
            ],
     */
    
    public function parseSetting(string $template_name, array $data, array $atts): string { 
        if ((empty($template_name)) || (!isset($this->formatlist[$template_name]))) {
            return '';
        }
        
        $dir = $this->formatlist[$template_name]['name'];
        $content = '';
        $parser = new Parser();
        
        // Also add $atts to $data 
        foreach ($atts as $name => $value) {
            $attvarname = "atts_".$name;
            $data[$attvarname] = $value;
        }    
        
        // Generate special atts
        $data['atts_show_degree_title'] = (empty($atts['hide_degree'])? true : false);  
        $data['atts_do_accordion'] = !($atts['hide_degree_accordion'] && $atts['hide_type_accordion']);
        $data['atts_do_type_accordion'] = !$atts['hide_type_accordion'];
        $data['atts_do_degree_accordion'] = !$atts['hide_degree_accordion'];
        

        
        if (!empty($this->formatlist[$template_name]['contains'])) {
            // Enthält Sub-Template
            foreach ($this->formatlist[$template_name]['contains'] as $subtemplate) {
                $attname = $subtemplate['attribut'];
                
                if ((($subtemplate['default'] == true) && (!isset($atts['hide_'.$attname])))
                 || (($subtemplate['default'] == false) && (isset($atts['show_'.$attname])))) {
                    
                    $subtemplate_file = $dir.'/'.$subtemplate['name'].'.php';
                    $cachename = $dir.'/'.$subtemplate['name'];
                    if ((!isset($this->template_cache[$cachename])) || (empty($this->template_cache[$cachename]))) {
                        $subcontent = self::getTemplate($subtemplate_file);
                        if (!empty($subcontent)) {
                            $this->template_cache[$cachename] = $subcontent;
                        }
                    } else {
                        $subcontent = $this->template_cache[$cachename];
                    }
                    
                    if (!empty($subcontent)) {
             //           $content .= Debug::get_notice("Parsing Subtemplate ".$subtemplate['name']." with file ".$subtemplate_file);      
                        $parsed_subcontent = $parser->parse($subcontent, $data);
                    
                        if (!empty($parsed_subcontent)) {
                            $data["subtemplate_".$subtemplate['name']] = $parsed_subcontent;
                        }
                    }
 
                }

            }

        }
        $basetemplate = $dir.'/'.$dir.'.php';  
        $cachename = $dir.'/'.$dir;
        if ((!isset($this->template_cache[$cachename])) || (empty($this->template_cache[$cachename]))) {
            $base_content = self::getTemplate($basetemplate);
            if (!empty($base_content)) {
                $this->template_cache[$cachename] = $base_content;
            }
        } else {
            $base_content = $this->template_cache[$cachename];
        }
       
   //     $content .= Debug::get_notice("Base Template: $template_name in $basetemplate ");      
        if (!empty($base_content)) {
           $content .= $parser->parse($base_content, $data); 
        }

        return $content;
    }
        
    
       
    
    public static function getContent(string $template = '', array &$data = []): string
    {
        return self::parseContent($template, $data);
    }

    protected static function parseContent(string $template, array &$data): string
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
        } else{
            Debug::log('warn','warning',$templateFile . ' not readable');

        }
        return $content;
    }

    public static function makeCollapseTitle(array &$data, string &$group): string
    {
        $name = htmlentities($data['name']);

        switch ($group) {
            case 'a-z':
                $ret = strtoupper(substr($name, 0, 1));
                break;
            default:
                $ret = $name;
                break;
        }

        return $ret;
    }

    public static function makeAccordion(array &$data, int &$i, int &$max, string &$tite, string &$group): array
    {
        $data['accordion'] = true;
        $data['collapsibles_start'] = ($i == 1 ? true : false);
        $data['collapsibles_end'] = ($i < $max ? false : true);
        $data['collapse_title'] = $this->makeCollapseTitle($data, $group);
        $data['collapse_start'] = ($data['collapse_title'] ? true : false);
        $data['collapse_end'] = ($data['collapse_start'] && $i > 1 ? true : false);

        return $data;
    }

}
