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
    private $db;      // Either APP or IFAS
    private $dbHost;
    private $dbPort;
    private $dbName;
    private $dbUser;
    private $dbPass;
    private $dbOptions = array( );
    public  $dbObj;
    public  $dbRes;


    function __construct ( $db = "APP" ) {

        $this->db = $db;

        // Load applicable database configuration
        if ( $this->db == "APP" ) {
            $this->dbType = APP_DB_TYPE;
            $this->dbHost = APP_DB_HOST;
            $this->dbPort = APP_DB_PORT;
            $this->dbName = APP_DB_NAME;
            $this->dbUser = APP_DB_USER;
            $this->dbPass = APP_DB_PASS;
        }
        elseif ( $this->db == "IFAS" ) {
            $this->dbType = IFAS_DB_TYPE;
            $this->dbHost = IFAS_DB_HOST;
            $this->dbPort = IFAS_DB_PORT;
            $this->dbName = IFAS_DB_NAME;
            $this->dbUser = IFAS_DB_USER;
            $this->dbPass = IFAS_DB_PASS;
        }

        // Build correct PDO DSN based on database type
        if ( $this->dbType == "mysql" ) {
            $this->dsn = "mysql:host=$this->dbHost;port=$this->dbPort;dbname=$this->dbName";
        }
        elseif ( $this->dbType == "postgres" ) {
            $this->dsn = "pgsql:host=$this->dbHost;port=$this->dbPort;dbname=$this->dbName";
        }
        elseif ( $this->dbType == "odbc" ) {
            $this->dsn = "odbc:$this->dbName";
        }
        else {
            $mesg    = "DB Error " . $this->db . " DB can not process DB Type: " . $this->dbType . "<br />";
            $logMesg = $mesg;

            $debug = new debug();
            $debug->write_debug( date( "Y-m-d h:m:s"), 1, $_SESSION["userName"], $mesg, $logMesg );
            exit;
        }

        // Attempt to build database connection
        try {
            $this->dbh = new PDO($this->dsn, $this->dbUser , $this->dbPass , $this->dbOptions );
        }
        catch (PDOException $e) {
            $mesg    = "DB Error for " . $this->db . " - " . $e->getMessage() . "<br />";
            $logMesg = $mesg;

            $debug = new debug();
            $debug->write_debug( date( "Y-m-d h:m:s"), 1, $_SESSION["userName"], $mesg, $logMesg );
            exit;
        }

        //$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    }


    function __destruct () {
        $this->dbh = null;
    }


    public function dbStatus () {
        $this->dbRes = $this->dbh->exec("SELECT COUNT(*) FROM auth_users");
        return ( $this->dbh->errorCode() );
    }


    public function dbBeginTransaction () {

        // Write Debug
        $mesg  = "Begining " . $this->db . " DB Transaction";
        $debug = new debug();
        $debug->write_debug( date( "Y-m-d h:m:s"), 3, $_SESSION["userName"], $mesg, $mesg );

        // Start Transaction Mode
        $tranStatus = NULL;
        $tranStatus = $this->dbh->beginTransaction();

        // Error Handling
        if ( $tranStatus == 0 ) {
           $mesg  = "Database " . $this->db . " reported: Begining DB Transaction - Failed";
           $debug->write_debug( date( "Y-m-d h:m:s"), 1, $_SESSION["userName"], $mesg, $mesg );
        }
        else {
           $mesg  = "Database " . $this->db . " reported: Begining DB Transaction - Success";
           $debug->write_debug( date( "Y-m-d h:m:s"), 3, $_SESSION["userName"], $mesg, $mesg );
        }
    }


    public function dbRollback () {

        // Debug
        $mesg    = "Performing " . $this->db . " DB Transaction Rollback";
        $debug = new debug();
        $debug->write_debug( date( "Y-m-d h:m:s"), 3, $_SESSION["userName"], $mesg, $mesg );


        // Rollback Transaction
        $tranStatus = NULL;
        $tranStatus = $this->dbh->rollBack();


        // Error Handling
        if ( $tranStatus == 0 ) {
           $mesg  = "Database " . $this->db . " reported: DB Transaction Rollback - Failed";
           $debug->write_debug( date( "Y-m-d h:m:s"), 1, $_SESSION["userName"], $mesg, $mesg );
        }
        else {
           $mesg  = "Database " . $this->db . " reported: DB Transaction Rollback - Success";
           $debug->write_debug( date( "Y-m-d h:m:s"), 3, $_SESSION["userName"], $mesg, $mesg );
        }
    }


    public function dbCommit () {

        $errorCode = $this->dbErrorInfo();

        if ( $errorCode[1] > 0 ) {

            $mesg  = "A " . $this->db . " database error was detedcted when attempting commit - Performing DB Transaction Rollback";
            $debug = new debug();
            $debug->write_debug( date( "Y-m-d h:m:s"), 1, $_SESSION["userName"], $mesg, $mesg );

            $this->dbh->rollBack();
        }
        else {

            $mesg  = "Performing " . $this->db . " DB Transaction Commit";
            $debug = new debug();
            $debug->write_debug( date( "Y-m-d h:m:s"), 3, $_SESSION["userName"], $mesg, $mesg );

            // Commit Transaction
            $tranStatus = NULL;
            $tranStatus = $this->dbh->commit();

            // Error Handling
            if ( $tranStatus == 0 ) {
               $mesg  = "Database " . $this->db . " reported: DB Transaction Commit - Failed";
               $debug->write_debug( date( "Y-m-d h:m:s"), 1, $_SESSION["userName"], $mesg, $mesg );
            }
            else {
               $mesg  = "Database " . $this->db . " reported: DB Transaction Commit - Success";
               $debug->write_debug( date( "Y-m-d h:m:s"), 3, $_SESSION["userName"], $mesg, $mesg );
            }
        }
    }


    public function dbQuery ( $sql ) {

        $this->dbObj = $this->dbh->query( $sql );
    }


    public function dbPrepare ( $sql ) {

        $mesg     = "Preparing " . $this->db . " DB SQL statment <br />".$sql;
        $logMesg  = "Preparing " . $this->db . " DB SQL statment \n\t".$sql;
        $debug = new debug();
        $debug->write_debug( date( "Y-m-d h:m:s"), 3, $_SESSION["userName"], $mesg, $logMesg );

        $this->dbObj = $this->dbh->prepare( $sql );

        # Error Handling
        $errorCode = $this->dbErrorInfo();
        if ( $errorCode[1] > 0 || !is_object($this->dbObj) ) {

            $mesg  = $this->db . " DB Error " . $this->dbErrorCode() . "<br />";
            $mesg .= "<pre>";
            $mesg .= print_r ( $this->dbErrorInfo(), true );
            $mesg .= "</pre><br />";

            $logMesg = $this->db . " DB Error " . $this->dbErrorCode() . " " . print_r ( $this->dbErrorInfo(), true );

            $debug = new debug();
            $debug->write_debug( date( "Y-m-d h:m:s"), 1, $_SESSION["userName"], $mesg, $logMesg );
        }
    }


    public function dbExecute ( $params = NULL ) {

        $mesg     = "Executing " . $this->db . " DB SQL statment with Params: </br >";
        $mesg    .= print_r( $params, true );
        $logMesg  = "Executing " . $this->db . " DB SQL statment with Params: \n";
        $logMesg .= print_r( $params, true );

        $debug = new debug();
        $debug->write_debug( date( "Y-m-d h:m:s"), 3, $_SESSION["userName"], $mesg, $logMesg );

        $params = is_array( $params ) ? $params : array();
        $this->dbRes = $this->dbObj->execute($params);

        # Error Handling
        $errorCode = $this->dbErrorInfo();
        if ( $errorCode[1] > 0 ) {

            $mesg  = $this->db . " DB Error " . $this->dbErrorCode() . "<br />";
            $mesg .= "<pre>";
            $mesg .= print_r ( $this->dbErrorInfo(), true );
            $mesg .= "</pre><br />";

            $logMesg = $this->db . " DB Error " . $this->dbErrorCode() . " " . print_r ( $this->dbErrorInfo(), true );

            $debug = new debug();
            $debug->write_debug( date( "Y-m-d h:m:s"), 1, $_SESSION["userName"], $mesg, $logMesg );
        }

        return ( $this->dbRes );
    }


    public function dbFetch ( $fetchStyle ) {
        # fetchStyle should be one of: all, assoc, both, num, res

        $mesg    = "Executing " . $this->db . " DB data fetch type: ".$fetchStyle;
        $logMesg = $mesg;

        $debug = new debug();
        $debug->write_debug( date( "Y-m-d h:m:s"), 4, $_SESSION["userName"], $mesg, $logMesg );

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
        if ( "odbc" == $this->dbType ) {
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
