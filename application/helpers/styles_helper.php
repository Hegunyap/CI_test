<?php
if (!function_exists('webimp_styles')) {
    // singleton
    function webimp_styles()
    {
        global $webimp_styles;

        if (!($webimp_styles instanceof WebimpStyles)) {
            $webimp_styles = new WebimpStyles();
        }

        return $webimp_styles;
    }
}




if (!function_exists('register_style')) {
    function register_style($handle, $src, $deps = [], $ver = false, $media = 'all')
    {
        return webimp_styles()->add($handle, $src, $deps, $ver, $media);
    }
}




if (!function_exists('enqueue_style')) {
    function enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all')
    {
        $webimp_styles = webimp_styles();

        if ($src) {
            $_handle = explode('?', $handle);

            $webimp_styles->add($_handle[0], $src, $deps, $ver, $media);
        }

        $webimp_styles->enqueue($handle);
    }
}
