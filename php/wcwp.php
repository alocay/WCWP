<?php
	require 'steamauth/steamauth.php';

	if(!isset($_SESSION['steamid'])) {
	    steamlogin(); //login button
	    echo "Nope.";
	}  else {
    	include ('steamauth/userInfo.php'); //To access the $steamprofile array
    	
    	//Protected content

    	logoutbutton(); //Logout Button
    	
    	echo "Yep.";
	}	
?>