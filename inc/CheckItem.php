<?php

class CheckItem {
    const PASS = "Pass";
    const SKIP = "Skip";
    const FAIL = "Fail";
    const WARN = "Warn";
    
    public function __construct($criteria, $options=array()) {
        $settings = array_merge(array(
            "message" => "",
            "status" => self::SKIP
        ), $options);

        $this->criteria = $criteria;
        $this->message = $settings["message"];
        $this->status = $settings["status"];
    }

    public function failedCheck() {
        return ($this->status === self::FAIL);
    }

    public function getStatus() {
        return array(
            "criteria" => $this->criteria,
            "status" => $this->status,
            "message" => $this->message);
    }

    private $criteria;
    private $status;
    private $message;
}
?>
