<?php
/**
 * A base controller for CodeIgniter with view autoloading, layout support,
 * model loading, helper loading, asides/partials and per-controller 404
 *
 * Modified by 
 *
 * @link      http://github.com/jamierumbelow/codeigniter-base-controller
 * @copyright Copyright (c) 2012, Jamie Rumbelow <http://jamierumbelow.net>
 */

class MY_Controller extends CI_Controller
{

    /* --------------------------------------------------------------
     * VARIABLES
     * ------------------------------------------------------------ */

    /**
     * Show profiler
     *
     * (default value: false)
     *
     * @var    bool
     * @access public
     */
    public $profiler_option = false;

    /**
     * Show notifications for sidebar
     *
     * (default value: true)
     *
     * @var    bool
     * @access public
     */
    public $show_notifications = true;

    /**
     * An array of style handles to enqueue.
     *
     * @var array
     */
    public $styles = ['sanz'];

    /**
     * An array of script handles to enqueue.
     *
     * @var array
     */
    public $scripts = ['sanz'];

    /**
     * The current request's view. Automatically guessed
     * from the name of the controller and action
     */
    protected $view = '';

    /**
     * An array of variables to be passed through to the
     * view, layout and any asides
     */
    protected $data = array();

    /**
     * The name of the layout to wrap around the view.
     */
    protected $layout;

    /**
     * An arbitrary list of asides/partials to be loaded into
     * the layout. The key is the declared name, the value the file
     */
    protected $asides = array();

    /**
     * A list of models to be autoloaded
     */
    protected $models = array();

    /**
     * A formatting string for the model autoloading feature.
     * The percent symbol (%) will be replaced with the model name.
     */
    protected $model_string = '%_model';

    /**
     * A list of helpers to be autoloaded
     */
    protected $helpers = array();

    /**
     * Array of messages to set in alert box
     */
    protected $alert = array();

    /**
     * Array of breadcrumbs, auto-loaded into view
     */
    protected $breadcrumbs = array();




    /* --------------------------------------------------------------
     * GENERIC METHODS
     * ------------------------------------------------------------ */

    /**
     * Initialise the controller, tie into the CodeIgniter superobject
     * and try to autoload the models and helpers
     */
    public function __construct()
    {
        parent::__construct();

        $this->load_models();
        $this->load_helpers();

        // $this->breadcrumbs[] = anchor(site_url(), 'Home');

        if (ENVIRONMENT == 'production') {
            $this->load->driver('cache', ['adapter' => 'memcached']);
        } else {
            $this->load->driver('cache', ['adapter' => 'file']);
        }

        // load csrf
        $this->data['csrf'] = [
            'name'  => $this->security->get_csrf_token_name(),
            'value' => $this->security->get_csrf_hash(),
        ];
    }




    /**
     * Set the alert.
     *
     * @access public
     * @param  string $description
     * @param  string $status      (default: 'warning')
     * @param  bool   $flash       (default: false)
     * @return void
     */
    public function set_alert($description = null, $status = 'warning', $flash = false)
    {
        $this->load->library('session');

        $description = (string) trim($description);

        if ($description) {
            $alert = [
                'status'      => $status,
                'description' => $description,
            ];

            if ($flash) {
                $this->session->set_flashdata('alert', $alert);
            } else {
                $this->data['alert'] = $alert;
            }
        }
    }




    /**
     * Get and set the alert variable in controller.
     *
     * @access private
     * @return array
     */
    private function get_alert()
    {
        if (isset($this->data['alert']['description'])
            && !empty($this->data['alert']['description'])
        ) {
            $this->data['alert'] = $this->data['alert'];
        } else {
            $this->data['alert'] = $this->session->flashdata('alert');
        }
    }




    /**
     * Add links to breachcrumb.
     *
     * @access public
     * @param  string $anchor
     * @return void
     */
    public function add_breadcrumb($anchor)
    {
        if (is_array($anchor)) {
            foreach ($anchor as $link) {
                $this->breadcrumbs[] = $link;
            }
        } elseif (is_string($anchor)) {
            $this->breadcrumbs[] = $anchor;
        }
    }




    /**
     * Set array of breadcrumbs for controller.
     *
     * @access private
     * @return void
     */
    private function get_breadcrumbs()
    {
        $this->data['page']['breadcrumbs'] = $this->breadcrumbs;
    }




    /**
     * Add script handles to the controller before loading view.
     *
     * @param array|string $handle The script handle string or an
     *                             array of script handles.
     */
    public function add_scripts($handles)
    {
        if (is_string($handles)) {
            $handles = [$handles];
        }

        foreach ($handles as $handle) {
            if (!in_array($handle, $this->scripts)) {
                array_push($this->scripts, trim($handle));
            }
        }
    }




    /**
     * Add styles handles to the controller before loading view.
     *
     * @param array|string $handle The style handle string or an
     *                             array of style handles.
     */
    public function add_styles($handles)
    {
        if (is_string($handles)) {
            $handles = [$handles];
        }

        foreach ($handles as $handle) {
            if (!in_array($handle, $this->styles)) {
                array_push($this->styles, trim($handle));
            }
        }
    }




    /* --------------------------------------------------------------
     * VIEW RENDERING
     * ------------------------------------------------------------ */

    /**
     * Override CodeIgniter's dispatch mechanism and route the request
     * through to the appropriate action. Support custom 404 methods and
     * autoload the view into the layout.
     */
    public function _remap($method)
    {
        if (method_exists($this, $method)) {
            call_user_func_array(array($this, $method), array_slice($this->uri->rsegments, 2));
        } else {
            if (method_exists($this, '_404')) {
                call_user_func_array(array($this, '_404'), array($method));
            } else {
                show_404(strtolower(get_class($this)).'/'.$method);
            }
        }

        $this->_load_view();
    }




    /**
     * Automatically load the view, allowing the developer to override if
     * he or she wishes, otherwise being conventional.
     */
    protected function _load_view()
    {
        // If $this->view == false, we don't want to load anything
        if ($this->view !== false) {
            $this->register_assets();
            $this->enqueue_assets();

            $this->load->library('session'); // comment out this line if no session table in DB

            $this->get_notifications();

            $this->get_alert();

            $this->get_breadcrumbs();

            $this->output->enable_profiler($this->profiler_option);

            // If $this->view isn't empty, load it. If it isn't, try and guess based on the controller and action name
            $view = (!empty($this->view)) ? $this->view : $this->router->directory . 'controllers/' . strtolower($this->router->class) . '/' . strtolower($this->router->method);

            // Load the view into $yield
            $data['yield'] = $this->load->view($view, $this->data, true);

            // Do we have any asides? Load them.
            if (!empty($this->asides)) {
                foreach ($this->asides as $name => $file) {
                    $data['yield_'.$name] = $this->load->view($file, $this->data, true);
                }
            }

            // Load in our existing data with the asides and view
            $data = array_merge($this->data, $data);
            $layout = false;

            // If we didn't specify the layout, try to guess it
            if (!isset($this->layout)) {
                if (file_exists(APPPATH . 'views/layouts/' . $this->router->class . '.php')) {
                    $layout = 'layouts/' . $this->router->class;
                } elseif ($this->user_model->logged_in()
                    && file_exists(APPPATH . 'views/layouts/private.php')
                ) {
                    $layout = 'layouts/private';
                } else {
                    $layout = 'layouts/public';
                }
            } elseif ($this->layout !== false) { // If we did, use it
                $layout = $this->layout;
            }

            // If $layout is false, we're not interested in loading a layout, so output the view directly
            if ($layout == false) {
                $this->output->set_output($data['yield']);
            } else { // Otherwise? Load away :)
                $this->load->view($layout, $data);
            }
        }
    }




    /**
     * Register scripts and styles for the application. These assets are managed by Grunt.
     */
    private function register_assets()
    {
        $version = get_app_version();

        // main app
        // register_script('sanz', assets_url("js/sanz.min.js"), ['jquery', 'toastr', 'sweetalert'], $version, false);
        // register_style('sanz', assets_url("css/sanz.min.css"), ['vendor', 'daterangepicker'], $version);

        // vendors
        register_style('vendor', assets_url("css/vendor.min.css"), [], $version);

        // datatables
        // register_script('datatables', assets_url("js/datatables.min.js"), ['jquery', 'sanz'], '1.10.16', false);
        // register_script('datatables-hr', assets_url("js/datatables/hr.min.js"), ['datatables'], $version, false);
        // register_script('datatables-finance', assets_url("js/datatables/finance.min.js"), ['datatables'], $version, false);
        // register_script('datatables-accounting', assets_url("js/datatables/accounting.min.js"), ['datatables'], $version, false);
        // register_script('datatables-customer', assets_url("js/datatables/customer.min.js"), ['datatables'], $version, false);
        // register_script('datatables-product', assets_url("js/datatables/product.min.js"), ['datatables'], $version, false);
        // register_script('datatables-quotation', assets_url("js/datatables/quotation.min.js"), ['datatables'], $version, false);
        // register_script('datatables-sales', assets_url("js/datatables/sales.min.js"), ['datatables'], $version, false);
        // register_script('datatables-supplier', assets_url("js/datatables/supplier.min.js"), ['datatables'], $version, false);
        // register_script('datatables-setting', assets_url("js/datatables/setting.min.js"), ['datatables'], $version, false);
        
        // modules
        register_script('modules', assets_url("js/modules/base.min.js"), ['jquery', 'sanz'], $version, false);
        // register_script('modules-leave', assets_url("js/modules/leave.min.js"), ['modules'], $version, false);
        // register_script('modules-staff', assets_url("js/modules/staff.min.js"), ['modules'], $version, false);
        // register_script('modules-payroll', assets_url("js/modules/payroll.min.js"), ['modules'], $version, false);
        // register_script('modules-timesheet', assets_url("js/modules/timesheet.min.js"), ['modules', 'XLSX'], $version, false);
        // register_script('modules-fixed-asset', assets_url("js/modules/fixed_asset.min.js"), ['modules'], $version, false);
        // register_script('modules-profit-and-loss', assets_url("js/modules/profit_and_loss.min.js"), ['modules'], $version, false);

        // register_script('modules-account', assets_url("src/js/modules/account.js"), ['modules'], $version, false);
        // register_script('modules-product', assets_url("js/modules/product.min.js"), ['modules', 'XLSX'], $version, false);
        // register_script('modules-supplier', assets_url("js/modules/supplier.min.js"), ['modules'], $version, false);

        // jquery
        register_script('jquery', assets_url("js/jquery.min.js"), [], '3.3.1', false);

        // jquery-ui
        register_script('jquery-ui', assets_url("js/jquery-ui.min.js"), ['jquery'], '1.12.1', false);

        // toastr
        register_script('toastr', assets_url("js/toastr.min.js"), ['jquery'], '2.1.4', false);

        // sweet alert
        register_script('sweetalert', assets_url("js/sweetalert.min.js"), ['jquery'], '2.1.0', false);

        // select2
        register_script('select2', assets_url("js/select2.min.js"), ['jquery'], '4.0.5', false);

        // font awesome 5
        register_style('font-awesome', assets_url("css/vendor.min.css"), ['jquery'], '5.8.1', false);

        // XLSX Excel Sheet
        register_script('XLSX', assets_url("js/xlsx.full.min.js"), [], '0.14.3', false);

        // Full Calendar
        register_style('fullcalendar', assets_url("css/fullcalendar.min.css"), [], $version);
        register_script('fullcalendar', assets_url("js/fullcalendar.min.js"), [], $version, false);

        // highcharts
        register_script('highcharts', assets_url("js/highcharts.js"), [], '6.0.6', false);
        register_script('highcharts-superadmin', assets_url("js/highcharts/superadmin.min.js"), ['highcharts'], $version, false);
        register_script('highcharts-clientstaff', assets_url("js/highcharts/clientstaff.min.js"), ['highcharts'], $version, false);

        // Full Calendar
        register_style('daterangepicker', assets_url("css/daterangepicker.min.css"), [], $version);
        register_script('daterangepicker', assets_url("js/daterangepicker.min.js"), [], $version, false);

    }




    /**
     * Enqueue scripts and styles for the application.
     */
    private function enqueue_assets()
    {
        foreach ((array) $this->scripts as $script) {
            enqueue_script($script);
        }

        foreach ((array) $this->styles as $style) {
            enqueue_style($style);
        }
    }




    /* --------------------------------------------------------------
     * MODEL LOADING
     * ------------------------------------------------------------ */

    /**
     * Load models based on the $this->models array
     */
    private function load_models()
    {
        foreach ($this->models as $model) {
            $this->load->model($this->_model_name($model));
        }
    }




    /**
     * Returns the loadable model name based on
     * the model formatting string
     */
    protected function _model_name($model)
    {
        return str_replace('%', $model, $this->model_string);
    }




    /* --------------------------------------------------------------
     * HELPER LOADING
     * ------------------------------------------------------------ */

    /**
     * Load helpers based on the $this->helpers array
     */
    private function load_helpers()
    {
        foreach ($this->helpers as $helper) {
            $this->load->helper($helper);
        }
    }




    /**
     * getter for sidebar notifications.
     *
     * @access private
     * @return void
     */
    private function get_notifications()
    {
        if ($this->show_notifications == true) {
            $this->load->library(['notification']);
            $this->data['notification'] = $this->notification->get_all();
        }
    }
}
