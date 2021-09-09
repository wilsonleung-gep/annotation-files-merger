<?php
require_once(dirname(__FILE__)."/VType.php");

class Validator {
    private $errors;
    private $unsafe;
    public $clean;

    public function __construct($taintedArray) {
        $this->unsafe = $taintedArray;
        $this->errors = array();
        $this->clean = new stdClass();
    }

    public function validate($v) {
        if (array_key_exists($v->fieldName, $this->unsafe)) {
            $this->clean_field($v);
        } else {
            if ($v->isRequired) {
                $this->add_errors("Parameter {$v->fieldName} does not exists");
            }
        }
    }

    public function clean_field($v) {
        $fieldName = $v->fieldName;

        if (is_bool($this->unsafe[$fieldName])) {
            $this->clean_boolean($v);
            return;
        }

        $this->unsafe[$fieldName] = trim($this->unsafe[$fieldName]);

        if ($this->is_empty($this->unsafe[$fieldName])) {
            $this->add_errors("Field {$v->fieldLabel} is empty");
        } else {
            $func = "clean_{$v->type}";
            $this->$func($v);
        }
    }

    public function validate_uploaded_files($upload_files) {
        $this->clean->files = array();

        if (count($upload_files) <= 0) {
            $this->add_errors('No files have been submitted');
            return;
        }

        foreach ($upload_files as $fieldName => $file) {
            $this->check_file($file);
        }

    }

    private function check_file($file) {
        $extension_whitelist = array("txt", "gff", "fasta", "pep");

        if (isset($file["error"]) && $file["error"] != 0) {
            $this->add_errors("Error uploading the file: ".$file["error"]);
            return;
        }

        if (!isset($file["tmp_name"]) || (!is_uploaded_file($file["tmp_name"]))) {
            $this->add_errors("Cannot access uploaded file ".$file["error"]);
            return;
        }

        if (!isset($file['name'])) {
            $this->add_errors("Cannot access name of file file ");
            return;
        }

        $path_info = pathinfo($file['name']);
        $file_extension = $path_info["extension"];

        $is_valid_extension = false;
        foreach ($extension_whitelist as $extension) {
            if (strcasecmp($file_extension, $extension) == 0) {
                $is_valid_extension = true;
                break;
            }
        }

        if (!$is_valid_extension) {
            $this->add_errors("Invalid file extensions");
            return;
        }

        $this->clean->files[] = $file;
    }

    private function clean_string($v) {
        $pattern = '/^[a-zA-Z0-9-\(\){},.|\s:]+$/';
        $taintedInput = $this->unsafe[$v->fieldName];

        if ($this->check_pattern($pattern, $taintedInput)) {
            $this->clean->{$v->fieldName} = filter_var($taintedInput, FILTER_SANITIZE_STRING);
        } else {
            $this->add_errors("Field {$v->fieldLabel} contains illegal characters");
        }
    }

    private function clean_boolean($v) {
        $taintedInput = $this->unsafe[$v->fieldName];

        if ($taintedInput == true || $taintedInput == false) {
            $this->clean->{$v->fieldName} = $taintedInput;
        } else {
            $this->add_errors("Field {$v->fieldLabel} is not a valid boolean value");
        }
    }

    private function clean_int($v) {
        $taintedInput = $this->unsafe[$v->fieldName];

        if ($this->check_int($taintedInput)) {
            $this->clean->{$v->fieldName} = filter_var($taintedInput, FILTER_VALIDATE_INT);
        } else {
            $this->add_errors("Field {$v->fieldLabel} is not a valid integer");
        }
    }

    private function clean_float($v) {
        $taintedInput = $this->unsafe[$v->fieldName];

        if ($this->check_float($taintedInput)) {
            $this->clean->{$v->fieldName} = filter_var($taintedInput, FILTER_VALIDATE_FLOAT);
        } else {
            $this->add_errors("Field {$v->fieldLabel} is not a valid float");
        }
    }

    private function clean_custom($v) {
        $validate_func = $v->predicate;

        if ($validate_func === null) {
            throw new Exception("Custom validator requires a predicate");
        }

        $taintedInput = $this->unsafe[$v->fieldName];

        if ($validate_func($taintedInput)) {
            $this->clean->{$v->fieldName} = $taintedInput;
        } else {
            $this->add_errors("The field {$v->fieldLabel} is invalid");
        }
    }

    public function add_errors($errmsg) {
      $this->errors[] = $errmsg;
    }

    public function clear_errors() {
        $this->errors = array();
    }

    public function has_errors() {
        return (count($this->errors) != 0);
    }

    public function list_errors($separator="<br>") {
      return join($separator, $this->errors);
    }


    private function is_empty($taintedInput) {
      return (strlen(trim($taintedInput)) <= 0);
    }

    private function check_pattern($pattern, $taintedInput) {
        return (preg_match($pattern, $taintedInput) > 0);
    }

    private function check_int($taintedInput) {
        return ($taintedInput == strval(intval($taintedInput)));
    }

    private function check_float($taintedInput) {
        return ($taintedInput == strval(floatval($taintedInput)));
    }
}
