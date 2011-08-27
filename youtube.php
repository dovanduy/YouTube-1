<?php 
/**
 * YouTube PHP library
 * 
 * @author Alexandros D
 * @copyright Alexandros D 2011
 * @license GNU/GPL v2
 * @version 0.5.0
 * 
 */

class YouTube {
	
	private $_developerKey;
	private $_lastError;
	private $_baseUrl;
	private $_format; /* null = atom ; rss = RSS 2.0 ; json = JSON */
	
	private $_debug;
	
	public function __construct( $developerKey = NULL , $baseUrl = NULL , $format = NULL) {
		$this->_developerKey = $developerKey;
		if ( $baseUrl ) {
			$this->_baseUrl = $baseUrl;
		}
		else {
			$this->_baseUrl = "http://gdata.youtube.com/feeds/";
		}
		$this->_format = $format;
		$this->_debug = FALSE;
	}
	
	/***************************************************************************
	****************************************************************************
	*** Public functions
	*****************************************************************************
	*****************************************************************************/
	/**
	 * Returns an Exception object about the last error that occured
	 * 
	 * @return Exception
	 */
	public function getLastError() {
		return $this->_lastError;
	}
	
	/**
	* Load a specific user's playlists
	*
	* @param String $username The username
	* @param int $maxResults The maximum results to return
	* @param int $startIndex If set then it will skip the first $startIndex-1 entries
	* @return String
	*/
	public function getPlaylistsByUser ( $username , $maxResults = 0 , $startIndex = 0 ) {
		$url = $this->_baseUrl . "api/users/" . $username . "/playlists";
		
		$params = Array();
		if ($maxResults !=0 && $startIndex !=0) {
			$params[] = "max-results=" . $maxResults;
			$params[] = "start-index=" . $startIndex;
		}
		if ($maxResults == 0 && $startIndex != 0) {
			$params[] = "start-index=" . $startIndex;
		}
		if ($maxResults != 0 && $startIndex == 0) {
			$params[] = "max-results=" . $maxResults;
		}
		
		return $this->_httpGet($url , $params);
	}
	
	/**
	* Load a specific user's favorite videos
	*
	* @param String $username The username
	* @return String
	*/
	public function getFavoritesByUser ( $username ) {
		$url = $this->_baseUrl . "api/users/" . $username . "/favorites";
		return $this->_httpGet($url);
	}
	
	/**
	* Load a specific playlist
	*
	* @param String $playlist The playlist id
	* @return String
	*/
	public function getPlaylist ( $playlist ) {
		$url = $this->_baseUrl . "api/playlists/" . $playlist;
		return $this->_httpGet($url);
	}
	
	/**
	* Load a specific playlist entry
	*
	* @param String $playlist The playlist id
	* @param String $entry The entry id
	* @return String
	*/
	public function getPlaylistEntry ( $playlist , $entry ) {
		$url = $this->_baseUrl . "api/playlists/" . $playlist . "/" . $entry;
		return $this->_httpGet($url);
	}
	
	/**
	* Load a specific user's subscriptions
	*
	* @param String $username The username
	* @param int $maxResults The maximum results to return
	* @param int $startIndex If set then it will skip the first $startIndex-1 entries
	* @return String
	*/
	public function getSubscriptionsByUser ( $username , $maxResults = 0 , $startIndex = 0 ) {
		$url = $this->_baseUrl . "base/users/" . $username . "/subscriptions";
	
		$params = Array();
		if ($maxResults !=0 && $startIndex !=0) {
			$params[] = "max-results=" . $maxResults;
			$params[] = "start-index=" . $startIndex;
		}
		if ($maxResults == 0 && $startIndex != 0) {
			$params[] = "start-index=" . $startIndex;
		}
		if ($maxResults != 0 && $startIndex == 0) {
			$params[] = "max-results=" . $maxResults;
		}
		
		return $this->_httpGet($url , $params);
	}
	
	/**
	* Load a specific user's uploads
	*
	* @param String $username The username
	* @param int $maxResults The maximum results to return
	* @param int $startIndex If set then it will skip the first $startIndex-1 entries
	* @param String $orderby If null, will sort by relevance. Other options are 'published' and 'viewCount'
	* @return String
	*/
	public function getUploadsByUser ( $username , $maxResults = 0 , $startIndex = 0 , $orderby = NULL) {
		$url = $this->_baseUrl . "base/users/" . $username . "/uploads";
	
		$params = Array();
		if ($maxResults !=0 && $startIndex !=0) {
			$params[] = "max-results=" . $maxResults;
			$params[] = "start-index=" . $startIndex;
		}
		if ($maxResults == 0 && $startIndex != 0) {
			$params[] = "start-index=" . $startIndex;
		}
		if ($maxResults != 0 && $startIndex == 0) {
			$params[] = "max-results=" . $maxResults;
		}
		if ($orderby != 0 ) {
			$params[] = "orderby=" . $orderby;
		}
	
		return $this->_httpGet($url , $params);
	}
	
	/**
	* Load a video's related videos
	*
	* @param String $videoId The video id
	* @param int $maxResults The maximum results to return
	* @param int $startIndex If set then it will skip the first $startIndex-1 entries
	* @return String
	*/
	public function getRelatedVideos ( $videoId , $maxResults = 0 , $startIndex = 0 ) {
		$url = $this->_baseUrl . "base/videos/" . $videoId . "/related";
	
		$params = Array();
		if ($maxResults !=0 && $startIndex !=0) {
			$params[] = "max-results=" . $maxResults;
			$params[] = "start-index=" . $startIndex;
		}
		if ($maxResults == 0 && $startIndex != 0) {
			$params[] = "start-index=" . $startIndex;
		}
		if ($maxResults != 0 && $startIndex == 0) {
			$params[] = "max-results=" . $maxResults;
		}
	
		return $this->_httpGet($url , $params);
	}
	
	/***************************************************************************
	****************************************************************************
	*** Private functions
	*****************************************************************************
	*****************************************************************************/
	/**
	* Perform an HTTP get request
	*
	* @param String $url The url to GET
	* @param Array $params An array of parameters
	* @return String
	*/
	private function _httpGet( $url , $params = NULL) {
		if ($this->_format != NULL) {
			$params[] = "alt=" . $this->_format;
		}
		
		if ( count($params) ) {
			$queryString = implode("&", $params);
			$url .= "?" . $queryString;
		}
		
		if ($this->_debug) {
			echo "URL: $url";
		}
		
		//Initialize curl
		$ch = curl_init();
		
		//Set curl url
		curl_setopt($ch, CURLOPT_URL, $url);
		
		//HTTP headers
		$headers = array(
			'GData-Version: 2',
			'Cache-Control: no-cache'
		);
		
		//Set dev key if has been provided
		if ( $this->_developerKey ) {
			$headers[] = 'X-GData-Key: key=' . $this->_developerKey;
		}
		
		//Set HTTP headers
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
		
		//Start buffering output
		ob_start();
		
		//fetch data
		try {
			curl_exec($ch);
			curl_close($ch);
			$data = ob_get_contents();
			ob_end_clean();
		} 
		catch(Exception $err) {
			$data = null;
			$this->_lastError = $err;
		}
		
		//return data
		return $data;
	} 	
}