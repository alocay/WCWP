<?php

function get_gamelist_data($id) {
    $api_url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=8B8C86D67AB89F6F4F7CD347003F7CF5&steamid=" . $id . "&include_appinfo=1&include_played_free_games=1&format=json";
    return json_decode(file_get_contents($api_url));
}

function get_store_html_content($gamelist) {
    $games = $gamelist->{'response'}->{'games'};
    $mh = curl_multi_init();
    
    foreach($games as $game) {
        $store_url = "http://store.steampowered.com/app/" . $game->{'appid'};
        $gamedata[$game->{'appid'}]['data'] = $game;
        $gamedata[$game->{'appid'}]['url'] = $store_url;
        $gamedata[$game->{'appid'}]['curl'] = curl_init();
        
        curl_setopt($gamedata[$game->{'appid'}]['curl'], CURLOPT_URL, $store_url);
        curl_setopt($gamedata[$game->{'appid'}]['curl'], CURLOPT_RETURNTRANSFER, true);
        
        curl_multi_add_handle($mh, $gamedata[$game->{'appid'}]['curl']);
    }
    
    echo "Curl count: " . count($gamedata) . "\n";
    
    $active = NULL;
    do {
        $ret = curl_multi_exec($mh, $active);
    } while ($ret == CURLM_CALL_MULTI_PERFORM);
    
    echo "First loop done.\n";
    
    while ($active && $ret == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }
    
    echo "Got data\n";
    
    foreach($gamedata as $g) {
        $result = curl_multi_getcontent($g['curl']);
        
        //var_dump($result);
        
        $g['html'] = $result;
        curl_multi_remove_handle($mh, $g['curl']);
    }
    
    echo "Closing curl\n";
    
    curl_multi_close($mh);
    
    return $gamedata;
}

function filter_multiplayer_games($gamelist) {
    $games = $gamelist->{'response'}->{'games'};
    $index = 0;
    $filtered_games = array();
    
    include('simple_html_dom.php');
    
    foreach($games as $game) {            
        $store_url = "http://store.steampowered.com/app/" . $game->{'appid'};
        //$html = file_get_html($store_url);
        
        $html = file_get_contents($store_url);
        
        if (strpos($html,'InitAppTagModal') !== false && (strpos($html, 'Multiplayer') !== false || strpos($html, 'Co-op') !== false )) {
            $filtered_games[$index] = $game;
            $index++;
        }
        
        /*foreach($html->find('script') as $e) {
            if (strpos($e,'InitAppTagModal') !== false && (strpos($e, 'Multiplayer') !== false || strpos($e, 'Co-op') !== false )) {
                $filtered_games[$index] = $game;
                $index++;
                break;
            }
        }*/
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

$userid = "76561198030292877";
$friendid = "76561198028853323";

$user_gamelist_data = get_gamelist_data($userid);
$friend_gamelist_data = get_gamelist_data($friendid);

echo "Starting...\n\n";

$before = microtime(true);
$html_data = get_store_html_content($user_gamelist_data);
//$filtered_users_games = filter_multiplayer_games($user_gamelist_data);
$after = microtime(true);
echo "\nfilter_multiplayer_games: " . number_format(($after - $before), 4) . " Seconds\n\n";

var_dump($html_data);

/*
$before = microtime(true);
$matched_games = compare_games($filtered_users_games, $friend_gamelist_data->{'response'}->{'games'});
$after = microtime(true);
echo "compare_games: " . number_format(($after - $before), 4) . " Seconds\n\n";

var_dump($matched_games);*/

?>