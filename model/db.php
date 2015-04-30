<?php

    /**
     * The db class provides PHP PDO database support with integrated error handling.
     *
     * The db class is a robust PDO (PHP Database Objects) database interface
     * for all database access. Database error handling is built-in and will be
     * automatically routed through the debug class.  Currently PostgreSQL, MySQL
     * & ODBC are supported, but due to the use of PDO and this class, additional
     * database platforms can be added easily.  Database connection information is
     * controlled through the main application configuration file: bootstrap.php
     *
     * There are five settings each, in the bootstrap config file, for the APP
     * and IFAS database:
     *
     * <b>APP Database:</b>
     * <ol>
     *  <li>APP_DB_HOST (Set to database host name/ip)</li>
     *  <li>APP_DB_PORT (Set to port the database is listening on)</li>
     *  <li>APP_DB_NAME (Set to the name of the database)</li>
     *  <li>APP_DB_USER (Set to database user)</li>
     *  <li>APP_DB_PASS (Set to database password)</li>
     * </ol>
     *
     * <b>IFAS Database:</b>
     * <ol>
     *  <li>IFAS_DB_HOST (Set to database host name/ip)</li>
     *  <li>IFAS_DB_PORT (Set to port the database is listening on)</li>
     *  <li>IFAS_DB_NAME (Set to the name of the database)</li>
     *  <li>IFAS_DB_USER (Set to database user)</li>
     *  <li>IFAS_DB_PASS (Set to database password)</li>
     * </ol>
     *
     * <b>General Usage of db class:</b>
     * <ul>
     *  <li>$sql = "SELECT column1, column2 FROM table WHERE column1 = ? AND column2 = ?";</li>
     *  <li>$dbObj = new db();</li>
     *  <li>$dbObj->dbPrepare( $sql );</li>
     *  <li>$dbObj->dbExecute( array( whereItem1, whereItem2 ) );</li>
     *  <li>$row = $dbObj->dbFetch( "assoc" );</li>
     * </ul>
     *
     * @package Framework
     *
     */



class db {

    private $dbh;
    private $dsn;
    private $dbType;  // Either mysql, postgres, odbc
    private $dbHost;
    private $dbPort;
    private $dbName;
    private $dbUser;
    private $dbPass;
    private $dbOptions = array( );
    private $appUser;

    public  $dbObj;
    public  $dbRes;



    /*
     * On construct, build out database connection using configuration
     * parameters.
     *
     */
    function __construct () {

        // Set application user, if available, for diagnostic purposes
        $this->appUser = ( isset($_SESSION['username']) ? $_SESSION['username'] : 'system' );


        // Load applicable database configuration
        $this->dbType = APP_DB_TYPE;
        $this->dbHost = APP_DB_HOST;
        $this->dbPort = APP_DB_PORT;
        $this->dbName = APP_DB_NAME;
        $this->dbUser = APP_DB_USER;
        $this->dbPass = APP_DB_PASS;

        // Build correct PDO DSN based on database type
        switch( $this->dbType ) {
            case 'mysql':
                $this->dsn = "mysql:host=$this->dbHost;port=$this->dbPort;dbname=$this->dbName";
                break;

            case 'postgres':
                $this->dsn = "pgsql:host=$this->dbHost;port=$this->dbPort;dbname=$this->dbName";
                break;

            case 'odbc':
                $this->dsn = "odbc:$this->dbName";
                break;

            default:
                $mesg    = 'DB Error ' . $this->dbType . ' DB can not process DB Type: ' . $this->dbType . '<br />';
                $logMesg = $mesg;

                $debug = new debug();
                $debug->write_debug( date( 'Y-m-d h:m:s'), 1, $this->appUser, $mesg, $logMesg );
                exit;
        }

        // Attempt to build database connection
        try {
            $this->dbh = new PDO($this->dsn, $this->dbUser , $this->dbPass , $this->dbOptions );
        }
        catch (PDOException $e) {
            $mesg    = 'DB Error for ' . $this->dbType . ' database connection - ' . $e->getMessage() . '<br />';
            $logMesg = $mesg;

            $debug = new debug();
            $debug->write_debug( date( 'Y-m-d h:m:s'), 1, $this->appUser, $mesg, $logMesg );
            exit;
        }
    }


    /*
     * On destruct, close database connection.
     *
     */
    function __destruct () {
        $this->dbh = null;
    }


    /*
     * Get Database status !Deprecated
     *
     * This is a terrible way to get status, as auth_users may not be a table
     * in every application we write. However, there is no cross platform
     * PDO status function we can use. Use this method at risk of it's
     * elimination from future db.php updates.
     *
     */
    public function dbStatus () {
        $this->dbRes = $this->dbh->exec("SELECT COUNT(*) FROM auth_users");
        return ( $this->dbh->errorCode() );
    }


    public function dbBeginTransaction () {

        // Write Debug
        $mesg  = 'Begining ' . $this->dbType . ' DB Transaction';
        $debug = new debug();
        $debug->write_debug( date( 'Y-m-d h:m:s'), 3, $this->appUser, $mesg, $mesg );

        // Start Transaction Mode
        $tranStatus = NULL;
        $tranStatus = $this->dbh->beginTransaction();

        // Error Handling
        if ( $tranStatus == 0 ) {
           $mesg  = 'Database ' . $this->dbType . ' reported: Begining DB Transaction - Failed';
           $debug->write_debug( date( 'Y-m-d h:m:s'), 1, $this->appUser, $mesg, $mesg );
        }
        else {
           $mesg  = 'Database ' . $this->dbType . ' reported: Begining DB Transaction - Success';
           $debug->write_debug( date( 'Y-m-d h:m:s'), 3, $this->appUser, $mesg, $mesg );
        }
    }


    public function dbRollback () {

        // Debug
        $mesg    = 'Performing ' . $this->dbType . ' DB Transaction Rollback';
        $debug = new debug();
        $debug->write_debug( date( 'Y-m-d h:m:s'), 3, $this->appUser, $mesg, $mesg );


        // Rollback Transaction
        $tranStatus = NULL;
        $tranStatus = $this->dbh->rollBack();


        // Error Handling
        if ( $tranStatus == 0 ) {
           $mesg  = 'Database ' . $this->dbType . ' reported: DB Transaction Rollback - Failed';
           $debug->write_debug( date( 'Y-m-d h:m:s'), 1, $this->appUser, $mesg, $mesg );
        }
        else {
           $mesg  = 'Database ' . $this->dbType . ' reported: DB Transaction Rollback - Success';
           $debug->write_debug( date( 'Y-m-d h:m:s'), 3, $this->appUser, $mesg, $mesg );
        }
    }


    public function dbCommit () {

        $errorCode = $this->dbErrorInfo();

        if ( $errorCode[1] > 0 ) {

            $mesg  = 'A ' . $this->dbType . ' database error was detedcted when attempting commit - Performing DB Transaction Rollback';
            $debug = new debug();
            $debug->write_debug( date( 'Y-m-d h:m:s'), 1, $this->appUser, $mesg, $mesg );

            $this->dbh->rollBack();
        }
        else {

            $mesg  = 'Performing ' . $this->dbType . ' DB Transaction Commit';
            $debug = new debug();
            $debug->write_debug( date( 'Y-m-d h:m:s'), 3, $this->appUser, $mesg, $mesg );

            // Commit Transaction
            $tranStatus = NULL;
            $tranStatus = $this->dbh->commit();

            // Error Handling
            if ( $tranStatus == 0 ) {
               $mesg  = 'Database ' . $this->dbType . ' reported: DB Transaction Commit - Failed';
               $debug->write_debug( date( 'Y-m-d h:m:s'), 1, $this->appUser, $mesg, $mesg );
            }
            else {
               $mesg  = 'Database ' . $this->dbType . ' reported: DB Transaction Commit - Success';
               $debug->write_debug( date( 'Y-m-d h:m:s'), 3, $this->appUser, $mesg, $mesg );
            }
        }
    }


    public function dbQuery ( $sql ) {

        $this->dbObj = $this->dbh->query( $sql );
    }


    public function dbPrepare ( $sql ) {

        $mesg     = 'Preparing ' . $this->dbType . ' DB SQL statment <br />' . $sql;
        $logMesg  = 'Preparing ' . $this->dbType . ' DB SQL statment ' ."\n\t" . $sql;
        $debug = new debug();
        $debug->write_debug( date( 'Y-m-d h:m:s'), 3, $this->appUser, $mesg, $logMesg );

        $this->dbObj = $this->dbh->prepare( $sql );

        # Error Handling
        $errorCode = $this->dbErrorInfo();
        if ( $errorCode[1] > 0 || !is_object($this->dbObj) ) {

            $mesg  = $this->dbType . ' DB Error ' . $this->dbErrorCode() . '<br />';
            $mesg .= '<pre>';
            $mesg .= print_r ( $this->dbErrorInfo(), true );
            $mesg .= '</pre><br />';

            $logMesg = $this->dbType . ' DB Error ' . $this->dbErrorCode() . ' ' . print_r ( $this->dbErrorInfo(), true );

            $debug = new debug();
            $debug->write_debug( date( 'Y-m-d h:m:s'), 1, $this->appUser, $mesg, $logMesg );
        }
    }


    public function dbExecute ( $params = NULL ) {

        $mesg     = 'Executing ' . $this->dbType . ' DB SQL statment with Params: </br >';
        $mesg    .= print_r( $params, true );
        $logMesg  = 'Executing ' . $this->dbType . ' DB SQL statment with Params: ' . "\n\t";
        $logMesg .= print_r( $params, true );

        $debug = new debug();
        $debug->write_debug( date( 'Y-m-d h:m:s'), 3, $this->appUser, $mesg, $logMesg );

        $params = is_array( $params ) ? $params : array();
        $this->dbRes = $this->dbObj->execute($params);

        # Error Handling
        $errorCode = $this->dbErrorInfo();
        if ( $errorCode[1] > 0 ) {

            $mesg  = $this->dbType . ' DB Error ' . $this->dbErrorCode() . '<br />';
            $mesg .= '<pre>';
            $mesg .= print_r ( $this->dbErrorInfo(), true );
            $mesg .= '</pre><br />';

            $logMesg = $this->dbType . ' DB Error ' . $this->dbErrorCode() . ' ' . print_r ( $this->dbErrorInfo(), true );

            $debug = new debug();
            $debug->write_debug( date( 'Y-m-d h:m:s'), 1, $this->appUser, $mesg, $logMesg );
        }

        return ( $this->dbRes );
    }


    public function dbFetch ( $fetchStyle ) {
        # fetchStyle should be one of: all, assoc, both, num, res

        $mesg    = 'Executing ' . $this->dbType . ' DB data fetch type: ' . $fetchStyle;
        $logMesg = $mesg;

        $debug = new debug();
        $debug->write_debug( date( 'Y-m-d h:m:s'), 4, $this->appUser, $mesg, $logMesg );

    # What type of data should be returned
        switch ( $fetchStyle ) {

            case 'assoc':
                return $this->dbObj->fetch(PDO::FETCH_ASSOC);
                break;

            case 'all':
                return $this->dbObj->fetchAll();
                break;

            case 'both':
                return $this->dbObj->fetch(PDO::FETCH_BOTH);
                break;

            case 'num':
                return $this->dbObj->fetch(PDO::FETCH_NUM);
                break;

            case 'res':
                return $this->dbRes;
                break;

            default:
                return $this->dbRes;
                break;
        }
    }


    public function dbGetLastInsertId() {
        return ( $this->dbh->lastInsertId() );
    }


    public function dbQuote( $string ) {
        if ( 'odbc' == $this->dbType ) {
            return( "'" . str_replace( "'", "''", $string ) . "'" );
        }
        else {
            return ( $this->dbh->quote( $string ) );
        }
    }


    public function dbErrorCode () {
        if ( is_object( $this->dbObj ) ) {
            return ( $this->dbObj->errorCode() );
        }
        else {
            return ( $this->dbh->errorCode() );
        }
    }


    public function dbErrorInfo () {
        if ( is_object( $this->dbObj ) ) {
            return ( $this->dbObj->errorInfo() );
        }
        else {
            return ( $this->dbh->errorInfo() );
        }
    }

}
