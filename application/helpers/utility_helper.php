<?php defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . "/third_party/sendgrid-php/sendgrid-php.php";

use \SendGrid\Mail\From as From;
use \SendGrid\Mail\Mail as Mail;
use \SendGrid\Mail\To as To;

if (!function_exists('get_app_version')) {
    function get_app_version()
    {
        return '1.0.0';
    }
}

if (!function_exists('assets_url')) {
    function assets_url($string = null)
    {
        return base_url() . 'assets/' . $string;
    }
}

if (!function_exists('temp_url')) {
    function temp_url($string = null)
    {
        return base_url() . 'temp/' . $string;
    }
}

if (!function_exists('uploads_url')) {
    function uploads_url($string = null)
    {
        return base_url() . 'uploads/' . $string;
    }
}

if (!function_exists('is_checked')) {
    function is_checked($name, $value = null, $default = false)
    {
        if (isset($_POST[$name])) {
            if ($value) {
                if ($_POST[$name] == $value) {
                    return true;
                }

                return false;
            }

            return true;
        } else {
            if ($default) {
                return true;
            }

        }
    }
}

/**
 * Merge user defined arguments into defaults array.
 *
 * This function is used throughout the application for both string or array
 * to be merged into another array.
 *
 * @param string|array|object $args     Value to merge with $defaults.
 * @param string              $defaults Optional. Array that serves as the defaults. Default
 *                                      empty.
 *
 * @return [type]                          Merged user defined values with defaults.
 */
function parse_args($args, $defaults = '')
{
    if (is_object($args)) {
        $output = get_object_vars($args);
    } elseif (is_array($args)) {
        $output = &$args;
    } else {
        parse_str($args, $output);
    }

    if (is_array($defaults)) {
        return array_merge($defaults, $output);
    }

    return $output;
}

/**
 * Outputs all enqueued styles and scripts.
 */
function app_head()
{
    webimp_styles()->do_items();
    webimp_scripts()->do_head_items();
}

/**
 * Retrieves a modified URL query string.
 *
 * @param string|array $key   Either a query variable key, or an associative array of query variables.
 * @param string       $value Optional. Either a query variable value, or a URL to act upon.
 * @param string       $url   Optional. A URL to act upon.
 *
 * @return string               New URL query (unescaped).
 */
function add_query_arg()
{
    $args = func_get_args();
    if (is_array($args[0])) {
        if (count($args) < 2 || false === $args[1]) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            $uri = $args[1];
        }
    } else {
        if (count($args) < 3 || false === $args[2]) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            $uri = $args[2];
        }
    }

    if ($frag = strstr($uri, '#')) {
        $uri = substr($uri, 0, -strlen($frag));
    } else {
        $frag = '';
    }

    if (0 === stripos($uri, 'http://')) {
        $protocol = 'http://';
        $uri = substr($uri, 7);
    } elseif (0 === stripos($uri, 'https://')) {
        $protocol = 'https://';
        $uri = substr($uri, 8);
    } else {
        $protocol = '';
    }

    if (strpos($uri, '?') !== false) {
        list($base, $query) = explode('?', $uri, 2);
        $base .= '?';
    } elseif ($protocol || strpos($uri, '=') === false) {
        $base = $uri . '?';
        $query = '';
    } else {
        $base = '';
        $query = $uri;
    }

    parse_str($query, $qs);
    $qs = map_deep($qs, 'urlencode');

    if (is_array($args[0])) {
        foreach ($args[0] as $k => $v) {
            $qs[$k] = $v;
        }
    } else {
        $qs[$args[0]] = $args[1];
    }

    foreach ($qs as $k => $v) {
        if ($v === false) {
            unset($qs[$k]);
        }
    }

    $ret = build_query($qs);
    $ret = trim($ret, '?');
    $ret = preg_replace('#=(&|$)#', '$1', $ret);
    $ret = $protocol . $base . $ret . $frag;
    $ret = rtrim($ret, '?');
    return $ret;
}

/**
 * Maps a function to all non-iterable elements of an array or an object.
 *
 * @param mixed    $value    The array, object, or scalar.
 * @param callable $callback The function to map onto $value.
 *
 * @return mixed                The value with the callback applied to all non-arrays and
 *                              non-objects inside it.
 */
function map_deep($value, $callback)
{
    if (is_array($value)) {
        foreach ($value as $index => $item) {
            $value[$index] = map_deep($item, $callback);
        }
    } elseif (is_object($value)) {
        $object_vars = get_object_vars($value);

        foreach ($object_vars as $property_name => $property_value) {
            $value->$property_name = map_deep($property_value, $callback);
        }
    } else {
        $value = call_user_func($callback, $value);
    }

    return $value;
}

if (!function_exists('build_query')) {
    function build_query($data, $prefix = null, $sep = '&', $key = '', $urlencode = true)
    {
        $ret = [];

        foreach ((array) $data as $k => $v) {
            if ($urlencode) {
                $k = urlencode($k);
            }

            if (is_int($k) && $prefix != null) {
                $k = $prefix . $k;
            }

            if (!empty($key)) {
                $k = $key . '%5B' . $k . '%5B';
            }

            if ($v === null) {
                continue;
            } elseif ($v === false) {
                $v = '0';
            }

            if (is_array($v)
                || is_object($v)
            ) {
                array_push($ret, http_build_query($v, '', $sep, $k, $urlencode));
            } elseif ($urlencode) {
                array_push($ret, $k . '=' . urlencode($v));
            } else {
                array_push($ret, $k . '=' . $v);
            }
        }

        if ($sep === null) {
            $sep = ini_get('arg_seperator.output');
        }

        return implode($sep, $ret);
    }
}

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s     Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d     Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r     Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole  $img   True to return a complete IMG tag False for just the URL
 * @param array  $atts  Optional, additional key/value attributes to include in the IMG tag
 *
 * @return String containing either just a URL or a complete image tag
 * @source http://gravatar.com/site/implement/images/php/
 */
function get_gravatar($email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array())
{
    $url = 'http://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&d=$d&r=$r";

    if ($img) {
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val) {
            $url .= ' ' . $key . '="' . $val . '"';
        }
        $url .= ' />';
    }
    return $url;
}

if (!function_exists('human_date')) {
    function human_date($datestring = null, $format = 'j M Y, g:i A')
    {
        if ($datestring) {
            return date($format, strtotime($datestring));
        }

        return null;
    }
}

if (!function_exists('human_timestamp')) {
    function human_timestamp($timestamp = null, $format = 'j M Y, g:i A')
    {
        if ($timestamp) {
            return date($format, $timestamp);
        }

        return null;
    }
}

if (!function_exists('mysql_datetime')) {
    function mysql_datetime($timestamp = null, $format = '%Y-%m-%d %H:%i:%s')
    {
        if ($timestamp) {
            return mdate($format, $timestamp);
        } else {
            return mdate($format, time());
        }

        return null;
    }
}

/**
 * Output preformatted data from $data for debugging purposes.
 *
 * @param mixed $data The array or object to print.
 */
function verbose($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

if (!function_exists('money_number_format')) {
    function money_number_format($number, $decimals = 2)
    {
        return '$ ' . number_format($number, $decimals);
    }
}

if (!function_exists('replace_newline')) {
    function replace_newline($string = null, $replace_to = '<BR>')
    {
        if ($string) {
            return preg_replace('~[\r\n]+~', $replace_to, $string);
        } else {
            return null;
        }
    }
}

if (!function_exists('randomGen')) {
    function randomGen($min, $max, $quantity)
    {
        $numbers = range($min, $max);
        shuffle($numbers);
        return array_slice($numbers, 0, $quantity);
    }
}

if (!function_exists('convert_to_utf8')) {
    function convert_to_utf8($param, $ent = ENT_QUOTES, $encoding_type = 'UTF-8')
    {
        $param = html_entity_decode($param, $ent, $encoding_type);
        return $param;
    }
}

if (!function_exists('sort_on_field')) {
    function sort_on_field(&$objects, $on, $order = 'ASC')
    {
        $comparer = ($order === 'DESC')
        ? "return -strcmp(\$a->{$on},\$b->{$on});"
        : "return strcmp(\$a->{$on},\$b->{$on});";
        usort($objects, create_function('$a,$b', $comparer));
    }
}

if (!function_exists('datestring_to_format')) {
    function datestring_to_format($datestring = null, $format = 'j M Y, g:i A')
    {
        return timestamp_to_format(strtotime($datestring), $format);
    }
}

if (!function_exists('timestamp_to_format')) {
    // some don't have any timestamp, so return null
    function timestamp_to_format($timestamp = null, $format = 'j M Y, g:i A')
    {
        if ($timestamp) {
            return date($format, $timestamp);
        } else {
            return null;
        }
    }
}

/**
 * Move an array item to a new index.
 *
 * @param array $a      The array to be modified.
 * @param int   $oldpos The original index position of item to be moved.
 * @param int   $newpos The new index position.
 *
 * @return array           The new modified array.
 */
function array_move(&$a, $oldpos, $newpos)
{
    if ($oldpos == $newpos) {
        return;
    }

    array_splice($a, max($newpos, 0), 0, array_splice($a, max($oldpos, 0), 1));
}

/**
 * Convert a timestamp string into a relative time string.
 *
 * @param string $ts The timestamp string.
 *
 * @return string       The relative-time formatted string.
 */
function timestamp_to_relative($ts)
{
    if (!ctype_digit($ts)) {
        $ts = strtotime($ts);
    }

    $diff = time() - $ts;

    if ($diff == 0) {
        return 'now';
    } elseif ($diff > 0) {
        $day_diff = floor($diff / 86400);

        if ($day_diff == 0) {
            if ($diff < 60) {return 'just now';
            }
            if ($diff < 120) {return '1 minute ago';
            }
            if ($diff < 3600) {return floor($diff / 60) . ' minutes ago';
            }
            if ($diff < 7200) {return '1 hour ago';
            }
            if ($diff < 86400) {return floor($diff / 3600) . ' hours ago';
            }
        }

        if ($day_diff == 1) {return 'yesterday';
        }
        if ($day_diff < 7) {return $day_diff . ' days ago';
        }
        if ($day_diff < 31) {return ceil($day_diff / 7) . ' weeks ago';
        }
        if ($day_diff < 60) {return 'last month';
        }

        return date('F Y', $ts);
    } else {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);

        if ($day_diff == 0) {
            if ($diff < 120) {return 'in a minute';
            }
            if ($diff < 3600) {return 'in ' . floor($diff / 60) . ' minutes';
            }
            if ($diff < 7200) {return 'in an hour';
            }
            if ($diff < 86400) {return 'in ' . floor($diff / 3600) . ' hours';
            }
        }

        if ($day_diff == 1) {return 'tomorrow';
        }
        if ($day_diff < 4) {return date('l', $ts);
        }
        if ($day_diff < 7 + (7 - date('w'))) {return 'next week';
        }
        if (ceil($day_diff / 7) < 4) {return 'in ' . ceil($day_diff / 7) . ' weeks';
        }
        if (date('n', $ts) == date('n') + 1) {return 'next month';
        }

        return date('F Y', $ts);
    }
}

if (!function_exists('get_working_days')) {
    function get_working_days($startDate, $endDate, $holidays = [])
    {
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);

        // the total number of days between the two dates. We compute the no. of seconds and divide it by 60*60*24
        // we add one to include both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        // it will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        // the two can be equal in leap years when february has 29 days, the equal sign is added here
        // in the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) {$no_remaining_days--;
            }
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) {$no_remaining_days--;
            }
        } else {
            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            } else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        // the no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
        // february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0) {
            $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
        foreach ($holidays as $holiday) {
            $time_stamp = strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N", $time_stamp) != 6 && date("N", $time_stamp) != 7) {
                $workingDays--;
            }
        }

        return $workingDays;
    }
}

if (!function_exists('display_data')) {
    function display_data($temp = '')
    {
        if ($temp) {
            return $temp;
        } else {
            return ' - ';
        }
    }
}

if (!function_exists('crypto_rand_secure')) {
    function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) {return $min; // not so random...
        }
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1

        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);

        return $min + $rnd;
    }
}

if (!function_exists('getToken')) {
    function getToken($length = 100)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[crypto_rand_secure(0, $max)];
        }
        return $token;
    }
}

if (!function_exists('format_data_values')) {
    function format_data_values($post, $column_name)
    {
        $output = [];
        $item = [];

        foreach ($post[$column_name] as $i => $key) {
            foreach ($post as $attr => $val) {
                $item[$attr] = $val[$i];
            }

            if (count($item) > 0) {
                array_push($output, $item);
            }
        }

        return $output;
    }
}

if (!function_exists('calculate_date_diff')) {
    function calculate_date_diff($start, $end = '', $type = 'minute')
    {
        if (empty($start)) {
            return 0;
        }

        $start_time = new DateTimeImmutable($start);
        $end_time = new DateTimeImmutable($end);

        if ($start_time > $end_time) {
            return 0;
        }

        $interval = $start_time->diff($end_time);

        $diff = 0;
        if ($type == 'minute') {
            $diff = $interval->days * 24 * 60;
            $diff += $interval->h * 60;
            $diff += $interval->i;
        } else if ($type == 'days') {
            $diff = $interval->days + 1;
        } else if ($type == 'months') {
            $diff = ($interval->format('%y') * 12) + $interval->format('%m');
        } else if ($type == 'years') {
            $diff = $interval->y;
        }

        return $diff;
    }
}

if (!function_exists('object_to_array')) {
    // https://stackoverflow.com/questions/19495068/convert-stdclass-object-to-array-in-php/28538844
    function object_to_array($d)
    {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            /*
             * Return array converted to object
             * Using __FUNCTION__ (Magic constant)
             * for recursive call
             */
            return array_map(__FUNCTION__, $d);
        } else {
            // Return array
            return $d;
        }
    }
}

if (!function_exists('sendgrid_email')) {
    function sendgrid_email($template_id, $email_datas = array())
    {
        $CI = get_instance();
        $CI->config->load('email');

        $from = new From("no-reply@sanz.com.sg", "Sanz Pte Ltd");

        $tos = [];
        foreach ($email_datas as $email_data) {
            $tos[] = new To(
                $email_data['recipient_email'],
                $email_data['recipient_email_name'],
                $email_data['body']
            );
        }

        $email = new Mail(
            $from,
            $tos
        );
        $email->setTemplateId($template_id);
        $sendgrid = new \SendGrid($CI->config->item('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }

        echo 'sendgrid-email : ', $response->statusCode();
    }
}

if (!function_exists('convert_to_slug')) {
    function convert_to_slug($str, $delimiter = '-')
    {
        // https://stackoverflow.com/questions/40641973/php-to-convert-string-to-slug
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }
}

if (!function_exists('rearray_files')) {
    function rearray_files(&$file_post)
    {
        $file_ary = array();
        if (isset($file_post['name']) && is_array($file_post['name'])) {
            $file_count = 0;
            $file_keys = array_keys($file_post);

            foreach ($file_post['name'] as $key => $value) {
                if ($value) {
                    $file_count += 1;
                }
            }

            for ($i = 0; $i < $file_count; $i++) {
                foreach ($file_keys as $key) {
                    $file_ary[$i][$key] = $file_post[$key][$i];
                }
            }
        }

        return $file_ary;
    }
}

if (!function_exists('time_ago_en')) {
    function time_ago_en($time)
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "age");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "100");

        $now = time();

        $difference = $time - $now;
        if ($difference > 0) {
            $tense = 'left';
        } elseif ($difference < 0) {
            // $tense = 'past';
            return false;
        }

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        $period = $periods[$j] . ($difference > 1 ? 's' : '');
        return "{$difference} {$period} {$tense} ";
    }
}

if (!function_exists('getStringsBetween')) {
    function getStringsBetween($string, $start, $end)
    {
        $pattern = sprintf(
            '/%s(.*?)%s/',
            preg_quote($start),
            preg_quote($end)
        );
        preg_match_all($pattern, $string, $matches);

        return $matches[1];
    }
}
