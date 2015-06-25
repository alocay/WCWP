$(document).ready(function() {
    var userSteamId = $("#steamid").val();
    var userGameList = [];
    
    var postInitFunction = function (games) {
        userGameList = games;
        if (userSteamId) {
            $.get("php/wcwp_friends.php", { steamid: userSteamId })
                .done(function (json) {
                    var friendsdata = JSON.parse(json);
                    getAndShowFriends(friendsdata.friendslist.friends);
                });
        }
        
        $("#friends-list").on("click", ".friend", function (data) {
            var steamid = this.getAttribute("data-steamid");
            $("#game-list").empty();
            compareAndShowGames(steamid);
        });
    }
    
    getGameList(userSteamId, postInitFunction);
    
    function getAndShowFriends(friendsList) {
        var friendsListElement = $("#friends-list");
        var steamids = "";
        for (var i = 0; i < friendsList.length; i++) {
            steamids = steamids.concat(friendsList[i].steamid);
            
            if (i != friendsList.length - 1) {
                steamids = steamids.concat("+");
            }
        }
        
        $.get("php/wcwp_userdata.php", { steamids: steamids })
            .done(function (json) {
                var userdata = JSON.parse(json);
                
                for (var i = 0; i < userdata.response.players.length; i++) {
                    var player = userdata.response.players[i];
                    friendsListElement.append('<li class="friend" data-steamid="' + player.steamid +'"><span><img src="' + player.avatar + '" /></span><span>' + player.personaname + '</span></li>');
                }
            });
    }
    
    function compareAndShowGames(steamid) {
        $.get("php/wcwp_compare.php", { userid: userSteamId, friendid: steamid })
            .done(function (matchedGames) {
                showGameList(JSON.parse(matchedGames));
            });
    }
    
    function getGameList(steamid, doneCallback) {
        $.get("php/wcwp_gamelist.php", { steamid: steamid })
            .done(function (gamelistjson) {
                var gamelist = JSON.parse(gamelistjson);
                var games = [];
                
                for (var i = 0; i < gamelist.response.games.length; i++) {
                    var game = gamelist.response.games[i];
                    if (game.name.indexOf("ValveTestApp") == -1) {
                        games.push(game);
                    }
                }
                
                doneCallback(games);
            });
    }
    
    function showGameList(games) {
        var gameListElement = $("#game-list");
        for (var i = 0; i < games.length; i++) {
        	var gameIconUrl = "http://media.steampowered.com/steamcommunity/public/images/apps/" + games[i].appid + "/" + games[i].img_icon_url + ".jpg";
            gameListElement.append('<li class="game"><span><img src="' + gameIconUrl + '" /></span><span>' + games[i].name + '</span></li>');
        }
    }
});