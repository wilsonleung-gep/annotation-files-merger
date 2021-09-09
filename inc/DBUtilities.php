<?php
class DBUtilities {
    public function __construct($dbsettings) {
        $dbconfig = $this->load_db_config($dbsettings);

        $this->dbconn = new mysqli($dbconfig['hostname'], $dbconfig['username'],
            $dbconfig['password'], $dbconfig['db']);

        if(mysqli_connect_errno()) {
            throw new Exception(
                "Cannot connect to the database: ".mysqli_connect_error());
        }
    }

    public function prepare($query) {
        $stmt = $this->dbconn->prepare($query);

        if (empty($stmt)) {
            throw new Exception("Error in prepare statement: ".$this->dbconn->error);
        }

        return $stmt;
    }

    private function load_db_config($cfg) {
        $required_params = array('username', 'password', 'db');

        foreach ($required_params as $param) {
            if (!isset($cfg[$param])) {
                throw new Exception("Error in database configuration file");
            }
        }

        if (!isset($cfg["hostname"])) {
            $cfg["hostname"] = "localhost";
        }

        return $cfg;
    }

    public function disconnect() {
        $this->dbconn->close();
        $this->dbconn = null;
    }

    function get_conn() {
        return $this->dbconn;
    }

    private $dbconn;
}
