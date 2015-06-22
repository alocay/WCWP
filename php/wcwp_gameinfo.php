<?php
	 if (isset($_GET['appid'])) {
	     $appid = $_GET['appid'];
		 $api_url = "http://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key=8B8C86D67AB89F6F4F7CD347003F7CF5&appid=" . $appid . "&format=json";
		 
		 $json = file_get_contents($api_url);
		 
		 echo $json;
	 }
?>