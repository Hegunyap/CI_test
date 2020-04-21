<?php
class WebimpStyles extends WebimpAssetDependencies
{
    // full url with trailing slash
    public $base_url;

    // default version string for stylesheets/scripts
    public $default_version = '1.0';




    /**
     * Processes a style dependency.
     *
     * @param  string  $handle  The style's registered handle.
     *
     * @return boolean          True on success, false on failure.
     */
    public function do_item($handle)
    {
        if (!parent::do_item($handle)) {
            return false;
        }

        $obj = $this->registered[$handle];

        if ($obj->ver === null) {
            $ver = '';
        } else {
            $ver = $obj->ver ? $obj->ver : $this->default_version;
        }

        if (isset($this->args[$handle])) {
            $ver = $ver ? $ver . '&amp;' . $this->args[$handle] : $this->args[$handle];
        }

        if (isset($obj->args)) {
            $media = html_escape($obj->args);
        } else {
            $media = 'all';
        }

        if (!$obj->src) {
            // a single item may alias a set of items, by having dependencies, but no source.
            if ($inline_style = $this->print_inline_style($handle, false)) {
                $inline_style = sprintf("<style id='%s-inline-css' type='text/css'>\n%s\n</style>\n", esc_attr($handle), $inline_style);

                echo $inline_style;
            }

            return true;
        }

        $href = $this->_css_href($obj->src, $ver, $handle);

        if (!$href) {
            return true;
        }

        $rel = (isset($obj->extra['alt']) && $obj->extra['alt']) ? 'alternate stylesheet' : 'stylesheet';
        $title = isset($obj->extra['title']) ? 'title="' . html_escape($obj->extra['title']) . '"' : '';
        $tag = '<link rel="' . $rel . '" id="' . $handle . '-css"' . $title . ' href="' . $href . '" type="text/css" media="' . $media . '" />' . "\n";

        $conditional_pre = $conditional_post = '';
        if (isset($obj->extra['conditional']) &&
            $obj->extra['conditional']
        ) {
            $conditional_pre  = "<!--[if {$obj->extra['conditional']}]>\n";
            $conditional_post = "<![endif]-->\n";
        }

        echo $conditional_pre;
        echo $tag;
        $this->print_inline_style($handle);
        echo $conditional_post;

        return true;
    }




    /**
     * Prints extra CSS styles of a registered stylesheet.
     *
     * @param  string          $handle  The style's registered handle.
     * @param  boolean         $echo    Optional. Whether to echo the inline style instead of just
     *                                  returning it. Default true.
     *
     * @return string|boolean           False if no data exists, inline styles if `$echo` is true,
     *                                  true otherwise.
     */
    public function print_inline_style($handle, $echo = true)
    {
        $output = $this->get_data($handle, 'after');

        if (empty($output)) {
            return false;
        }

        $output = implode("\n", $output);

        if (!$echo) {
            return $output;
        }

        printf('<style id="%s-inline-css" type="text/css">%s</style>', html_escape($handle), $output);

        return true;
    }




    /**
     * Generates an enqueued style's fully qualified URL.
     *
     * @param  string $src    The source of the enqueued style.
     * @param  string $ver    The version of the enqueued style.
     * @param  string $handle The style's registered handle.
     *
     * @return string         Style's fully qualified URL.
     */
    public function _css_href($src, $ver, $handle)
    {
        if (!is_bool($src) &&
            !preg_match('|^(https?:)?//|', $src) &&
            !($this->content_url && 0 === strpos($src, $this->content_url))
        ) {
            $src = $this->base_url . $src;
        }

        if (!empty($ver)) {
            $src = add_query_arg('ver', $ver, $src);
        }

        return html_escape($src);
    }
}
