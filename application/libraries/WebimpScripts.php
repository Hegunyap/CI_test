<?php
class WebimpScripts extends WebimpAssetDependencies
{
    // full url with trailing slash
    public $base_url;

    // holds handles of scripts which are enqueued in footer
    public $in_footer;

    // default version string for stylesheets/scripts
    public $default_version = '1.0';




    /**
     * Process items and dependencies for the head group.
     *
     * @return array  Handles of items that have been processed.
     */
    public function do_head_items()
    {
        $this->do_items(false, 0);
        return $this->done;
    }




    /**
     * Processes items and dependencies for the footer group.
     *
     * @return array  Handles of items that have been processed.
     */
    public function do_footer_items()
    {
        $this->do_items(false, 1);
        return $this->done;
    }




    /**
     * Prints the scripts passed to it or the print queue. Also prints all necessary dependencies.
     *
     * @param  boolean  $handles  Optional. Scripts to be printed. (void) prints queue, (string) prints
     *                            that script, (array of strings) prints those scripts. Default false.
     * @param  boolean  $group    Optional. If scripts were queued in groups prints this group number.
     *                            Default false.
     * @return array              Scripts that have been printed.
     */
    public function print_scripts($handles = false, $group = false)
    {
        return $this->do_items($handles, $group);
    }




    /**
     * Processes a script dependency.
     *
     * @param  string     $handle  The script's registered handle.
     * @param  int|false  $group   Optional. Group level: (int) level.
     *
     * @return bool                True on success, false on failure.
     */
    public function do_item($handle, $group = false)
    {
        if (!parent::do_item($handle)) {
            return false;
        }

        if ($group === 0 &&
            $this->groups[$handle] > 0
        ) {
            $this->in_footer[] = $handle;
            return false;
        }

        if ($group === false &&
            in_array($handle, $this->in_footer, true)
        ) {
            $this->in_footer = array_diff($this->in_footer, (array) $handle);
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

        $src = $obj->src;
        $cond_before = $cond_after = '';
        $conditional = isset($obj->extra['conditional']) ? $obj->extra['conditional'] : '';

        if ($conditional) {
            $cond_before = "<!--[if {$conditional}]>\n";
            $cond_after = "<![endif]-->\n";
        }

        $before_handle = $this->print_inline_script($handle, 'before', false);
        $after_handle = $this->print_inline_script($handle, 'after', false);

        if ($before_handle) {
            $before_handle = sprintf("<script type='text/javascript'>\n%s\n</script>\n", $before_handle);
        }

        if ($after_handle) {
            $after_handle = sprintf("<script type='text/javascript'>\n%s\n</script>\n", $after_handle);
        }

        $has_conditional_data = $conditional && $this->get_data($handle, 'data');

        if ($has_conditional_data) {
            echo $cond_before;
        }

        $this->print_extra_script($handle);

        if ($has_conditional_data) {
            echo $cond_after;
        }

        // A single item may alias a set of items, by having dependencies, but no source.
        if (!$obj->src) {
            return true;
        }

        if (!preg_match('|^(https?:)?//|', $src) &&
            !($this->content_url && 0 === strpos($src, $this->content_url))
        ) {
            $src = $this->base_url . $src;
        }

        if (!empty($ver)) {
            $src = add_query_arg('ver', $ver, $src);
        }

        if (!$src) {
            return true;
        }

        $tag = $cond_before . $before_handle . '<script type="text/javascript" src="' . $src . '"></script>' . "\n" . $after_handle . $cond_after;

        echo $tag;

        return true;
    }




    /**
     * Prints inline scripts registered for a specific handle.
     *
     * @param  string   $handle    Name of the script to add the inline script to. Must be lowercase.
     * @param  string   $position  Optional. Whether to add the inline script before the handle
     *                             or after. Default 'after'.
     * @param  boolean  $echo      Optional. Whether to echo the script instead of just returning it.
     *                             Default true.
     *
     * @return string|false        Script on success, false otherwise.
     */
    public function print_inline_script($handle, $position = 'after', $echo = true)
    {
        $output = $this->get_data($handle, $position);

        if (empty($output)) {
            return false;
        }

        $output = trim(implode("\n", $output), "\n");

        if ($echo) {
            printf("<script type='text/javascript'>\n%s\n</script>\n", $output);
        }

        return $output;
    }




    /**
     * Prints extra scripts of a registered script.
     *
     * @param  string  $handle   The script's registered handle.
     * @param  boolean $echo     Optional. Whether to echo the extra script instead of just returning it.
     *
     * @return bool|string|void  Void if no data exists, extra scripts if `$echo` is true, true otherwise.
     */
    public function print_extra_script($handle, $echo = true)
    {
        if (!$output = $this->get_data($handle, 'data')) {
            return;
        }

        if (!$echo) {
            return $output;
        }

        echo "<script type='text/javascript'>\n"; // CDATA and type='text/javascript' is not needed for HTML 5
        echo "/* <![CDATA[ */\n";
        echo "$output\n";
        echo "/* ]]> */\n";
        echo "</script>\n";

        return true;
    }
}
