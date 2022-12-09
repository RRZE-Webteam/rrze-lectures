<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

/**
 * Parse a markup string with embedded interpolation expressions.
 *
 * Simple interpolation: {{=value}}
 * Scrubbed interpolation: {{%unsafe_value}}
 * Name-spaced variables: {{=user.address.city}}
 * If blocks: {{value}} <<markup>> {{/value}}
 * If not blocks: {{!value}} <<markup>> {{/!value}}
 * If/else blocks: {{value}} <<markup>> {{:value}} <<alternate markup>> {{/value}}
 * Object/Array iteration: {{@values}} {{=_key}}:{{=_val}} {{/@values}}
 */
class Parser
{
    /**
     * Regular expression that identifies a block.
     * @var string
     */
    protected $blockRegex = '/\\{\\{(([@!]?)(.+?))\\}\\}(([\\s\\S]+?)(\\{\\{:\\1\\}\\}([\\s\\S]+?))?)\\{\\{\\/\\1\\}\\}/';

    /**
     * Regular expression that identifies a value.
     * @var string
     */
    protected $valueRegex = '/\\{\\{([=%])(.+?)\\}\\}/';

    /**
     * Array that stores the identified values.
     * @var array
     */
    protected $vars;

    /**
     * Construct function.
     */
    public function __construct()
    {
        $this->vars = [];
    }

    /**
     * Convert special characters to HTML entities
     * @param string $value The string being converted.
     * @return string The converted string.
     */
    public function convertToHtmlEntities(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES);
    }

    /**
     * Get a specific value.
     * @param string $index The value index.
     * @return mixed Returns the value.
     */
    public function getValue(string $index)
    {
        $index = explode('.', $index);

        return $this->searchValue($index, $this->vars);
    }

    /**
     * Look for a value.
     * @param mixed $index The value index.
     * @param mixed $value The value.
     * @return mixed Returns the found value.
     */
    protected function searchValue($index, $value)
    {
        if (
            is_array($index) &&
            !empty($index)
        ) {
            $currentIndex = array_shift($index);
        }
        if (
            is_array($index) &&
            !empty($index) &&
            isset($value[$currentIndex]) &&
            is_array($value[$currentIndex]) &&
            !empty($value[$currentIndex])
        ) {
            return $this->searchValue($index, $value[$currentIndex]);
        } else {
            return $value[$currentIndex] ?? '';
        }
    }

    /**
     * Matching tags.
     * @param array $matches An array containing tags.
     * @return mixed Returns the matching tags.
     */
    public function matchTags($matches)
    {
        $_key = $matches[0] ?? '';
        $_val = $matches[1] ?? '';
        $meta = $matches[2] ?? '';
        $key = $matches[3] ?? '';
        $inner = $matches[4] ?? '';
        $ifTrue = $matches[5] ?? '';
        $hasElse = $matches[6] ?? '';
        $ifFalse = $matches[7] ?? '';

        $val = $this->getValue($key);

        $temp = "";

        if (!$val) {
            // handle if not
            if ($meta == '!') {
                return $this->render($inner);
            }
            // check for else
            if ($hasElse) {
                return $this->render($ifFalse);
            }

            return "";
        }

        // regular if
        if (!$meta) {
            return $this->render($ifTrue);
        }

        // process array/obj iteration
        if ($meta == '@') {
            // store any previous vars
            // reuse existing vars
            $_key = $this->vars['_key'] ?? '';
            $_val = $this->vars['_val'] ?? '';

            foreach ($val as $i => $v) {
                $this->vars['_key'] = $i;
                $this->vars['_val'] = $v;

                $temp .= $this->render($inner);
            }

            $this->vars['_key'] = $_key;
            $this->vars['_val'] = $_val;

            return $temp;
        }
    }

    /**
     * Replace the tags.
     * @param array $matches An array of matching tags.
     * @return mixed Returns the tags replaced with the corresponding value.
     */
    public function replaceTags(array $matches)
    {
        if (!is_array($matches)) {
            return '';
        }

        $meta = $matches[1] ?? '';
        $key = $matches[2] ?? '';

        $val = $this->getValue($key);

        if ($val || $val === 0) {
            return $meta == '%' ? $this->convertToHtmlEntities($val) : $val;
        }

        return '';
    }

    /**
     * Render a fragment of the content.
     * @param string $fragment The fragment.
     * @return mixed Returns the rendered fragment.
     */
    protected function render(string $fragment)
    {
        $matchTags = preg_replace_callback($this->blockRegex, [$this, 'matchTags'], $fragment);
        $replaceTags = preg_replace_callback($this->valueRegex, [$this, 'replaceTags'], $matchTags);

        return $replaceTags;
    }

    /**
     * Parse a string content with embedded interpolation expressions.
     * @param string $content The content.
     * @param array $data An array of data to be replaced.
     * @return string Returns the parsed content.
     */
    public function parse(string $content, array $data): string
    {
        $this->vars = (array) $data;
        return $this->render($content);
    }
}
