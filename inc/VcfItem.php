<?php
class VcfItem {
    const NA = ".";
    const MIN_REQUIRED_FIELDS = 8;

    private $projectName;
    private $vcfLine;
    private $position;
    
    function __construct($vcfLine) {
        $fields = explode("\t", $vcfLine);
        
        if (count($fields) < self::MIN_REQUIRED_FIELDS) {
            $errorMsg = sprintf("Invalid VCF file. VCF file must have at least %d fields:<br>%s",
                                self::MIN_REQUIRED_FIELDS, $vcfLine);

            throw new Exception($errorMsg);
        }

        $this->vcfLine = $vcfLine;
        $this->projectName = $fields[0];
        $this->position = $fields[1];
    }

    function __toString() {
        return $this->vcfLine;
    }

    function getProjectName() {
      return $this->projectName;
    }
    
    function getPosition() {
      return $this->position;
    }
    
}