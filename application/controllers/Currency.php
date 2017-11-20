<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Currency extends CI_Controller {

    public function index()
    {
        $input_data = json_decode($this->input->raw_input_stream, true);
        if(isset($input_data['currency'])){
            $this->session->set_userdata('currency', $input_data['currency']);
        }
    }
}