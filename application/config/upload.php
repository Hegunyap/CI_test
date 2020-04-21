<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine') !== FALSE) {
    $config['upload_path']   = GS_BUCKET . "/uploads/";
} else {
    $config['upload_path']   = "./uploads/";
}

$config['allowed_types']    = ['gif','jpg','png','pdf'];
$config['max_size']         = '100000000';
$config['max_width']        = '1024';
$config['max_height']       = '768';
$config['max_filename']     = '250';
$config['file_ext_tolower'] = TRUE;
