<?php

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
    
    if (isset($_POST['usergames'])) {
        $user_games = json_decode($_POST['usergames']);
        
		// get html game data
		$html_data = get_store_html_content($user_games);
		
		// filter out non-multiplayer and non-coop games
        $filtered_user_games = filter_multiplayer_games($html_data);
        
		// return results
        echo json_encode($filtered_user_games);
    }
?>