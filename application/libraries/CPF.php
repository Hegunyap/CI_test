<?php

defined('BASEPATH') or exit('No direct script access allowed');


/**
 * Calculate the CPF / SDL for the month. Private sector.
 * 
 * Last Updated: 28 Jun 2018
 */
class CPF
{
    private $employer_percent;
    private $employee_percent;

    // SDL settings
    private $sdl_percent    = 0.0025;
    private $sdl_min_payout = 2;
    private $sdl_max_payout = 11.25;
    private $sdl_min_wage   = 800;
    private $sdl_max_wage   = 4500;

    // cpf-payable
    protected $dob             = null; // DateTime object
    protected $basic           = 0;
    protected $bonus           = 0;
    protected $cash_incentives = 0;
    protected $allowance_pre   = 0;
    protected $allowance_post  = 0;
    protected $commission      = 0;
    protected $has_cpf         = 0;




    /**
     * Set has CPF or not
     * 
     * @access public
     * @param float $basic
     * @return obj
     */
    public function setHasCPF($has_cpf)
    {
        $this->has_cpf = (int) $has_cpf;

        return $this;
    }




    /**
     * Set the basic salary.
     * 
     * @access public
     * @param float $basic
     * @return obj
     */
    public function setBasic($basic)
    {
        $this->basic = $basic;

        return $this;
    }




    /**
     * Set the date of birth.
     * 
     * @access public
     * @param int $dob (unix timestamp)
     * @return obj
     */
    public function setDateOfBirth($dob)
    {
        $this->dob      = new DateTime($dob);

        return $this;
    }




    /**
     * Set allowances.
     * 
     * @access public
     * @param float $pre (default: 0)
     * @param float $post (default: 0)
     * @return obj
     */
    public function setAllowance($pre = 0, $post = 0)
    {
        $this->allowance_pre  = $pre;
        $this->allowance_post = $post;

        return $this;
    }




    /**
     * Set commission.
     * 
     * @access public
     * @param float $commission (default: 0)
     * @return obj
     */
    public function setCommission($commission = 0)
    {
        $this->commission = $commission;

        return $this;
    }




    /**
     * Calculate the SDL.
     * 
     * @access public
     * @return float
     */
    public function get_sdl()
    {
        if (!$this->has_cpf) {
            return 0;
        } 

        $total_wage = $this->getOrdinaryWage() + $this->getAdditionalWage() + $this->getPostCpfWage();

        // cap wage to max wage: $4.5k
        $sdl_wage = ($total_wage > $this->sdl_max_wage) ? $this->sdl_max_wage : $total_wage;

        $payout = $sdl_wage * $this->sdl_percent;
        // format it to 2 decimal number, ensure CPF to always 2 that format
        $payout = number_format($payout, 2);

        // Wage <= $800
        if ($sdl_wage < $this->sdl_min_wage) {
            // Minimum payout: $2
            $payout = $this->sdl_min_payout;
        }

        return $payout;
    }




    /**
     * Calculate the SHGs.
     * 
     * @access public
     * @return float
     */
    public function get_shgs($group)
    {
        if (!$this->has_cpf) {
            return 0;
        } 

        $total_wage = $this->getOrdinaryWage() + $this->getAdditionalWage() + $this->getPostCpfWage();

        switch ($group) {
            case 'CDAC':
                if ($total_wage > 7500) {
                    $contribution = 3.00;
                } elseif ($total_wage > 5000) {
                    $contribution = 2.00;
                } elseif ($total_wage > 3500) {
                    $contribution = 1.50;
                } elseif ($total_wage > 2000) {
                    $contribution = 1.00;
                } else {
                    $contribution = 0.50;
                }
                break;
            case 'ECF':
                if ($total_wage > 10000) {
                    $contribution = 20.00;
                } elseif ($total_wage > 7000) {
                    $contribution = 16.00;
                } elseif ($total_wage > 4000) {
                    $contribution = 12.00;
                } elseif ($total_wage > 2500) {
                    $contribution = 9.00;
                } elseif ($total_wage > 1500) {
                    $contribution = 6.00;
                } elseif ($total_wage > 1000) {
                    $contribution = 4.00;
                } else {
                    $contribution = 2.00;
                }
                break;
            case 'MBMF':
                if ($total_wage > 10000) {
                    $contribution = 26.00;
                } elseif ($total_wage > 8000) {
                    $contribution = 24.00;
                } elseif ($total_wage > 6000) {
                    $contribution = 22.00;
                } elseif ($total_wage > 4000) {
                    $contribution = 19.50;
                } elseif ($total_wage > 3000) {
                    $contribution = 15.00;
                } elseif ($total_wage > 2000) {
                    $contribution = 6.50;
                } elseif ($total_wage > 1000) {
                    $contribution = 4.50;
                } else {
                    $contribution = 3.00;
                }
                break;
            case 'SINDA':
                if ($total_wage > 15000) {
                    $contribution = 30.00;
                } elseif ($total_wage > 10000) {
                    $contribution = 18.00;
                } elseif ($total_wage > 7500) {
                    $contribution = 12.00;
                } elseif ($total_wage > 4500) {
                    $contribution = 9.00;
                } elseif ($total_wage > 2500) {
                    $contribution = 7.00;
                } elseif ($total_wage > 1500) {
                    $contribution = 5.00;
                } elseif ($total_wage > 1000) {
                    $contribution = 3.00;
                } else {
                    $contribution = 1.00;
                }
                break;
            default:
                $contribution = 0;
                break;
        }

        return $contribution;
    }




    /**
     * Calculate the CPF contribution.
     * 
     * @access public
     * @return obj
     */
    public function get_contributions()
    {
        $ordinary      = $this->getOrdinaryWage();
        $additional    = $this->getAdditionalWage();
        $contributions = $this->calculateContributions($this->dob, $ordinary, $additional);

        return $contributions;
    }



    /**
     * Filtering and returning contributions.
     * 
     * @access private
     * @param obj $dob
     * @param float $ordinary
     * @param float $additional
     * @return obj
     */
    private function calculateContributions($dob, $ordinary, $additional)
    {
        $today         = new DateTime();
        $interval      = $dob->diff($today);
        $age           = $interval->y;

        $ordinary = ($ordinary > 6000) ? 6000 : $ordinary; // capped at OW Ceiling of $6,000
        $total_wage    = $ordinary + $additional;

        $contributions = new stdClass();

        if (!$this->has_cpf) {
            $contributions->employer = 0;
            $contributions->employee = 0;
            $contributions->total = 0;

            return $contributions;
        } 

        if ($age > 65) {
            if ($total_wage >= 750) {
                // total contributions
                $o_t                  = $ordinary * 0.125;
                $a_t                  = $additional * 0.125;
                $contributions->total = round($o_t + $a_t);

                // employee contributions
                $o_e                     = $ordinary * 0.05;
                $a_e                     = $additional * 0.05;
                $contributions->employee = $this->dropCents($o_e + $a_e);
            } elseif ($total_wage > 500) {
                $contributions->employee = $this->dropCents(0.15 * ($total_wage - 500));
                $contributions->total    = round(($total_wage * 0.075) + $contributions->employee);
            } elseif ($total_wage > 50) {
                $contributions->total    = round($total_wage * 0.075);
                $contributions->employee = 0;
            } else {
                $contributions->total    = 0;
                $contributions->employee = 0;
            }
        } elseif ($age > 60 && $age <= 65) {
            if ($total_wage >= 750) {
                // total contributions
                $o_t                  = $ordinary * 0.165;
                $a_t                  = $additional * 0.165;
                $contributions->total = round($o_t + $a_t);

                // employee contributions
                $o_e                     = $ordinary * 0.075;
                $a_e                     = $additional * 0.075;
                $contributions->employee = $this->dropCents($o_e + $a_e);
            } elseif ($total_wage > 500) {
                $contributions->employee = $this->dropCents(0.225 * ($total_wage - 500));
                $contributions->total    = round(($total_wage * 0.09) + $contributions->employee);
            } elseif ($total_wage > 50) {
                $contributions->total    = round($total_wage * 0.09);
                $contributions->employee = 0;
            } else {
                $contributions->total    = 0;
                $contributions->employee = 0;
            }
        } elseif ($age > 55 && $age <= 60) {
            if ($total_wage >= 750) {
                // total contributions
                $o_t                  = $ordinary * 0.26;
                $a_t                  = $additional * 0.26;
                $contributions->total = round($o_t + $a_t);

                // employee contributions
                $o_e                     = $ordinary * 0.13;
                $a_e                     = $additional * 0.13;
                $contributions->employee = $this->dropCents($o_e + $a_e);
            } elseif ($total_wage > 500) {
                $contributions->employee = $this->dropCents(0.39 * ($total_wage - 500));
                $contributions->total    = round(($total_wage * 0.13) + $contributions->employee);
            } elseif ($total_wage > 50) {
                $contributions->total    = round($total_wage * 0.13);
                $contributions->employee = 0;
            } else {
                $contributions->total    = 0;
                $contributions->employee = 0;
            }
        } else {
            if ($total_wage >= 750) {
                // total contributions
                $o_t                  = $ordinary * 0.37;
                $a_t                  = $additional * 0.37;
                $contributions->total = round($o_t + $a_t);

                // employee contributions
                $o_e                     = $ordinary * 0.2;
                $a_e                     = $additional * 0.2;
                $contributions->employee = $this->dropCents($o_e + $a_e);
            } elseif ($total_wage > 500) {
                $contributions->employee = $this->dropCents(0.6 * ($total_wage - 500));
                $contributions->total    = round(($total_wage * 0.17) + $contributions->employee);
            } elseif ($total_wage > 50) {
                $contributions->total    = round($total_wage * 0.17);
                $contributions->employee = 0;
            } else {
                $contributions->total    = 0;
                $contributions->employee = 0;
            }
        }

        $contributions->employer = $contributions->total - $contributions->employee;

        return $contributions;
    }




    /**
     * Returns total income, grouped under "ordinary wage".
     * 
     * @access private
     * @return float
     */
    private function getOrdinaryWage()
    {
        return $this->basic;
    }




    /**
     * Returns total income, grouped under "additional wage".
     * 
     * @access private
     * @return float
     */
    private function getAdditionalWage()
    {
        return $this->bonus + $this->cash_incentives + $this->allowance_pre + $this->commission;
    }




    /**
     * Returns remaining income not considered in CPF.
     * 
     * @access private
     * @return float
     */
    private function getPostCpfWage()
    {
        return $this->allowance_post;
    }



    /**
     * Drop the cents without rounding.
     * 
     * @access private
     * @param float $float
     * @return int
     */
    private function dropCents($float)
    {
        return 0.01 * (int)($float * 100);
    }
}
