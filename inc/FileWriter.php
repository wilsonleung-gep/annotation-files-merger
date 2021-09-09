<?php

class FileWriter {
  public $fileName;
  public $fhOutfile;
  
  function __construct($fileName) {
    $this->fileName = $fileName;
  }
  
  function open() {
    $this->fhOutfile = fopen($this->fileName, "w");
    if (!$this->fhOutfile) {
      throw new Exception(
        sprintf("Cannot write to output file: %s", $this->fileName));
    }
  }
  
  function writeLine($line) {
    if (! is_resource($this->fhOutfile)) {
      $this->open();
    }
    
    $writeStatus = fwrite($this->fhOutfile, $line."\n");
    if ($writeStatus === FALSE) {
      throw new Exception("Cannot write to output file");
    }
  }
  
  function writeCollection($items) {
    if (! is_array($items)) {
      throw new Exception("Parameter should be an array of items");
    }
    
    $numItems = count($items);
    
    for ($i=0; $i<$numItems; $i++) {
      $this->writeLine($items[$i]->__toString());
    }
  }
  
  function close() {
    if (is_resource($this->fhOutfile)) {
      $closeStatus = fclose($this->fhOutfile);
      
      if ($closeStatus === FALSE) {
        throw new Exception("Cannot close output file");
      }
    }
  }
  
  
}
