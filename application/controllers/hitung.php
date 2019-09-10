<?php if (! defined('BASEBATH')) exit('No direct script access allowed');

class Hitung extends CI_Controller{

    function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
    }

    function index()
    {
        echo "ini index";
        //$this->load->view('hitung/index');
    }

    function perkalian()
    {
        $data['v1'] = (int)$this->input->post('v1', true);
        $data['v2'] = (int)$this->input->post('v2', true);
        $data['hasil']=$data('v1')*$data['v2'];
        $this->load-view('perkalian', $data);
    }

    function pembagian()
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