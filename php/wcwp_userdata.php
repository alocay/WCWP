<?php
	 if (isset($_GET['steamids'])) {
	     $steamids = $_GET['steamids'];
		 $api_url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=8B8C86D67AB89F6F4F7CD347003F7CF5&steamids=" . $steamids;
		 
		 $json = file_get_contents($api_url);
		 
		 echo $json;
	 }
?>