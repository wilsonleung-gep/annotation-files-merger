<?php

class IsoformCountChecker {

    private $dbConfig;
    private $gtfTable;

    public function __construct($dbConfig, $gtfTable) {
        $this->dbConfig = $dbConfig;
        $this->gtfTable = $gtfTable;
    }

    public function checkIsoformCounts() {
        $db = null;

        try {
            $db = new DBUtilities($this->dbConfig);

            $checklist = $this->createCheckList($db);

            $db->disconnect();

            return $checklist;
        } catch (Exception $e) {
            if (isset($db)) {
                $db->disconnect();
            }

            throw $e;
        }
    }

    private function createCheckList($db) {
        $checklist = array();

        $query = "SELECT mrnacount FROM gene_index WHERE FBname = ?";

        $stmt = $db->prepare($query);

        if (empty($stmt)) {
            throw new Exception("Cannot determine isoform count for submitted genes");
        }

        $geneList = $this->gtfTable->getGeneList();

        $sortedGeneNames = array_keys($geneList);
        sort($sortedGeneNames);

        foreach ($sortedGeneNames as $geneName) {
            $actualCount = count(array_keys($geneList[$geneName]));
            $checkItem = $this->checkGene($stmt, $geneName, $actualCount);

            array_push($checklist, $checkItem->getStatus());
        }

        return $checklist;
    }

    private function checkGene($stmt, $geneName, $actualCount) {
        $stmt->bind_param('s', $geneName);
        $stmt->execute();
        $stmt->bind_result($expectedCount);

        while ($stmt->fetch()) {
            if ($expectedCount === $actualCount) {
                return new CheckItem($geneName, array("status" => CheckItem::PASS));
            } else {
                $errorMsg = sprintf("Expected %d isoforms, merged file contains %d isoforms", $expectedCount, $actualCount);

                return new CheckItem($geneName, array("status" => CheckItem::WARN, "message" => $errorMsg));
            }
        }

        return new CheckItem($geneName,
                        array("status" => CheckItem::FAIL,
                            "message" => sprintf("Unable to find gene record: %s", $geneName)));
    }
}
