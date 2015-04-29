<?php

class entity {

    protected $dirtyColumn;


    protected function insertDirtyColumn( $column, $property ) {
        $this->dirtyColumn[] = array( "columnName" => $column, "property" => $property );
    }


    public function insertRecord( $returnId = null ) {

        if ( is_array( $this->dirtyColumn ) ) {

            # Check to see if a connection was provided
            if ( is_object( $this->db ) ) {
                $db = $this->db;
            }
            else {
                $db = new db( $this->db );
            }

            $sql = "INSERT INTO " . $this->tableName;

            $columList = " (";
            $valueList = " (";
            foreach ( $this->dirtyColumn as $index => $data ) {
                $col  = $data["columnName"];
                $prop = $data["property"];
                $columList .= $col . ", ";

                // Handle null values
                if ( "null" == $this->$prop || "NULL" == $this->$prop  ) {
                    $valueList .= "NULL" . ", ";
                }
                // Don't surond numerics in quotes
                elseif(is_int($this->$prop) || is_float($this->$prop) || is_double($this->$prop)) {
                    $valueList .=  $this->$prop . ", ";
                }
                // Escape quotes in text
                elseif ( strstr( $this->$prop, "'" ) ) {
                    $valueList .= $db->dbQuote( $this->$prop ) . ", ";
                }
                // Quote Text
                else {
                    $valueList .= "'" . $this->$prop . "', ";
                }
            }

            $columList = substr( $columList, 0, -2 ) . ") ";
            $valueList = substr( $valueList, 0, -2 ) . ") ";

            $sql .= $columList . " VALUES " . $valueList;

            $db->dbPrepare( $sql );
            $db->dbExecute();

            $this->dirtyColumn = null;

            if ( null != $returnId ) {
                return ( $db->dbGetLastInsertId() );
            }
        }
    }

    public function updateRecord() {

        if ( is_array( $this->dirtyColumn ) ) {

            # Check to see if a connection was provided
            if ( is_object( $this->db ) ) {
                $db = $this->db;
            }
            else {
                $db = new db( $this->db );
            }

            $idColumn = "id";
            if ( $this->idColumn ) {
                $idColumn = $this->idColumn;
            }

            $sql = "UPDATE " . $this->tableName . " SET ";

            foreach ( $this->dirtyColumn as $index => $data ) {
                $col  = $data["columnName"];
                $prop = $data["property"];

                // Handle null values
                if ( "null" == $this->$prop || "NULL" == $this->$prop  ) {
                    $sql .= $col . " = NULL, ";
                }
                // Don't quote numerics
                elseif(is_int($this->$prop) || is_float($this->$prop) || is_double($this->$prop)) {
                    $sql .= $col . " = " . $this->$prop . ", ";
                }
                // Escape quotes in text
                elseif ( strstr( $this->$prop, "'" ) ) {
                    $sql .= $col . " = " . $db->dbQuote( $this->$prop ) . ", ";
                }
                // Wrap text in quotes
                else {
                    $sql .= $col . " = '" . $this->$prop . "', ";
                }
            }

            $sql = substr( $sql, 0, -2 );
            $sql .= " WHERE " . $idColumn . " = '" . $this->id . "'";

            $db->dbPrepare( $sql );
            $db->dbExecute();

            $this->dirtyColumn = null;
        }
    }

}