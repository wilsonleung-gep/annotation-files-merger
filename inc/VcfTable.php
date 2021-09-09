<?php

class VcfTable {
  const HEADER_PATTERN = '/^#/';
  const BGZIP_SUFFIX = "gz";
  const CUSTOMTRACK_SUFFIX = "track.txt";

  protected $header;
  protected $vcfItems;
  protected $projectName;
  private $ucscDb;
  protected $appConfig;

  public function __construct($appConfig) {

    $this->header = <<<HEADER
##fileformat=VCFv4.1
##source=postulated_sequence_errors
##INFO=<ID=NM,Number=1,Type=String,Description="Authors">
#CHROM	POS	ID	REF	ALT	QUAL	FILTER	INFO
HEADER;

    $this->vcfItems = array();
    $this->projectName = null;
    $this->ucscDb = null;
    $this->appConfig = $appConfig;
  }

  public function getProjectName() {
    return $this->projectName;
  }

  public function getUcscDb() {
    return $this->ucscDb;
  }

  public function addVcfBlocks($contents) {
    foreach ($contents as $vcfLine) {
      if (preg_match(self::HEADER_PATTERN, $vcfLine)) {
        continue;
      }

      $item = new VcfItem($vcfLine);
      $projectName = $item->getProjectName();

      if ($this->projectName === null) {
        $this->projectName = $projectName;
      }

      if ($this->projectName !== $projectName) {
        throw new Exception(
          sprintf("Vcf files contain multiple project names: %s and %s",
                  $this->projectName, $projectName));
      }

      array_push($this->vcfItems, $item);
    }
  }

  public function writeVcfFile($tmpOutfile) {
    $fileWriter = new FileWriter($tmpOutfile);

    $fileWriter->open();

    $fileWriter->writeLine($this->header);

    $this->writeVcfItems($fileWriter);

    $fileWriter->close();
  }

  public function createCustomTrackSpec($vcfBgZipPath) {
    $customTrackFilePath =
      str_replace(self::BGZIP_SUFFIX, self::CUSTOMTRACK_SUFFIX, $vcfBgZipPath);

    $appPaths = $this->appConfig["app"];

    $webVcfBgZipPath = str_replace(
      $appPaths["rootdir"], $appPaths["webroot"], $vcfBgZipPath);

    $fileWriter = new FileWriter($customTrackFilePath);

    $fileWriter->open();

    $trackConfigLine = implode(" ", array(
      "track", "type=vcfTabix", 'visibility=3', 'name="Potential Errors"',
      'description="Potential Consensus Errors"',
      "bigDataUrl={$webVcfBgZipPath}"));

    $fileWriter->writeLine($trackConfigLine);

    $fileWriter->close();
  }

  public function indexVcfFile($vcfPath) {
    $vcfBgZipPath = sprintf("%s.%s", $vcfPath, self::BGZIP_SUFFIX);
    $binConfig = $this->appConfig["bin"];

    $bgzipCmd = sprintf("%s -c %s > %s",
      $binConfig["bgzip"], $vcfPath, $vcfBgZipPath);

    Utilities::runCommand($bgzipCmd);

    $tabixCmd = sprintf("%s -p vcf %s",
      $binConfig["tabix"], $vcfBgZipPath);

    Utilities::runCommand($tabixCmd);

    $this->createCustomTrackSpec($vcfBgZipPath);
  }

  private function writeVcfItems($fileWriter) {
    usort($this->vcfItems, "VcfTable_sortVcfItems");

    $fileWriter->writeCollection($this->vcfItems);
  }
}

function VcfTable_sortVcfItems($a, $b) {
  $aProject = $a->getProjectName();
  $aPosition = $a->getPosition();

  $bProject = $b->getProjectName();
  $bPosition = $b->getPosition();

  if ($aProject !== $bProject) {
    return ($aProject < $bProject) ? -1 : 1;
  }

  return ($aPosition === $bPosition) ? 0 : ($aPosition < $bPosition ? -1 : 1);
}
