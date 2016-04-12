<?php

class auth {

    private $authMessage;
    private $authType;      // 1 = Internal, 2 = Database, 3 = LDAP/AD
    private $user;
    private $username;
    private $plainPassword;


    public function __construct () {

        defined('APP_AUTH_TYPE') or
            die ('Configuration Setting: APP_AUTH_TYPE is not set.');

        $this->authType = APP_AUTH_TYPE;
        if ( 1 > $this->authType || 3 < $this->authType ) {
            throw new \Exception ('Invalid auth type: ' . $this->authType );
        }
    }


    public function getAuthMessage() {
        return $this->authMessage;
    }


    public function processAuth ( $array ) {

        // User and Password from login form
        $this->username      = $array['userName'];
        $this->plainPassword = $array['password'];

        /*
         * Make sure password isn't null. Null passwords can trick some
         * authentication systems.
         */
        if ( '' == $this->plainPassword || null == $this->plainPassword ) {
            $this->plainPassword = crypt( microtime(), APP_SECRET );
        }


        // Verify this is a valid user
        if ( true === $this->userVerify() ) {

            // Process specified authentication method
            $authSuccsess = false;
            if ( 1 == $this->authType ) {
                $authSuccsess = $this->internalAuth();
            }
            elseif ( 2 == $this->authType ) {
                $authSuccsess = $this->databaseAuth();
            }
            elseif ( 3 == $this->authType ) {
                $authSuccsess = $this->ldapAuth();
            }
        }
        else {
            if(empty($this->authMessage)) {
                $this->authMessage[] =
                    'You are not authorized to use this application';
            }

            return false;
        }

        // If auth is successful
        if (true === $authSuccsess ) {

            $_SESSION['username']  = $this->user->getUsername();
            $_SESSION['firstName'] = $this->user->getFirstName();
            $_SESSION['lastName']  = $this->user->getLastName();
            $_SESSION['email']     = $this->user->getEmail();
            $_SESSION['role']      = $this->user->getRole();

            header ( 'Location: ' . APP_DOC_ROOT );
        }
        # Auth failed, display login form with error
        else {
            if(empty($this->authMessage)) {
                $this->authMessage[] = 'Credentials are invalid';
            }
        }
    }


    /*
     * Check database based authentication
     */
    private function databaseAuth() {
        $token = md5( $this->user->getSalt() . $this->plainPassword );

        if ( $this->user->getPassword() === $token ) {
            return true;
        }

        return false;
    }


    /*
     * Check internal (in-memory) authentication
     */
    private function internalAuth() {
        if ( $this->user->getPassword() == $this->plainPassword ) {
            return true;
        }

        return false;
    }


    /*
     * Check LDAP/AD based authentication
     */
    private function ldapAuth() {

        # Establish an anonymous connection to the server
        $ldapConnection = @ldap_connect(APP_AUTH_LDAP_SERVER);

        if ($ldapConnection) {

            # Bind to the server
            $ldapBind = @ldap_bind($ldapConnection, APP_AUTH_LDAP_USER, APP_AUTH_LDAP_PASS);

            if ($ldapBind) {

                # Create a search filter
                $filter = '('. APP_AUTH_LDAP_SEARCH_ATTR . '=' . $this->username . ')';

                # Attributes to return
                $attrs = array(APP_AUTH_LDAP_RETURN_ATTR);

                # Assign the search results to a variable:
                $ldapSearch = @ldap_search($ldapConnection, APP_AUTH_LDAP_ROOT, $filter, $attrs);

                # Make a list of the search results
                $list = @ldap_get_entries($ldapConnection, $ldapSearch);


                if ($list['count'] == '0') {
                    # No user matches
                    $logMesg =  'No user on the LDAP server: ' . APP_AUTH_LDAP_SERVER . ', ' .
                                'using search base: ' . APP_AUTH_LDAP_ROOT . ', matches: ' . $this->username;

                    $mesg    = $logMesg . '<br />';
                    $debug = new debug();
                    $debug->write_debug(date('Y-m-d h:m:s'), 1, $this->username, $mesg, $logMesg);

                    return false;
                }
                elseif ($list['count'] > '1') {
                    # More than one user matches, What?!?!
                    $logMesg =  'More than one user on the LDAP server: ' . APP_AUTH_LDAP_SERVER . ', ' .
                                'using search base: ' . APP_AUTH_LDAP_ROOT . ', matches: '. $this->username;

                    $mesg    = $logMesg . '<br />';
                    $debug = new debug();
                    $debug->write_debug(date('Y-m-d h:m:s'), 1, $this->username, $mesg, $logMesg);

                    $this->authMessage[] = 'Please contact customer support';

                    return false;
                }
                else {
                    $ldapData = $list['0'][APP_AUTH_LDAP_RETURN_ATTR];
                    $auth = @ldap_bind($ldapConnection, $ldapData, $this->plainPassword);
                    if($auth == '0') {
                        # Password does not match
                        $logMesg =  'The password supplied does not match for user: ' . $this->username .
                                    ' on the LDAP server: ' . APP_AUTH_LDAP_SERVER . ', using search base: ' .
                                    APP_AUTH_LDAP_ROOT;

                        $mesg    = $logMesg . '<br />';
                        $debug = new debug();
                        $debug->write_debug(date('Y-m-d h:m:s'), 1, $this->username, $mesg, $logMesg);

                        return false;
                    }
                    elseif($auth == '1') {
                        # Password matches, Success!
                        $logMesg =  'User: ' . $this->username . ' successfully authenticated on the LDAP server: ' .
                                    APP_AUTH_LDAP_SERVER . ', using search base: ' . APP_AUTH_LDAP_ROOT;

                        $mesg    = $logMesg . '<br />';
                        $debug = new debug();
                        $debug->write_debug(date('Y-m-d h:m:s'), 3, $this->username, $mesg, $logMesg);

                        return true;
                    }
                    else {
                        # Something is broken, this shouldn't happen!
                        $logMesg =  'The user: ' . $this->username . ' matches, but something strange happened on ' .
                                    'the ldap_bind for LDAP server: ' . APP_AUTH_LDAP_SERVER . ' using search base: ' .
                                    APP_AUTH_LDAP_ROOT;

                        $mesg    = $logMesg . '<br />';
                        $debug = new debug();
                        $debug->write_debug(date('Y-m-d h:m:s'), 1, $this->username, $mesg, $logMesg);

                        $this->authMessage[] = 'Please contact customer support';

                        return false;
                    }
                }
            } // End ldap_bind
            else {
                //LDAP Bind Failed
                $logMesg = 'Unable to bind to LDAP server: ' . APP_AUTH_LDAP_SERVER . ' please check the ' .
                           'ldap host name, user, and password in bootstrap.php.';

                $mesg    = $logMesg . '<br />';
                $debug = new debug();
                $debug->write_debug(date('Y-m-d h:m:s'), 1, $this->username, $mesg, $logMesg);

                $this->authMessage[] = 'Please contact customer support';

                return false;

            }

            # Close connection to the server.
            ldap_close($ldapConnection);
        }
        else  {
            # The connection was NOT successful.
            $logMesg = 'The connection information for LDAP server: ' . APP_AUTH_LDAP_SERVER . ' is not valid';

            $mesg    = $logMesg . '<br />';
            $debug = new debug();
            $debug->write_debug(date('Y-m-d h:m:s'), 1, $this->username, $mesg, $logMesg);

            $this->authMessage[] = 'Please contact customer support';

            return false;
        }
    }


    /*
     * Verify if specified user is a valid user of this application.
     * Also ensure the user account is active and unlocked.
     */
    private function userVerify() {

        // If Internal user - check config data for user
        if ( 1 == $this->authType ) {

            defined('APP_USER_LISTING') or
                die ('Configuration Setting: APP_USER_LISTING is not set.');

            $userListing = unserialize(APP_USER_LISTING);

            foreach ( $userListing as $key => $user ) {
                if ( $this->username === $user['username'] ) {
                    $this->user = new user;
                    $this->user->setEmail($user['email']);
                    $this->user->setFirstName($user['firstName']);
                    $this->user->setLastName($user['lastName']);
                    $this->user->setPassword($user['password']);
                    $this->user->setRole($user['role']);
                    $this->user->setUsername($user['username']);

                    return true;
                }
            }
            return false;
        }

        // Check database for user listing
        else {

            $sql = 'SELECT
                      *
                    FROM
                      auth_user
                    WHERE
                      username = ? AND
                      active = true';

            $dbObj = new db();
            $dbObj->dbPrepare( $sql );
            $dbObj->dbExecute( array( $this->username ) );

            $row = $dbObj->dbFetch( 'assoc' );

            if (true == $row['locked']) {
                $this->authMessage[] = 'Your account is locked, please contact customer support';

                return false;
            }

            if ( isset($row['username']) ) {
                $this->user = new user;
                $this->user->setEmail( $row['email'] );
                $this->user->setFirstName( $row['first_name'] );
                $this->user->setLastName( $row['last_name'] );
                $this->user->setPassword( $row['password'] );
                $this->user->setSalt( $row['salt'] );
                $this->user->setUsername( $row['username'] );

                $sql = 'SELECT
                          ar.name
                        FROM
                          auth_user__auth_role auar
                        JOIN
                          auth_role ar ON auar.role_id = ar.id
                        WHERE
                          auar.user_id = ?';

                $dbObj = new db();
                $dbObj->dbPrepare( $sql );
                $dbObj->dbExecute( array( $row['id'] ) );

                $role = array();
                while ( $row = $dbObj->dbFetch( 'assoc' ) ) {
                    if ( isset( $row['name'] ) ) {
                        $role[] = $row['name'];
                    }
                }

                $this->user->setRole($role);

                return true;
            }
            else {
                // This is an invalid application user
                return false;
            }
        }
    }


}
