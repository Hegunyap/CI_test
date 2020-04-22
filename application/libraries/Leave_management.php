<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_management
{
    private $_CI;

    protected $_data = [];
    
    protected $_hired_date = '';
    protected $_start_date;
    protected $_end_date;
    
    protected $_user_id;

    protected $validation_data = array();
    
    protected $_error_array = array();

    /**
	 * Start tag for error wrapping
	 *
	 * @var string
	 */
	protected $_error_prefix	= '<p>';

	/**
	 * End tag for error wrapping
	 *
	 * @var string
	 */
	protected $_error_suffix	= '</p>';

    protected $_types = array(
        'annual' => array(
            'pro_rated' => true,
            'min_work_day'  => 0, 
            'max_leave' => array(
                'oab' => 10,
                'e' => 14,
                'maa' => 18,
            ),
            'max_leave_cap' => array(
                'oab' => 14,
                'e' => 18,
                'maa' => 21,
            ),
            'advance_day' => array(
                'more_than_5' => 30,
                'general' => 3,
            ),
        ),
        'sick' => array(
            'min_work_day'  => 90,
            'max_leave'     => 14,
        ),
        'marriage' => array(
            'min_work_day'  => 90,
            'max_leave'     => 3,
        ),
        'maternity' => array(
            'min_work_day'  => 90,
            'sex'           => 'female',
        ),
        'paternity' => array(
            'min_work_day'  => 90,
            'max_leave'     => 10,
            'sex'           => 'male',
            'advance_day'   => 3,
        ),
        'shared_parental' => array(
            'min_work_day'  => 0, // means no minimum working day is needed
            'max_leave'     => 20,
        ),
        'childcare' => array(
            'min_work_day'  => 90,
            'max_leave'     => 6,
        ),
        'family_care' => array(
            'min_work_day'  => 90,
            'max_leave'     => 5,
        ),
        'compassionate' => array(
            'min_work_day'  => 90,
            'max_leave'     => 6,
        ),
        'unpaid' => array(
            'min_work_day'  => 0, // means no minimum working day is needed
            'max_leave'     => 7,
            'advance_day'   => 14,
        ),
        'reservist' => array(
            'min_work_day'  => 0, // means no minimum working day is needed
            'max_leave'     => 'unlimited',
            'advance_day'   => 30,
        ),
        'examination' => array(
            'min_work_day'  => 0, // means no minimum working day is needed
            'max_leave'    => 7
        ),
    );

    protected $_current_user;
    public function __construct()
    {
        // Get the CodeIgniter reference
        $this->_CI = &get_instance();
        $this->_CI->load->model('leave_type_model');
        $this->_CI->load->model('leave_model');
        $this->_CI->load->model('user_model');

        $this->_current_user = $this->_CI->session->userdata();
    }




    public function set_hired_date($hired_date = null)
    {
        $this->_hired_date = $hired_date;
        
        return $this;
    }




    public function set_leave_date($start_date, $end_date)
    {
        $this->_start_date = $start_date;
        $this->_end_date = $end_date;

        return $this;
    }



    public function set_user_id($user_id)
    {
        $this->_user_id = $user_id;

        return $this;
    }


    
    
    public function get_leave_type($leave_type_id = null)
    {
        if ($leave_type_id) {
            if ($lt = $this->_CI->leave_type_model->get($leave_type_id)) {
                $leave_left = $this->count_leave_left($lt->id);
                $leave_type_data = [
                    'value' => $lt->id,
                    'text' => $lt->description . (($leave_left > 0) ? " ($leave_left)" : ($leave_left === 'unlimited' ? "" : " (0)")),
                    'has_file' => $lt->has_file,
                    'disabled' => ($leave_left <= 0 && $leave_left !== '') ? 'disabled' : '',
                ];
            }
        } else {
            $all_types = $this->_CI->leave_type_model->get_all();
            foreach ($all_types as $lt) {
                $leave_left = $this->count_leave_left($lt->id);
                $leave_type_data[] = [
                    'value' => $lt->id,
                    'text' => $lt->description . (($leave_left > 0) ? " ($leave_left)" : ($leave_left === 'unlimited' ? "" : " (0)")),
                    'has_file' => $lt->has_file,
                    'disabled' => ($leave_left <= 0 && $leave_left !== 'unlimited') ? 'disabled' : '',
                ];
            }
        }

        return $leave_type_data;
    }



    /**
     * @params $leave_type_id
     * @params $current_date (If not specified, then will be assumed as today's date. Useful to count previous year unconsumed l)
     */
    public function count_leave_left($leave_type_id, $current_date = '')
    {
        $leave_type = $this->_CI->leave_type_model->get($leave_type_id);
        $total_leave_left = 0;
        
        if ( isset($this->_types[$leave_type->name]) && $this->_user_id && $this->_start_date && $this->_end_date) {
            $user = $this->_CI->user_model->get($this->_user_id);
            //Check creator of this leave, HR Manager or A Supervisor of an user ? If True, then ignore the validation.

            if ( !($this->_CI->user_model->grouped_in(['group_name' => ['manager'], 'dept_name' => ['hr']]) 
                || $this->_CI->user_model->is_a_supervisor(isset($this->_current_user['user_id']) ? $this->_current_user['user_id'] : null, $this->_user_id)) ) // Check if user who POST-ing is the supervisor
            {
                // Check the rules of min work day, if any
                if (( isset($this->_types[$leave_type->name]['min_work_day']) && $this->_types[$leave_type->name]['min_work_day'] > 0 )) {
                    if ($this->_hired_date) {
                        $hired_date = new Datetime($this->_hired_date);
                        $allowed_take_leave_date = $hired_date->add(new DateInterval('P' . $this->_types[$leave_type->name]['min_work_day'] . 'D'));
                        
                        if ( $allowed_take_leave_date > new Datetime($current_date) ) {
                            return 0;
                        }
                    } else {
                        return 0;
                    }
                } 

                //Check the rules of sex, if any
                if ( isset($this->_types[$leave_type->name]['sex']) ) {
                    if ($user = $this->_CI->user_model->get($this->_user_id)) {
                        if ($user->personal_sex !== $this->_types[$leave_type->name]['sex']) {
                            return 0;
                        }
                    }
                } 
            }

            $leave_takens = $this->_CI->leave_model->get_many_by([
                'user_id' => $this->_user_id,
                'leave_type_id' => $leave_type_id,
                'status' => 'approved',
                'start_date >=' => $this->_start_date,
                'end_date <=' => $this->_end_date,
            ]);
            
            $consumed_annual_leave = 0;
            foreach ($leave_takens as $leave) {
                $consumed_annual_leave += $leave->consumed_annual_leave;
            }

            if (isset($this->_types[$leave_type->name]['max_leave'])) {
                $max_leave = $this->_types[$leave_type->name]['max_leave'];
                if (isset($this->_types[$leave_type->name]['pro_rated']) && isset($max_leave[$user->employment_job_grade]) && isset($this->_types[$leave_type->name]['max_leave_cap'][$user->employment_job_grade])) {
                    $max_leave_cap = $this->_types[$leave_type->name]['max_leave_cap'];
                    $days_per_month = 30;
                    $days_per_year = $days_per_month * 12;

                    if ($leave_type->name === 'annual') {
                        // If staff has worked > 1 year, then add extra one day to the annual leave, until it reach the maximum cap defined.
                        $bonus_annual_leave = floor((calculate_date_diff($this->_hired_date, $current_date, 'days')) / $days_per_year);

                        // Compare the hired date with the current date - then find the difference in month
                        // Rules : 
                        // * Full months means on average 30 days per month
                        // * More than 3 months but less than a year -> Based on the number of full months you have worked.
                        // * More than a year -> Based on the number of full months you worked in your current year.
                        $full_month = floor((calculate_date_diff($this->_hired_date, $current_date, 'days')) / $days_per_month);
                        $full_month = ($full_month > 12) ? 12 : $full_month;

                        // Count Max Leave Available. Cannnot be more than the CAP
                        $max_leave = $max_leave[$user->employment_job_grade] + $bonus_annual_leave;
                        $max_leave = ($max_leave >= $max_leave_cap[$user->employment_job_grade]) ? $max_leave_cap[$user->employment_job_grade] : $max_leave;
                        $annual_leave = round((($full_month / 12) * ($max_leave)), 0, PHP_ROUND_HALF_UP);

                        $total_leave_left =  $annual_leave - $consumed_annual_leave + ($user->unconsumed_annual_leave ?: 0);
                    }
                } else {
                    if ($max_leave === 'unlimited') {
                        $total_leave_left = 'unlimited';
                    } else if (is_int($max_leave)) {
                        $total_leave_left = $max_leave - $consumed_annual_leave;
                    }
                }
            }
        }
        
        return $total_leave_left;
    }




    public function set_data(array $data)
	{
		if ( ! empty($data))
		{
			$this->validation_data = $data;
		}

		return $this;
	}




    public function validate()
    {
        $validation_array = empty($this->validation_data)
			? $_POST
            : $this->validation_data;

        if ( !empty($validation_array) ) {
            $leave_type = $this->_CI->leave_type_model->get($validation_array['leave_type_id']);

            $start_date = $validation_array['start_date'];
            $end_date = $validation_array['end_date'];
            $total_leave_selected = calculate_date_diff($start_date, $end_date, 'days');
            $total_leave_left = $this->count_leave_left($leave_type->id);
            
            if ( isset($this->_types[$leave_type->name]) ) {
                if ( !($this->_CI->user_model->grouped_in(['group_name' => ['manager'], 'dept_name' => ['hr']]) 
                    || $this->_CI->user_model->is_a_supervisor(isset($this->_current_user['user_id']) ? $this->_current_user['user_id'] : null, $this->_user_id)) ) {
                    
                    if ( isset($this->_types[$leave_type->name]['min_work_day']) && $this->_types[$leave_type->name]['min_work_day'] > 0 ) {
                        if ($this->_hired_date) {
                            $current_date = new Datetime('');
                            $hired_date = new Datetime($this->_hired_date);
                            $allowed_take_leave_date = $hired_date->add(new DateInterval('P' . $this->_types[$leave_type->name]['min_work_day'] . 'D'));
                            
                            if ($allowed_take_leave_date > $current_date) {
                                $this->_error_array[] = "You need to work at least {$this->_types[$leave_type->name]['min_work_day']} days from your hired date for {$leave_type->description}";
                            }
                        }
                    }

                    if (($total_leave_left !== 'unlimited') && ($total_leave_selected > $total_leave_left)) {
                        $this->_error_array[] = "You only have {$total_leave_left} days left for {$leave_type->description}";
                    }
        
                    if ( isset($this->_types[$leave_type->name]['advance_day']) ) {
                        $advance_day = $this->_types[$leave_type->name]['advance_day'];
                        if (isset($advance_day['more_than_5']) || isset($advance_day['general'])) {
                            if ($total_leave_selected >= 5) {
                                $advance_day = $advance_day['more_than_5'];
                            } else {
                                $advance_day = $advance_day['general'];
                            }
                        } 

                        if (is_int($advance_day)) {
                            $current_date = new Datetime('');
                            $advance_date = $current_date->add(new DateInterval('P' . $advance_day . 'D'));
                            $start_date = new Datetime($start_date);

                            if ($start_date < $advance_date) {
                                $this->_error_array[] =  "You must apply {$advance_day} days in advance for {$leave_type->description}";
                            }
                        }
                    }
                }
            } else {
                $this->_error_array[] =  "You have not selected any of the leave type";
            }
        }

        if (count($this->_error_array) > 0) {
            return FALSE;
        } 
        return TRUE;
    }



    /**
	 * Error String
	 *
	 * Returns the error messages as a string, wrapped in the error delimiters
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function error_string($prefix = '', $suffix = '')
	{
		// No errors, validation passes!
		if (count($this->_error_array) === 0)
		{
			return '';
		}

		if ($prefix === '')
		{
			$prefix = $this->_error_prefix;
		}

		if ($suffix === '')
		{
			$suffix = $this->_error_suffix;
		}

		// Generate the error string
		$str = '';
		foreach ($this->_error_array as $val)
		{
			if ($val !== '')
			{
				$str .= $prefix.$val.$suffix."\n";
			}
		}

		return $str;
	}
}