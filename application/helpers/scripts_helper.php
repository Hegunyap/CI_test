<?php
if (!function_exists('webimp_scripts')) {
    // singleton
    function webimp_scripts()
    {
        global $webimp_scripts;

        if (!($webimp_scripts instanceof WebimpScripts)) {
            $webimp_scripts = new WebimpScripts();
        }

        return $webimp_scripts;
    }
}




if (!function_exists('register_script')) {
    function register_script($handle, $src, $deps = [], $ver = false, $in_footer = false)
    {
        $webimp_scripts = webimp_scripts();

        $registered = $webimp_scripts->add($handle, $src, $deps, $ver);

        if ($in_footer) {
            $webimp_scripts->add_data($handle, 'group', 1);
        }

        return $registered;
    }
}




if (!function_exists('enqueue_script')) {
    function enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false)
    {
        $webimp_scripts = webimp_scripts();

        if ($src || $in_footer) {
            $_handle = explode('?', $handle);

            if ($src) {
                $webimp_scripts->add($_handle[0], $src, $deps, $ver);
            }

            if ($in_footer) {
                $webimp_scripts->add_data($_handle[0], 'group', 1);
            }
        }

        $webimp_scripts->enqueue($handle);
    }
}




if (!function_exists('register_scripts')) {
    function register_scripts()
    {
        // main app
        register_script('sanz', assets_url("js/sanz.min.js"), [], get_app_version(), false);

        // datatables
        register_script('datatables', assets_url("js/datatables.min.js"), ['sanz'], '1.10.16', false);
    }
}
