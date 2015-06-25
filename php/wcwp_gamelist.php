<?php
	 if (isset($_GET['steamid'])) {
	     $steamid = $_GET['steamid'];
		 $api_url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=8B8C86D67AB89F6F4F7CD347003F7CF5&steamid=" . $steamid . "&include_appinfo=1&format=json";
		 
		 echo file_get_contents($api_url);
	 }
?>