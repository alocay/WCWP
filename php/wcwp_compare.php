<?php

    function get_gamelist_data($id) {
	    $api_url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=8B8C86D67AB89F6F4F7CD347003F7CF5&steamid=" . $id . "&include_appinfo=1&include_played_free_games=1&format=json";
	    return json_decode(file_get_contents($api_url));
	}

	function get_store_html_content($gamelist) {
	    $games = $gamelist->{'response'}->{'games'};
	    $mh = curl_multi_init();
		$index = 0;
		
	    foreach($games as $game) {
	        $store_url = "http://store.steampowered.com/app/" . $game->{'appid'};
	        $gamedata[$index]['data'] = $game;
	        $gamedata[$index]['url'] = $store_url;
	        $gamedata[$index]['curl'] = curl_init();
	        
	        curl_setopt($gamedata[$index]['curl'], CURLOPT_URL, $store_url);
	        curl_setopt($gamedata[$index]['curl'], CURLOPT_RETURNTRANSFER, true);
	        
	        curl_multi_add_handle($mh, $gamedata[$index]['curl']);
			
			$index++;
	    }
	    
	    $active = NULL;
	    do {
	        $ret = curl_multi_exec($mh, $active);
	    } while ($ret == CURLM_CALL_MULTI_PERFORM);
	    
		do 
		{
			curl_multi_exec($mh, $active);
			curl_multi_select($mh);
		} while ($active > 0);
		
		for($i = 0; $i < count($gamedata); $i++) {
			$result = curl_multi_getcontent($gamedata[$i]['curl']);
			$gamedata[$i]['html'] = $result;
			curl_multi_remove_handle($mh, $gamedata[$i]['curl']);
		}        
	    
	    curl_multi_close($mh);
	    
	    return $gamedata;
	}

	function filter_multiplayer_games($gamedata_html) {
	    $index = 0;
	    $filtered_games = array();
	    
	    for($i = 0; $i < count($gamedata_html); $i++) {
	    	$data = $gamedata_html[$i];
			$tagModalPos = strpos($data['html'],'InitAppTagModal');
			
	        if ($tagModalPos !== false && (strpos($data['html'], 'Multiplayer', $tagModalPos) !== false || strpos($data['html'], 'Co-op', $tagModalPos) !== false )) {
	            $filtered_games[$index] = $data['data'];
	            $index++;
	        }
	    }
	    
	    return $filtered_games;
	}

	function compare_games($user_filtered_gamelist, $friend_gamelist) {
	    $matched_games = array();
	    $index = 0;
	    foreach($user_filtered_gamelist as $user_game) {
	        foreach($friend_gamelist as $friend_game) {
	            if ($user_game->{'appid'} === $friend_game->{'appid'}) {
	                $matched_games[$index] = $user_game;
	                $index++;
	            }
	        }
	    }
	
	    return $matched_games;
	}
    
    if (isset($_GET['userid']) && isset($_GET['friendid'])) {
        $userid = $_GET['userid'];
        $friendid = $_GET['friendid'];
		
        $user_gamelist_data = get_gamelist_data($userid);
		$friend_gamelist_data = get_gamelist_data($friendid);
        
		// get html game data
		$html_data = get_store_html_content($user_gamelist_data);
		
		// filter out non-multiplayer and non-coop games
        $filtered_users_games = filter_multiplayer_games($html_data);
		
		// compare with friend's lsit
        $matched_games = compare_games($filtered_users_games, $friend_gamelist_data->{'response'}->{'games'});
        
		// return results
        echo json_encode($matched_games);
    }
?>