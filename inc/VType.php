<?php
class VType {
    public $type;
    public $fieldName;
    public $fieldLabel;
    public $isRequired;
    public $predicate;

    public function __construct($type, $fieldName, $fieldLabel, $isRequired=true, $predicate=null) {
        $this->type = $type;
        $this->fieldName = $fieldName;
        $this->fieldLabel = $fieldLabel;
        $this->isRequired = $isRequired;
        $this->predicate = $predicate;
    }
}
