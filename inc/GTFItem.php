<?php
class GTFItem {
    const NA = ".";
    const NUM_FIELDS = 9;

    private $projectName;
    private $gtfLine;
    private $geneName;
    private $isoformName;
    private $startPos;
    private $endPos;

    function __construct($gtfLine) {
        $fields = explode("\t", $gtfLine);

        if (count($fields) !== self::NUM_FIELDS) {
            $errorMsg = sprintf("Invalid GTF file. GTF item should have %d fields.<br>%s<br>",
                                self::NUM_FIELDS,
                                $gtfLine);

            throw new Exception($errorMsg);
        }

        $this->gtfLine = $gtfLine;
        $this->projectName = $this->parseProjectName($gtfLine);
        $this->isoformName = $this->parseIsoformName($fields[8]);
        $this->geneName = $this->parseGeneName($this->isoformName);
        $this->startPos = intval($fields[3], 10);
        $this->endPos = intval($fields[4], 10);
    }

    function __toString() {
        return $this->gtfLine;
    }

    function getGeneName() {
        return $this->geneName;
    }

    function getIsoformName() {
        return $this->isoformName;
    }

    function getProjectName() {
        return $this->projectName;
    }

    function getStartPos() {
      return $this->startPos;
    }

    function getEndPos() {
      return $this->endPos;
    }

    static function compareCoordinates($a, $b) {
      $aStart = $a->getStartPos();
      $bStart = $b->getStartPos();

      if ($aStart === $bStart) {
        return $b->getEndPos() - $a->getEndPos();
      }

      return $aStart - $bStart;
    }

    private function parseProjectName($gtfLine) {
        $fields = explode("\t", $gtfLine);
        return $fields[0];
    }

    private function parseGeneName($transcriptName) {
      if (preg_match('/([^_]+)_(.*)-(.*)/', $transcriptName, $matches)) {
        return $matches[2];
      }

      if (preg_match('/(.*)-(.*)/', $transcriptName, $matches)) {
          return $matches[1];
      }

      return $transcriptName;
    }

    private function parseIsoformName($attributeStr) {
      if (preg_match('/transcript_id "(.*?)"/', $attributeStr, $matches)) {
          return $matches[1];
      }

      return $attributeStr;
    }
}
