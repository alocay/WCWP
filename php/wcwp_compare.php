<?php

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
    
    if (isset($_POST['usergames']) && isset($_POST['friendgames'])) {
        $user_games = json_decode($_POST['usergames']);
        $friend_games = json_decode($_POST['friendgames']);
		
		// compare with friend's list
        $matched_games = compare_games($user_games, $friend_games);
        
		// return results
        echo json_encode($matched_games);
    }
?>