<?php

    function get_gamelist_data($id) {
        $api_url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=8B8C86D67AB89F6F4F7CD347003F7CF5&steamid=" . $id . "&include_appinfo=1&&include_played_free_games=1&format=json";
        return json_decode(file_get_contents($api_url));
    }
    
    function filter_multiplayer_games($gamelist) {
        $games = $gamelist->{'response'}->{'games'};
        $index = 0;
        $filtered_games = array();
        
        foreach($games as $game) {            
            $store_url = "http://store.steampowered.com/app/" . $game->{'appid'};
            $html = file_get_html($store_url);
            
            foreach($html->find('script') as $e) {
                if (strpos($e,'InitAppTagModal') !== false && (strpos($e, 'Multiplayer') !== false || strpos($e, 'Co-op') !== false )) {
                    $filtered_games[$index] = $game;
                    break;
                }
            }
        }
        
        return filtered_games;
    }
    
    /*funtion compare_games($ids, $gamelist_data) {
        
    }*/
    
    include('simple_html_dom.php');
    
    if (isset($_GET['userid']) && isset($_GET['friendid'])) {
        $userid = $_GET['userid'];
        $friendid = $_GET['friendid'];
		$store_url = "http://store.steampowered.com/app/" . $appid;
		
        $user_gamelist_data = get_gamelist_data($userid);
        $friend_gamelist_data = get_gamelist_data($friendid);
        
        $filtered_users_games = filter_multiplayer_games($user_gamelist_data);
        
        //$matched_games = compare_games($filtered_users_games, $friend_gamelist_data);
        
        echo "hello"; //count(filtered_users_games); //$multi ? "true" : "false";
    }
?>