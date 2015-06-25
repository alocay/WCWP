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
            compareGames(steamid);
            //getAndShowGameList(steamid);
        });
        
        $.get("php/wcwp_gamemulti.php", { appid: 212680 })
            .done(function (html) {
                var data = html;
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
    
    function compareGames(steamid) {
        $.get("php/wcwp_compare.php", { userid: userSteamId, friendid: steamid })
            .done(function (matchedGames) {
                showGameList(matchedGames);
            });
        
    }
    
    /*var compareFunction = function (games) {
            var matchedGames = [];
            for (var i = 0; i < games.length; i++) {
                for (var j = 0; j < userGameList.length; j++) {
                    if (games[i].name === userGameList[j].name) {
                        matchedGames.push(games[i]);
                        break;
                    }
                }
            }
            
            showGameList(matchedGames);
        };
        
        getGameList(steamid, compareFunction);*/
    
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
            gameListElement.append('<li class="game"><span><img src="' + games[i].img_icon_url + '" /></span><span>' + games[i].name + '</span></li>');
        }
    }
});