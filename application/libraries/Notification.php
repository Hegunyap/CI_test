<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Get all notifications for logged in user
 */
class Notification
{
    public $models = [
        'user',
    ];


    public $libraries = [
        'session',
    ];




    public function __construct()
    {
        // pointer to main instance
        $this->ci =& get_instance();

        $this->load_models();
        $this->load_libraries();

        $this->current_user = $this->ci->session->userdata();
    }




    /**
     * Get all the notification counts for current user session.
     *
     * @access public
     * @return void
     */
    public function get_all()
    {
        $output = new stdClass();

        return $output;
    }




    /**
     * Load all models required.
     *
     * @access private
     * @return void
     */
    private function load_models()
    {
        foreach($this->models as $model) {
            $models[] = $model . '_model';
        }
        $this->ci->load->model($models);
    }




    /**
     * Load all libraries required.
     *
     * @access private
     * @return void
     */
    private function load_libraries()
    {
        $this->ci->load->library($this->libraries);
    }
}
