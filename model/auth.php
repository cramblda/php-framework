<?php

class auth {

    private $authMessage;
    private $authType;      // 1 = Internal, 2 = Database
    private $user;
    private $username;
    private $plainPassword;


    public function __construct () {

        defined('APP_AUTH_TYPE') or
            die ("Configuration Setting: APP_AUTH_TYPE is not set.");

        $this->authType = APP_AUTH_TYPE;
        if ( 1 > $this->authType || 2 < $this->authType ) {
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
        }
        else {
            $this->authMessage[] =
                'You are not authorized to use this application';

            return false;
        }

        // If auth is sucsessfull
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
            $this->authMessage[] = 'Credentials are invalid';
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
     * Verify if specified username is a valid user of this application.
     */
    private function userVerify() {

        // If Internal user - check config data for user
        if ( 1 == $this->authType ) {

            defined('APP_USER_LISTING') or
                die ("Configuration Setting: APP_USER_LISTING is not set.");

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

            $sql = "SELECT
                      *
                    FROM
                      auth_user
                    WHERE
                      username = ?";

            $dbObj = new db();
            $dbObj->dbPrepare( $sql );
            $dbObj->dbExecute( array( $this->username ) );

            $row = $dbObj->dbFetch( 'assoc' );

            if ( isset($row['username']) ) {
                $this->user = new user;
                $this->user->setEmail( $row['email'] );
                $this->user->setFirstName( $row['first_name'] );
                $this->user->setLastName( $row['last_name'] );
                $this->user->setPassword( $row['password'] );
                $this->user->setSalt( $row['salt'] );
                $this->user->setUsername( $row['username'] );

                $sql = "SELECT
                          ar.name
                        FROM
                          auth_user__auth_role auar
                        JOIN
                          auth_role ar ON auar.role_id = ar.id
                        WHERE
                          auar.user_id = ?";

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
