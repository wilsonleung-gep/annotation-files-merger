<?php
class OverlapChecker {
  protected $gtfItems;

  public function __construct($gtfTable)
  {
    $this->gtfItems = $gtfTable->getGTFItems();
    usort($this->gtfItems, array("GTFItem", "compareCoordinates"));
  }

  public function checkOverlap()
  {
    $overlapItems = array();
    $numItems = count($this->gtfItems);

    for ($i=0; $i<$numItems-1; $i++) {
      $a = $this->gtfItems[$i];
      $b = $this->gtfItems[$i+1];

      if ($this->isFeatureOverlap($a, $b)) {
        $overlapID = $this->overlapItemID($a, $b);

        $overlapItems[$overlapID] = sprintf(
          "Exon coordinates from %s overlap with %s",
          $a->getGeneName(),
          $b->getGeneName()
        );
      }
    }

    return $overlapItems;
  }

  private function overlapItemID($a, $b) {
    $names = array($a->getGeneName(), $b->getGeneName());
    sort($names);

    return implode("_", $names);
  }


  private function isFeatureOverlap($a, $b) {
    return (
      ($this->overlapSize($a, $b) > 0) &&
      ($a->getGeneName() !== $b->getGeneName())
    );
  }


  private function overlapSize($a, $b) {
    $maxStart = max($a->getStartPos(), $b->getStartPos());
    $minEnd = min($a->getEndPos(), $b->getEndPos());

    return $minEnd - $maxStart;
  }

}
