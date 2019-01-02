<?php

class Migrate extends CI_Controller
{

    public function index()
    {
        $this->load->library('migration');

        if ( ! $this->migration->current())
        {
            echo 'Error ' . $this->migration->error_string();
            // echo 'ini error';
        } else {
            echo 'Migrations ran successfully!';
        }
    }

}