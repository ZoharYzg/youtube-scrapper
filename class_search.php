<?php

require_once 'Google/autoload.php';

class Scrapper {
	
	private $DEVELOPER_KEY = 'AIzaSyB5d5OMlsRZUuV44X2ZG26gc7oWSrGiJxI';
	private $client;

	// Define an object that will be used to make all API requests.
	public $youtube;
	private $searchResponse;
	
	public function __construct() {
		$this->client = new Google_Client();
		$this->client->setDeveloperKey($this->DEVELOPER_KEY);
		$this->youtube = new Google_Service_YouTube($this->client);
		$htmlBody = <<<END
		<form method="GET">
		  <div>
			Search Term: <input type="search" id="q" name="q" placeholder="Enter Search Term">
		  </div>
		  <div>
			Max Results: <input type="number" id="maxResults" name="maxResults" min="1" max="50" step="1" value="25">
		  </div>
		  <input type="submit" value="Search">
		</form>
END;
		if ($_GET['q'] && $_GET['maxResults']) {
			$this->searchResponse = $this->generate_videos("MX145Tu4MHY");
			$videos = '';
			$channels = '';
			$playlists = '';

			// Add each result to the appropriate list, and then display the lists of
			// matching videos, channels, and playlists.
			foreach ($this->searchResponse['items'] as $searchResult) {
				switch ($searchResult['id']['kind']) {
					case 'youtube#video':
					  $videos .= sprintf('<li>%s (%s)</li>',
						  $searchResult['snippet']['title'], $searchResult['id']['videoId']);
					  $video_url = "https://www.googleapis.com/youtube/v3/videos?part=statistics&id={$searchResult['id']['videoId']}&key={$this->DEVELOPER_KEY}";
					  $json = file_get_contents($video_url);
					  $items = json_decode($json, true);
					  var_dump($items['items'][0]['statistics']);
					  $json_time = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id={$searchResult['id']['videoId']}&key={$this->DEVELOPER_KEY}");
					  $time_items = json_decode($json_time, true);
					  var_dump($time_items['items'][0]['contentDetails']);
					  preg_match_all('/(\d+)/',$time_items['items'][0]['contentDetails']['duration'],$parts);
					  $hours = intval(floor($parts[0][0]/60) * 60 * 60);
					  $minutes = intval($parts[0][0]%60 * 60);
					  $seconds = intval($parts[0][1]);
					  print "Hours: {$hours} minutes: {$minutes} seconds: {$seconds}";
					  $connect = file_get_contents("http://www.youtube.com/watch?v={$searchResult['id']['videoId']}");
					  preg_match_all('|<meta property="og\:video\:tag" content="(.+?)">|si', $connect, $tags, PREG_SET_ORDER);
					  foreach ($tags as $tag) {
						 echo $tag[1] . "<br>";
					  }
					  break;
					case 'youtube#channel':
					  $channels .= sprintf('<li>%s (%s)</li>',
						  $searchResult['snippet']['title'], $searchResult['id']['channelId']);
					  break;
					case 'youtube#playlist':
					  $playlists .= sprintf('<li>%s (%s)</li>',
						  $searchResult['snippet']['title'], $searchResult['id']['playlistId']);
					  break;
				}
			}
			
			$htmlBody .= <<<END
			<h3>Videos</h3>
			<ul>$videos</ul>
			<h3>Channels</h3>
			<ul>$channels</ul>
			<h3>Playlists</h3>
			<ul>$playlists</ul>
END;
			print $htmlBody;
		}
	}
	
	public function generate_videos($video_id) {
		try {
			// Call the search.list method to retrieve results matching the specified
			// query term.
			$searchResponse = $this->youtube->search->listSearch('id,snippet', array(
			  'q' => 'Beer',
			  'order' => 'viewCount',
			  'type' => 'video',
			  'maxResults' => $_GET['maxResults'],
			));
			return $searchResponse;
		} catch (Google_Service_Exception $e) {
			return null;
		} catch (Google_Exception $e) {
			return null;
		}
	}
}

$obj = new Scrapper;

?>
