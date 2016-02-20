<?php
	/**
	 * Created by PhpStorm.
	 * User: davey
	 * Date: 2/17/16
	 * Time: 10:01 AM
	 */
	class REST {

		public $_allow = array();
		public $_content_type = "application/json";
		public $_request = array();

		private $_method = "";
		private $_code = 200;

		public function __construct() {
			$this->inputs();
		}

		public function get_referer() {
			return $_SERVER['HTTP_REFERER'];
		}

		public function response($data, $status) {
			$this->_code = ($status ? $status : 200);
			$this->set_header();
			echo $data;
			exit;
		}

		// default to 550 - internal server error
		private function get_status_message() {
			$status = array(
				200 => 'OK',
				201 => 'Created',
				204 => 'No Content',
				404 => 'Not Found',
				406 => 'Not Acceptable'
			);
			return ($status[$this->_code] ? $status[$this->_code] : $status[500]);
		}

		public function get_request_method(){
			return $_SERVER['REQUEST_METHOD'];
		}

		private function inputs(){
			switch ($this->get_request_method()) {
				case "POST":
					$this->_request = $this->cleanInput($_POST);
					break;
				case "GET":
					break;
				case "DELETE":
					$this->_request = $this->cleanInput($_GET);
					break;
				case "PUT":
					$this->_request = json_decode(file_get_contents("php://input"), true);
					break;
				default:
					$this->response('', 406);
					break;
			}
		}

		private function cleanInput($data){
			$clean_input = array();
			if (is_array($data)) {
				foreach($data AS $key => $val) {
					$clean_input[$key] = $this->cleanInput($val);
				}
			} else {
				if (get_magic_quotes_gpc()) { $data = stripslashes($data); }
				$data = strip_tags($data);
				$clean_input = trim($data);
			}
			return $clean_input;
		}

		private function set_header(){
			header("HTTP/1.1 ".$this->_code." ".$this->get_status_message());
			header("Content-Type:".$this->_content_type);
		}
	}
