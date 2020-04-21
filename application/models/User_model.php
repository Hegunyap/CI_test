<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends WebimpModel
{
    protected $_table = 'user';
    protected $soft_delete = true;

    protected $errors = [];

    protected $error_start_delimiter;
    protected $error_end_delimiter;


    public function __construct()
    {
        parent::__construct();

        $this->config->load('form_validation', true);
        $this->error_start_delimiter = $this->config->item('error_prefix', 'form_validation');
        $this->error_end_delimiter   = $this->config->item('error_suffix', 'form_validation');

        $this->config->load('account', true);
        $this->salt_length = $this->config->item('salt_length', 'account');
        $this->store_salt  = $this->config->item('store_salt', 'account');
        $this->hash_method = $this->config->item('hash_method', 'account');

        $this->load->library(['session', 'bcrypt']);
        $this->load->helper('cookie');
    }
    
    
    
    
    /**
     * Check if user is logged in.
     *
     * @access public
     * @return bool
     */
    public function logged_in()
    {
        return (bool) $this->session->userdata('user_id');
    }
}
