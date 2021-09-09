<?php

class GTFTable {

  const HEADER_PATTERN = '/^track /';

  private $trackSettings;
  private $projectName;
  private $gtfItems;
  private $geneList;
  private $ucscDb;
  private $minProjectCoords;
  private $maxProjectCoords;

  public function __construct($trackOptions = array()) {
    $this->trackSettings = (array_merge(array(
                "name" => "GeneAnnotations",
                "description" => "All Gene Annotations",
                "color" => "0,0,200",
                "visibility" => Visibility::pack
                    ), $trackOptions));

    $this->gtfItems = array();
    $this->geneList = array();
    $this->ucscDb = null;
    $this->projectName = null;
    $this->minProjectCoords = null;
    $this->maxProjectCoords = null;
  }

  function parseUcscDb($gtfLine) {
    $pattern = '/description="Custom Gene Model for (\S+)"/';

    if (preg_match($pattern, $gtfLine, $matches)) {
      $ucscDb = $matches[1];

      if (($this->ucscDb !== null) && ($this->ucscDb !== $ucscDb)) {
        throw new Exception(
          sprintf("GTF files contain multiple databases: %s and %s",
            $this->ucscDb,
            $ucscDb)
        );
      } else {
        $this->ucscDb = $ucscDb;
      }
    }
  }

  function addGTFBlocks($contents) {
    foreach ($contents as $gtfLine) {
      if (preg_match(self::HEADER_PATTERN, $gtfLine)) {
        $this->parseUcscDb($gtfLine);
        continue;
      }

      $item = new GTFItem($gtfLine);
      $geneName = $item->getGeneName();
      $isoformName = $item->getIsoformName();
      $projectName = $item->getProjectName();

      $itemEnd = $item->getEndPos();

      if (($this->projectName !== null) && ($this->projectName !== $projectName)) {
        throw new Exception(
        sprintf("GTF files contain multiple project names: %s and %s", $this->projectName, $projectName));
      }

      $this->projectName = $projectName;

      $this->updateMinPosition($item);
      $this->updateMaxPosition($item);

      array_push($this->gtfItems, $item);

      if (!array_key_exists($geneName, $this->geneList)) {
        $this->geneList[$geneName] = array();
      }

      $this->geneList[$geneName][$isoformName] = 1;
    }
  }

  function updateMinPosition($item) {
    $itemStart = $item->getStartPos();

    if ($this->minProjectCoords === null) {
      $this->minProjectCoords = $itemStart;
    } else {
      $this->minProjectCoords = min($this->minProjectCoords, $itemStart);
    }
  }

  function updateMaxPosition($item) {
    $itemEnd = $item->getEndPos();

    if ($this->maxProjectCoords === null) {
      $this->maxProjectCoords = $itemEnd;
    } else {
      $this->maxProjectCoords = max($this->maxProjectCoords, $itemEnd);
    }
  }

  function getPosition() {
    if ($this->minProjectCoords === null) {
      return $this->projectName;
    }

    return sprintf(
      "%s:%d-%d",
      $this->projectName,
      $this->minProjectCoords,
      $this->maxProjectCoords
    );
  }

  function getProjectName() {
    return $this->projectName;
  }

  function getGeneList() {
    return $this->geneList;
  }

  function getGTFItems() {
    return $this->gtfItems;
  }

  function getNumIsoforms($geneName) {
    if (!array_key_exists($geneName, $this->geneList)) {
      throw new Exception("Unknown gene");
    }

    return count(array_keys($this->geneList[$geneName]));
  }

  function getUcscDb() {
    return $this->ucscDb;
  }

  function writeGTFFile($tmpfilename, $browserHeader = null) {
    $fileWriter = new FileWriter($tmpfilename);

    $fileWriter->open();

    if ($browserHeader !== null) {
      $fileWriter->writeLine($browserHeader);
    }

    $fileWriter->writeLine($this->buildTrackHeader());
    $fileWriter->writeCollection($this->gtfItems);

    $fileWriter->close();
  }

  private function buildTrackHeader() {
    $properties = array("track");

    $propertiesRequiredQuotes = array("name" => 1, "description" => 1);

    foreach ($this->trackSettings as $key => $value) {
      $tpl = (array_key_exists($key, $propertiesRequiredQuotes)) ? '%s="%s"' : "%s=%s";

      array_push($properties, sprintf($tpl, $key, $value));
    }

    return implode(" ", $properties);
  }

  private function writeLine($fh_out, $line) {
    $line = sprintf("%s\n", $line);

    if (fwrite($fh_out, $line) === FALSE) {
      throw new Exception("Cannot write to GTF file");
    }
  }
}
