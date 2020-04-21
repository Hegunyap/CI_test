<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Hitung extends CI_Controller{

    function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
    }

    public function index()
    {
        // echo "ini index";
        $this->load->view('hitung/menu_hitung');
    }

    public function perkalian()
    {
        if ($this->input->post()){
            $data['v1'] = (int)$this->input->post('v1', true);
            $data['v2'] = (int)$this->input->post('v2', true);
            $data['hasil']=$data('v1')*$data['v2'];

            $this->load-view('perkalian', $data);
        }else{
            $this->load-view('hitung/perkalian');
        }
    }

    public function pembagian()
    {
        $data['v1']=(int)$this->input->post('v1'. true);
        $data['v2']=(int)$this->input-post('v2', true);
        if($data['v2']>0)
        $data['hasil']=$data['v1']/$data['v2'];
        else
        $data['hasil']='Error, v2 tidak boleh 0!';
        $this->load->view('pembagian', $data);
    }
}