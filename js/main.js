$(document).ready(function() {
    var userSteamId = $("#steamid").val();
    var userName = $("#personaname").val();
    var userFriendsList = null;
    var userGameList = [];
    var fuseSearch = null;
    
    var postInitFunction = function (games) {
        userGameList = games;
        
        showFriendsSectionLoading("Getting friends list...");
        showGamesSectionLoading("Waiting on friends...");
        
        if (userSteamId) {
            $.get("php/wcwp_friends.php", { steamid: userSteamId })
                .done(function (json) {
                    var friendsdata = JSON.parse(json);
                    getAndShowFriends(friendsdata.friendslist.friends);
                });
        }
        
        $("#friends-search-text").on("input paste", function() {
            searchAndDisplayResults(this.value);
        });
        
        $("#friends-list").on("click", ".friend", function (data) {
            var steamid = this.getAttribute("data-steamid");
            $("#game-list").empty();
            showGamesSectionLoading("Comparing games...");
            compareAndShowGames(steamid);
        });
    }
    
    showFriendsSectionLoading("Getting " + userName + "'s games...");
    showGamesSectionLoading("Getting " + userName + "'s games...");
    getGameList(userSteamId, postInitFunction);
    
    /*
     * Thanks goes to tobiasahlin for this browserSupportsCSSProperty function (https://github.com/tobiasahlin)
     */
    function browserSupportsCSSProperty(propertyName) {
        var element = document.createElement('div');
        propertyName = propertyName.toLowerCase();
    
        if (element.style[propertyName] != undefined) {
            return true;
        }
    
        var propertyNameCapital = propertyName.charAt(0).toUpperCase() + propertyName.substr(1),
            domPrefixes = 'Webkit Moz ms O'.split(' ');
    
        for (var i = 0; i < domPrefixes.length; i++) {
            if (element.style[domPrefixes[i] + propertyNameCapital] != undefined) {
                return true;
            }
        }
    
        return false;
    }
    
    function getAndShowFriends(friendsList) {
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
                
                if (userdata && userdata.response && userdata.response.players) {
                    userFriendsList = userdata.response.players;
                    setUpSearchOptions(userFriendsList);
                    
                    showFriendsList(userdata.response.players);
                    
                    hideFriendsSectionLoading();
                    hideGameSectionLoading();
                    showGamesSectionChooseFriend();
                }
            });
    }
    
    function showFriendsList(friends) {
        var friendsListElement = $("#friends-list");
        friendsListElement.empty();
        for (var i = 0; i < friends.length; i++) {
            var player = friends[i];
            friendsListElement.append('<li class="friend" data-steamid="' + player.steamid +'"><span class="friend-img"><img src="' + player.avatar + '" /></span><span class="friend-name">' + player.personaname + '</span></li>');
        }
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
            gameListElement.append('<li class="game"><span class="game-img"><img src="' + gameIconUrl + '" /></span><span class="game-name">' + games[i].name + '</span></li>');
        }
        
        hideGameSectionLoading();
    }
    
    function setUpSearchOptions(searchData) {
        if(!fuseSearch) {
            var searchOptions = {
                shouldSort: true,
                keys: ['personaname']
            };
            
            fuseSearch = new Fuse(searchData, searchOptions);
        }
    }
    
    function searchAndDisplayResults(searchValue) {
        var results = userFriendsList;
        
        if (searchValue && searchValue !== "") {
            results = fuseSearch.search(searchValue);   
        }
        
        showFriendsList(results);        
    }
    
    function showFriendsSectionLoading(message) {
        $("#friends .loading-message").text(message);
        $("#friends .loading").removeClass("hidden");
    }
    
    function hideFriendsSectionLoading() {
    	$("#friends .loading").addClass("hidden");
    }
    
    function showGamesSectionLoading(message) {
        $("#precompare-message").addClass("hidden");
        $("#games .loading-message").text(message);
        $("#games .loading").removeClass("hidden");
    }
    
    function showGamesSectionChooseFriend() {
    	$("#precompare-message").removeClass("hidden");
    }
    
    function hideGameSectionLoading() {
    	$("#games .loading").addClass("hidden");
    }
});