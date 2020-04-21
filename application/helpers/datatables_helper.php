<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('dt_number_format')) {
    function dt_number_format($number, $decimals = 2)
    {
        $number = (float) $number;

        return number_format($number, $decimals);
    }
}




// should format it via SQL, rather than front-end, allowing DT's search to work
if (!function_exists('dt_datestring_to_format')) {
    function dt_datestring_to_format($datetime, $format = 'j M Y, g:i A')
    {
        $output = '-';
        if (!empty(str_replace(array("0", "-", ":", " "), "", $datetime))) {
            $output = datestring_to_format($datetime, $format);
        }

        return $output;
    }
}




// should format it via SQL, rather than front-end, allowing DT's search to work
if (!function_exists('dt_timestamp_to_format')) {
    function dt_timestamp_to_format($timestamp, $format = 'j M Y, g:i A')
    {
        $output = timestamp_to_format($timestamp, $format);

        return $output;
    }
}




if (!function_exists('dt_str_pad')) {
    function dt_str_pad($number, $padding, $prefix = null, $hex = '#')
    {
        if (is_numeric($prefix)) {
            $prefix = $hex . $prefix;
        }

        if ($number) {
            return $prefix . str_pad($number, $padding, "0", STR_PAD_LEFT);
        }

        return '';
    }
}




if (!function_exists('dt_icon')) {
    function dt_icon($bool, $icon)
    {
        return ($bool) ? '<span class="' . $icon . '"></span>' : '';
    }
}




if (!function_exists('dt_ucfirst')) {
    function dt_ucfirst($string)
    {
        return ucfirst($string);
    }
}




if (!function_exists('dt_ucwords')) {
    function dt_ucwords($string)
    {
        return ucwords($string);
    }
}




if (!function_exists('dt_tools')) {
    function dt_tools($id, $controller, $methods = 'form|view|delete')
    {
        $controller = strtolower($controller);
        $methods    = explode('|', $methods);
        $output     = [];

        foreach ($methods as $method) {
            // defaults
            $attr = array(
                'title'       => '',
                'target'      => '_self',
            );

            switch ($method) {
                case 'form':
                    $title           = 'Edit';
                    $attr['class']   = 'm-r-sm';
                    break;
                case 'view':
                    $method          = ''; 
                    $title           = 'View';
                    $attr['class']   = 'm-r-sm';
                    break;
                case 'delete':
                    $title           = 'Delete';
                    $attr['onclick'] = "if (!confirm('Are you sure? This action will delete all related table.')) return false;";
                    break;
                default:
                    $title = '<span class="fa fa-fw fa-question"></span>';
                    break;
            }

            $url = $controller . '/'  . $id . ($method ? '/' . $method : '');
            $output[] = anchor($url, $title, $attr);
        }

        return implode('', $output);
    }
}


if (!function_exists('dt_check')) {
    function dt_check($bool)
    {
        if ((bool) $bool) {
            return '<span class="text-success fa fa-fw fa-check-circle"></span>';
        }

        return '<span class="text-danger fa fa-fw fa-times-circle"></span>';
    }
}


if (!function_exists('dt_anchor_voucher')) {
    function dt_anchor_voucher($voucher_id, $number, $transacted = null)
    {
        if ($voucher_id) {
            if ($transacted) {
                $number = '<span data-toggle="tooltip" class="text-success fa fa-fw fa-check-circle" title="Transacted on ' . date('j M Y', $transacted) . '"></span> ' . $number;
            }

            return anchor('expense/voucher/' . $voucher_id, $number);
        }

        return null;
    }
}


if (!function_exists('dt_anchor_tel')) {
    function dt_anchor_tel($tel, $title = null)
    {
        if ($tel) {
            $title = $title ?: $tel;
            return '<a href="tel:' . $tel . '">' . $title . '</a>';
        } else {
            return '';
        }
    }
}


if (!function_exists('dt_anchor_mailto')) {
    function dt_anchor_mailto($email, $title = null)
    {
        if ($email) {
            $title = ($title) ?: $email;
            return '<a href="mailto:' . $email . '">' . $title . '</a>';
        } else {
            return '';
        }
    }
}


if (!function_exists('dt_shorten_string')) {
    function dt_shorten_string($text, $max_length = null)
    {
        if ($text) {
            if (strlen($text) > $max_length) {
                return substr($text, 0, $max_length) . '...';
            } else {
                return $text;
            }
        } else {
            return '';
        }
    }
}



if (!function_exists('dt_name')) {
    function dt_name($id, $name, $controller)
    {
        if ($id) {
            return anchor($controller . '/' . $id, $name);
        } else {
            return '-';
        }
    }
}




if (!function_exists('dt_name_with_group')) {
    function dt_name_with_group($id, $user_name, $group_name, $controller)
    {
        return anchor($controller . '/' . $id, $user_name) . '<p class="m-b-none small">' . $group_name . '</p>';
    }
}




if (!function_exists('dt_active_format')) {
    function dt_active_format($bool)
    {
        if ((bool) $bool) {
            return '<span class="text-success">Active</span>';
        }

        return '<span class="text-danger">Inactive</span>';
    }
}




if (!function_exists('dt_date_range')) {
    function dt_date_range($date1, $date2, $format = 'j M Y, g:i A')
    {
        return dt_datestring_to_format($date1, $format) . ' - ' . dt_datestring_to_format($date2, $format);
    }
}




if (!function_exists('dt_action')) {
    function dt_action($module, $id, $functions, $route)
    {
        $function = explode('|', $functions);

        foreach($function as $key => $f){
            if (strtolower($f)=='edit'){
                $output[] = anchor($route . '/' . $id . '/form','<span class="m-r-sm">' . $f . '</span>', ['id'=>"edit-".$module, "data-id"=>$id]);
            }elseif (strtolower($f)=='delete'){
                $output[] = anchor($route . '/' . $id . '/delete','<span class="m-r-sm">' . $f . '</span>', ["onclick" => "if (!confirm('Are you sure? This action will delete all related table.')) return false;"]);
            }elseif (strtolower($f)=='view'){
                $output[] = anchor($route . '/' . $id,'<span class="m-r-sm">' . $f . '</span>', ['id'=>"view-".$module, "data-id"=>$id]);
            }else{
                $output[] = '';
            }
        }

        return implode('', $output);
    }
}




if (!function_exists('dt_name_modal')) {
    function dt_name_modal($id, $module, $function, $name)
    {
        return anchor('#','<span class="m-r-sm">' . $name . '</span>', ['id'=>$function."-".$module, "data-id"=>$id]);
    }
}
