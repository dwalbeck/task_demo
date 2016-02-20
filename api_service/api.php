<?php
	/**
	 * Created by PhpStorm.
	 * User: davey
	 * Date: 2/17/16
	 * Time: 10:01 AM
	 */
	require_once("rest.inc.php");

	class API extends REST {

		public $data = "";
		public $log = '';

		const DB_SERVER = "127.0.0.1";
		const DB_USER = "webuser";
		const DB_PASSWORD = "w36U53r!";
		const DB = "avantlink_task";

		private $db = NULL;
		private $pgconn = NULL;

		public function __construct(){
			parent::__construct();				// Initialize parent
			$this->dbConnect();					// get database handle
			//if (!$this->log = fopen('error_log.txt', 'a')) { die('Failed to open log file'.getcwd()); }
		}

		public function __destruct() {
			//fclose($this->log);
		}

		public static function writeLog($message) {
			if (!$log = fopen('/www/avantlink/api_service/error_log.txt', 'a')) { die('Failed to open log file'.getcwd()); }
			fwrite($log, "$message\n");
			fclose($log);
			return true;
		}

		// Connect to Database
		private function dbConnect(){
			if (!$this->pgconn = pg_connect("host=".self::DB_SERVER." dbname=".self::DB." user=".self::DB_USER." password=".self::DB_PASSWORD)) {
				die('Failed to establish a database connection');
			}
		}

		// Dynmically call the method based on the query string
		public function processApi(){
			$func = strtolower(trim(str_replace("/", "", $_REQUEST['x'])));
			if ((int)method_exists($this, $func) > 0) {
				$this->$func();
			} else {
				$this->response('', 404); // If the method not exist with in this class "Page not found".
			}
		}

		private function login() {
			if ($this->get_request_method() != "POST") {
				$this->response('',406);
			}

			if (!empty($this->_request['user_email']) && !empty($this->_request['user_passwd'])) {
				if (filter_var($this->_request['user_email'], FILTER_VALIDATE_EMAIL)) {
					$query = "SELECT * FROM users WHERE user_email = '$this->_request['user_email']' AND user_passwd = '".md5($this->_request['user_passwd'])."' LIMIT 1";
					if (!$res = pg_query($this->pgconn, $query)) { die("Failed to query database for user account."); }

					if (pg_num_rows($res) > 0) {
						$row = pg_fetch_assoc($res);
						$this->response($this->json($row), 200); // send success code
					}
					$this->response('', 204);
				}
			}

			$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			$this->response($this->json($error), 400);
		}

		private function tasks() {
			if ($this->get_request_method() != "GET") { $this->response('', 406); }

			$query = "SELECT * FROM task ORDER BY date_due DESC, time_due DESC, task_created DESC";
			if (!$res = pg_query($this->pgconn, $query)) { die('Failed to execute query lookup for tasks '.$query); }
			if (pg_num_rows($res) > 0) {
				$result = array();
				while ($row = pg_fetch_assoc($res)) {
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('', 204);
		}

		private function task() {
			if ($this->get_request_method() != "GET") { $this->response('', 406); }
			$id = $_REQUEST['task_id'];

			if ($id > 0) {
				if (!$result = pg_select($this->pgconn, 'task', array('task_id' => $id))) { die('Failed to execute select query of task'); }
				$this->response($this->json($result), 200); // send user details

			} else {
				$this->response('Missing task ID in request', 204);
			}
			$this->response('', 204);
		}

		private function postTask() {
			if ($this->get_request_method() != "POST") { $this->response('', 406); }
			$task = json_decode(file_get_contents("php://input"), true);
			$field = array('category_id', 'task_name', 'task_desc', 'importance', 'alarm_set', 'alert_min', 'date_due', 'time_due', 'task_completed');
			$data = array();

			foreach ($field AS $fld) { // Grab all the field that are valid columns
			   	if (array_key_exists($fld, $task)) {
					$data[$fld] = $task[$fld];
				}
			}
			if (!empty($task)) {
				if (!$res = pg_insert($this->pgconn, 'task', $data)) { die('Failed to execute insert task'); }
				$success = array('status' => "Success", "msg" => "Successfully created task.", "data" => $data);
				$this->response($this->json($success), 200);

			} else {
				$this->response('', 204);
			}
		}

		private function putTask() {
			if ($this->get_request_method() != "PUT") { $this->response('', 406); }
			$task = $this->_request;
			if (!$task['task_id']) { $this->response('', 204); return; }

			$field = array('category_id', 'task_name', 'task_desc', 'importance', 'alarm_set', 'alert_min', 'date_due', 'time_due', 'task_completed');
			$up = array('task_updated' => date('Y-m-d H:i:s'));

			foreach ($field AS $fld) { // Check the customer received. If blank insert blank into the array.
				if (array_key_exists($fld, $task)) {
					$up[$fld] = $task[$fld];
				}
			}
			//$query = "UPDATE task SET $up WHERE task_id=".$task['task_id'];
			if (count($up)) {
				if (!$res = pg_update($this->pgconn, 'task', $up, array('task_id' => $task['task_id']))) { die('Failed to update task'); }
				$success = array('status' => "Success", "msg" => "Task ".$task['task_id']." successfully updated.", "data" => $task);
				$this->response($this->json($success), 200);

			} else {
				$this->response('', 204);
			}
		}

		private function deleteTask() {
			if ($this->get_request_method() != "DELETE") { $this->response('', 406); }
			$taskid = $_REQUEST['task_id'];
			if ($taskid) {
				if (!$res = pg_delete($this->pgconn, 'task', array('task_id' => $taskid))) {  die('Failed to execute task delete'); }
				$success = array('status' => "Success", "msg" => "Successfully deleted task.");
				$this->response($this->json($success), 200);

			} else {
				$this->response('', 204);
			}
		}

		private function upTask() {
			if ($this->get_request_method() != "PUT") { $this->response('', 406); }
			$id = $_REQUEST['task_id'];
			$status = strtotime($_REQUEST['status']);

			if ($id > 0) {
				$up = array('task_completed' => ($status ? 'NULL' : date('Y-m-d H:i:s')), 'task_updated' => date('Y-m-d H:i:s'));
				if (!$res = pg_update($this->pgconn, 'task', $up, array('task_id' => $id))) {  die('Failed to update task completed status'); }
				$success = array('status' => "Success", "msg" => "Successfully updated task status.");
				$this->response($this->json($success), 200);

			} else {
				$this->response('', 204);
			}
		}

		private function json($data){
			if (is_array($data)) {
				return json_encode($data);
			}
		}
	}

	// Instantiate and start REST API

	$api = new API;
	$api->processApi();
