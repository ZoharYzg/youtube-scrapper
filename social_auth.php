<?php
/**
 * social authentication web service
 *
 * @category  Social authentication
 * @author    Yzgeav Zohar <zoharyzgeav@gmail.com>
 * @copyright Copyright (c) 2015
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   1.1
 * @product	  wait for it (app)
 **/
 
require_once("mysql_connection.php");

class social_auth {
	private $db;
	private $error;
	
	/**
     * Initialize MySQLI connection object with defined settings
     *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $dbname
     */
	public function __construct($host, $user, $pass, $dbname) {
		$this->db = new MysqliDb ($host, $user, $pass, $dbname);
	}
	
	/**
     * Gets client's ip address using HTTP headers
     *
     */
	public function get_ip_address() {
		// check for shared internet/ISP IP
		if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		// check for IPs passing through proxies
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// check if multiple ips exist in var
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
				$iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				foreach ($iplist as $ip) {
					if (validate_ip($ip))
						return $ip;
				}
			} else {
				if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
					return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
			return $_SERVER['HTTP_X_FORWARDED'];
		if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
			return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
			return $_SERVER['HTTP_FORWARDED_FOR'];
		if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
			return $_SERVER['HTTP_FORWARDED'];

		// return unreliable ip since all else failed
		return $_SERVER['REMOTE_ADDR'];
	}
	
	/**
     * Splits Google request params and saving in the database
     *
	 * @param string $url
    */
	public function get_google_credintals($url) {
		$params = explode("&", $url);
		$email = explode("=", $params[1])[1];
		$birthdate = date("Y") - explode("=", explode("-", $params[2][0]))[1];
		$pic = explode("=", $params[3])[1];
		$gender = explode("=", $params[4])[1];
		$lang = explode("=", $params[5])[1];
		
		$result = $db->rawQuery('SELECT * from user_data where email = $email');
		if(!count($result)) {
			// insert all of the params to the specified table in the DB
			$data = array(
			"id" => "",
			"token" => "",
			"position" => "",
			"waiting_time" => "",
			"user_group" => 1,
			"datetime" => date_default_timezone_get()
			);
			
			$id = $db->insert ('users', $data);
			
			$user_data = array(
			"user_id" => $id,
			"birth_date" => $birthdate,
			"pic" => $pic,
			"gender" => $gender,
			"country" => "",
			"email" => $email,
			"ip" => $this->get_ip_address()
			);
			
			$id = ($id) ? ($db->insert ('user_data', $user_data)) : ($id);
			return $id;
		}
	}
	
	/**
     * Splits Facebook request params and saving in the database
     *
	 * @param string $url
    */
	public function get_facebook_credintals($url) {
		$params = explode("&", $url);
		$token = explode("=", $params[1])[1];
		$birthdate = date("Y") - $params[2];
		$pic = explode("=", $params[3])[1];
		$gender = explode("=", $params[4])[1];
		$country = explode("=", $params[5])[1];
		$email = explode("=", $params[6])[1];
		
		$result = $db->rawQuery('SELECT * from users where token = $token');
		if(!count($result)) {
			// insert all of the params to the specified table in the DB
			$user_data = array(
			"token" => $token,
			"position" => "",
			"waiting_time" => "",
			"user_group" => 1,
			"datetime" => date_default_timezone_get()
			);
			
			$id = $this->db->insert ('users', $user_data);
			
			$data = array(
			"user_id" => $id,
			"birth_date" => $birthdate,
			"pic" => $pic,
			"gender" => $gender,
			"country" => $country,
			"email" => $email,
			"ip" => $this->get_ip_address()
			);
			
			$id = ($id) ? ($this->db->insert ('user_data', $data)) : ($id);
			return $id;
		}
	}
	
	/**
     * Splits given request to Facebook / Google handler 
     *
	 * @param string $url
    */
	public function get_social_data($url) {
		$status = 0;
		$filterd_url = explode("?", $url)[1];
		$type = explode("=", explode("&", $filterd_url)[0])[1];
		if($type == "facebook") {
			$status = $this->get_google_credintals($filterd_url);
		} else if($type == "google") {
			$status = $this->get_facebook_credintals($filterd_url);
		} else {
			$this->error = "Error has been occurred in defining source point.";
		}
		print json_encode($status);
	}
}

$auth_obj = new social_auth("localhost", "root", "", "waitforit");
$url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$auth_obj->get_social_data($url);
?>
