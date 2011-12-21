<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {


	public function index()
	{
	
	    error_reporting(0);
        $this->load->helper('url');
		$this->load->helper('jlang');
		
		
		$this->load->view('welcome_message');
	}
}

