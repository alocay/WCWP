<?php
    require_once 'vendor/autoload.php';
	
	Twig_Autoloader::register();
	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader, array(
    	/*'cache' => './tmp/cache',*/
	));
	
	$template = $twig->loadTemplate('index.html');
	
	require_once 'steamauth/steamauth.php';	
	
	$steam_logged_in = FALSE;
	$steam_profile = NULL;
	
	if(!isset($_SESSION['steamid'])) {
		steamlogin(); //login button
	}  else {
    	include ('steamauth/userInfo.php'); //To access the $steamprofile array
    	$steam_logged_in = TRUE;
		
		if(isset($steamprofile))
		{
			$steam_profile = $steamprofile;
		}
	}
	
	$template->display(array('steam_logged_in' => $steam_logged_in, 'user' => $steam_profile));
?>