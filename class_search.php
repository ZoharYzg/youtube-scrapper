<?php

require_once 'Google/autoload.php';

@set_time_limit(20);

class Scrapper {
    
    private $DEVELOPER_KEY = 'AIzaSyB5d5OMlsRZUuV44X2ZG26gc7oWSrGiJxI';
    private $client;

    // Define an object that will be used to make all API requests.
    public $youtube;
    private $searchResponse;
    public $con;
    
    public function __construct() {
        $this->client = new Google_Client();
        $this->client->setDeveloperKey($this->DEVELOPER_KEY);
        $this->youtube = new Google_Service_YouTube($this->client);
        $this->con = @mysql_connect("localhost", "root", "");
        @mysql_select_db("waitforit", $this->con);
        if ($_GET['q'] && $_GET['maxResults']) {
            $this->searchResponse = $this->generate_videos_keywords();
        }
    }
	
	public function is_viral($video_obj) {
		$flag;
		/*if($video_obj->count_views > 500000 && $video_obj->like_count > 1000 && $video_obj->daily_views > 25000) {
			$flag = true;
		} else {
			$flag = false;
		} */
		return $flag;
	}
    
    public function generate_videos_by_query($query, $max_results) {
        try {
            // Call the search.list method to retrieve results matching the specified
            // query term.
            $searchResponse = $this->youtube->search->listSearch('id,snippet', array(
              'order' => 'viewCount',
              'q' => $query,
              'type' => 'video',
              'maxResults' => $max_results,
            ));
            return $searchResponse;
        } catch (Google_Service_Exception $e) {
            return null;
        } catch (Google_Exception $e) {
            return null;
        }
    }
	
	public function retervive_statistics($video_id) {
		$video_statistics = "https://www.googleapis.com/youtube/v3/videos?part=statistics&id={$video_id}&key={$this->DEVELOPER_KEY}";
		$json_statistics = file_get_contents($video_statistics );
		$statistics_items = json_decode($json_statistics , true);
		return $statistics_items['items'][0]['statistics'];
	}
	
	public function retervive_duration($video_id) {
		$json_time = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id={$video_id}&key={$this->DEVELOPER_KEY}");
		$time_items = json_decode($json_time, true);
		preg_match_all('/(\d+)/',$time_items['items'][0]['contentDetails']['duration'],$parts);
		$hours = intval(floor($parts[0][0]/60) * 60 * 60);
		$minutes = intval($parts[0][0]%60 * 60) / 60;
		$seconds = intval($parts[0]);
		return $hours . ":" . $minutes . ":" . $seconds;
	}
	
	public function retervive_tags($video_id) {
		$tags = array();
		$connect = file_get_contents("http://www.youtube.com/watch?v={$video_id}");
		preg_match_all('|<meta property="og\:video\:tag" content="(.+?)">|si', $connect, $tags, PREG_SET_ORDER);
		$counter = 0;
		foreach ($tags as $tag) {
			if($counter <= 8) {
				array_push($tags, $tag[1]);
				$counter++;
			}
			else {
				break;
			}
		}
		return $tags;
	}
    
    private function generate_videos_keywords() {
        $query = "SELECT `name` FROM `keywords` WHERE `status` = '0'";
        $row = mysql_query($query, $this->con);
        
        if (!$row){
           die(mysql_error());
        }
        
        while($result = mysql_fetch_assoc($row)) {
            // avaliable keyword here!
            // do the magic boy
            $videos = $this->generate_videos_by_query($result['name'], 20);
			
			foreach($videos as $video) {
				$video_id = $video['modelData']['id']['videoId'];
				$title = $video['modelData']['snippet']['title'];
				$statistics = $this->retervive_statistics($video_id);
				$duration = $this->retervive_duration($video_id);
				$tags = $this->retervive_tags($video_id);
				$duration = explode(":", $duration);
				$minutes = $duration[1];
				
				if($minutes <= 3) {
					$view_count = $statistics['viewCount'];
					$like_count = $statistics['likeCount'];
					$dislike_count = $statistics['dislikeCount'];
					$buffer_tags = array();
					foreach($tags as $tag) {
						array_push($buffer_tags, $tag[1]);
					}
					$buffer_tags = implode(",", $buffer_tags);
					
					var_dump("video id: " . $video_id . " title: " . $title . " view count: " . $view_count . " like count: " . $like_count . " dislike count: " . $dislike_count . " minutes: " . $minutes . " tags: " . $buffer_tags . "");
					
					mysql_query("INSERT INTO `videos` (`video_id`,`title`,`view_count`,`like_count`,`dislike_count`,`duration`,`tags`,`related_video`) VALUES('{$video_id}', '{$title}', '{$view_count}', '{$like_count}', '{$dislike_count}', '{$minutes}', '{$buffer_tags}', '')");
					
					mysql_query("UPDATE `keywords` SET `status` = '1' WHERE `name` = '" . $result['name'] . "'");
				}			
			}
        }
    }
}

$obj = new Scrapper;

?>
