<?php

// Load bootstrap to apply configuration
include('config/bootstrap.php');

// Process URL into Application routing parameters
$route = new route();

// Start session to track user
session_start();

// User security check
if ( 0 !== APP_AUTH_TYPE && !isset($_SESSION['userName']) && 'auth' != $route->getController() ) {
     $_SESSION = 0;
     session_destroy();
     session_start();
     header ( 'Location: auth/login' );
}

// Route request to desired controller
switch ( $route->getController() ) {

    case 'auth':
        include( APP_CONTROLLER . '/authController.php');
        break;

    case 'home':
        include( APP_CONTROLLER . '/homeController.php');
        break;

    default:
        include( APP_CONTROLLER . '/homeController.php');
        break;
}
