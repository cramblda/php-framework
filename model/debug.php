<?php

    /**
     * The debug class is an application wide diagnostic/error message handler.
     *
     * The debug class will display messages to the browser
     * and/or application log file.  Debug level, and log location
     * are all controlled through the main application configuration
     * file: bootstrap.php
     *
     * There are three settings in the bootstrap config file:
     * <ol>
     *  <li>APP_DEBUG_LEVEL (Set to None: 0, Errors: 1, Warnings: 2, Info: 3, or Verbose: 4)</li>
     *  <li>APP_DEBUG_LOG_LEVEL (Set to None: 0, Errors: 1, Warnings: 2, Info: 3, or Verbose: 4)</li>
     *  <li>APP_DEBUG_LOG (Set to location of the application log file)</li>
     * </ol>
     *
     * <b>General Usage:</b>
     * <ul>
     *  <li>$debug = new debug();</li>
     *  <li>$debug->write_debug( $timestamp, $level, $user, $mesg, $logMesg );</li>
     * </ul>
     *
     * @package Framework
     *
     */

class debug {

    private $fileHandle;
    private $appDebugLevel    = APP_DEBUG_LEVEL;
    private $appDebugLogLevel = APP_DEBUG_LOG_LEVEL;
    private $appDebugLog      = APP_DEBUG_LOG;
    public  $debugTimestamp;
    public  $debugLevel;
    public  $debugLevelDesc;
    public  $debugUser;
    public  $debugMesg;
    public  $debugLogMesg;
    public  $debugLogString;


    /**
     * The construct method prepares the application log file for logging if needed.
     *
     * If application logging is enabled, the construct method will determine
     * if the application log exisits.  If the file does not exist, it will
     * create the log file.  Once the log file is available, it will create a
     * file handle appending to the end of the log file with write only capabilities.
     *
     */
    function __construct ( ) {
        if ( $this->appDebugLogLevel > 0 ) {
            $this->fileHandle = fopen ( APP_DEBUG_LOG, "a" );
        }
    }


    /**
     * The destruct method closes the application log file handle if needed.
     *
     */
    function __destruct ( ) {
        if ( $this->fileHandle ) {
            fclose ( $this->fileHandle );
        }
    }


    /**
     * The write_debug method sends debug data to the browser and/or log file if needed.
     *
     * If debugging and/or logging is enabled, this method will write data to the
     * browser and/or application log. Debug will only be written if the debug level
     * is less than or equal to the current APP_DEBUG_LEVEL setting in the bootstrap
     * config file.  The method supports writing different formated messages to the
     * browser and the log file.  However, the messages can be the same format as well.
     * If either the browser or log message is NULL the debug will be ignored.  This
     * can be used as a way to write to only one source, when appropriate even if both
     * are methods are enabled.
     *
     * @param string $timestamp Timestamp for this message - use: date ( "Y-m-d h:u:s" );
     * @param int    $level     Debug Level for this message - use: 1, 2, 3, or 4
     * @param string $user      User that generated the message - use: $_SESSION["userName"];
     * @param string $mesg      Message to be displayed in browser
     * @param string $logMesg   Message to be displayed in log file
     */
    public function write_debug ( $timestamp, $level, $user, $mesg, $logMesg ) {

        $this->debugTimestamp = $timestamp;
        $this->debugLevel     = $level;
        $this->debugUser      = $user;
        $this->debugMesg      = $mesg;
        $this->debugLogMesg   = $logMesg;

        if ( $this->debugLevel == 1 ) {
            $this->debugLevelDesc = "[ ERROR   ]";
        }
        elseif ( $this->debugLevel == 2 ) {
            $this->debugLevelDesc = "[ WARNING ]";
        }
        elseif ( $this->debugLevel == 3 ) {
            $this->debugLevelDesc = "[ INFO    ]";
        }
        elseif ( $this->debugLevel == 4 ) {
            $this->debugLevelDesc = "[ VERBOSE ]";
        }

        if ( $this->appDebugLevel >= $this->debugLevel && $this->debugMesg != NULL ) {
            print_r ( $this->debugMesg );
            print "<br />";
        }

        if ( $this->appDebugLogLevel >= $this->debugLevel && $this->debugLogMesg != NULL ) {
            $this->debugLogString = $this->debugTimestamp ." ".$this->debugLevelDesc ." ". $this->debugUser ." : ". $this->debugLogMesg ."\n";
            fwrite ( $this->fileHandle, $this->debugLogString );
        }
    }

}
?>
