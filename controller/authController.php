<?php

switch ( $route->getAction() ) {

    case 'login':
        if ( $_POST  ) {
            $auth = new auth();
            $auth->processAuth( $_POST );
            $errors = $auth->getAuthMessage();
        }
        include( APP_VIEW . '/header.php' );
        include( APP_VIEW . '/auth/login.php' );
        include( APP_VIEW . '/footer.php' );
        break;


    case 'logout':
        $_SESSION = 0;
        session_destroy();
        session_start();
        header ( "Location: auth/login" );


    default:
        include( APP_VIEW . '/header.php' );
        include( APP_VIEW . '/auth/login.php' );
        include( APP_VIEW . '/footer.php' );
	    break;
}
