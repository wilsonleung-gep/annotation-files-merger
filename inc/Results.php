<?php
class Results {
    const SUCCESS = 'success';
    const FAILURE = 'failure';

    private $status;
    private $message;
    private $results;

    public function __construct($options=array()) {
        $default_settings = array(
            "status" => self::SUCCESS,
            "message" => "",
            "results" => array()
        );

        $settings = array_merge($default_settings, $options);

        $this->status = $settings["status"];
        $this->message = $settings["message"];
        $this->results = $settings["results"];
    }


    public function print_result($accept_type) {
        $writer = $this->get_writer($accept_type);
        $writer->print_result($this);
    }

    public function update_result($config) {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    public function is_successful() {
        return ($this->status == self::SUCCESS);
    }

    public function append_result($r, $key=null) {
        if ($key == NULL) {
            array_push($this->results, $r);
        } else {
            $this->results[$key] = $r;
        }
    }

    public function set_status($predicate) {
        $this->status = ($predicate) ? self::SUCCESS : self::FAILURE;
    }

    public function set_message($msg) {
        $this->message = $msg;
    }

    public function set_result($r) {
        $this->results = $r;
    }

    public function get_status() {
        return $this->status;
    }

    public function get_message() {
        return $this->message;
    }

    public function get_results() {
        return $this->results;
    }

    private function get_writer($accept_type) {
        $class_name = strtoupper($accept_type)."ResultWriter";
        return new $class_name();
    }
}
