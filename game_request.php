<?php
/**
 * games data manger web service
 *
 * @category  games data manager
 * @author    Yzgeav Zohar <zoharyzgeav@gmail.com>
 * @copyright Copyright (c) 2015
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   1.0
 * @product	  wait for it (app)
 **/

require_once("mysql_connection.php");

class game_request {
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
	 * Gets client request and returns game information 
	 * 
	 * @param string $url
	 */
	public function get_request($url) {
		$filterd_url = explode("?", $url)[1];
		$id = explode("=", explode("&", $filterd_url)[0])[1];
		$this->db->join("objects o", "g.object_id=o.id", "LEFT");
		$this->db->where("o.id", $id);
		$games = $this->db->get ("games g", null, "o.name, g.instruction, g.pic");
		if(count($games)) {
			print_r(json_encode($games));
		}
	}
}

$request = new game_request("localhost", "root", "", "waitforit");
$url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$request->get_request($url);
?>
