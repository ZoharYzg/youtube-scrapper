<?php
/**
 * Mark object in the play-list (sababa / not sababa)
 *
 * @category  playlist object mark
 * @author    Yzgeav Zohar <zoharyzgeav@gmail.com>
 * @copyright Copyright (c) 2015
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   1.0
 * @product	  wait for it (app)
 **/

require_once("mysql_connection.php");

class mark_object {
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
	 * Gets client request and updates like status in object id for user id
	 * 
	 * @param string $url
	 */
	public function get_request($url) {
		$filterd_url = explode("?", $url)[1];
		$object_id = explode("=", explode("&", $filterd_url)[0])[1];
		$user_id = explode("=", explode("&", $filterd_url)[1])[1];
		$like_status = explode("=", explode("&", $filterd_url)[2])[1];
		$watching_time = isset(explode("&", $filterd_url)[3]) ? (explode("=", explode("&", $filterd_url)[3])[1]) : (0);
		
		$data = Array (
               "user_id" => $user_id,
               "object_id" => $object_id,
			   "watching_time" => $watching_time,
			   "like_status" => $like_status,
			   "date" => date("m.d.y")
		);
		
		$id = $this->db->insert ('logs', $data);
	}
}

$request = new mark_object("localhost", "root", "", "waitforit");
$url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$request->get_request($url);
?>
