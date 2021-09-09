<?php

session_start();

define("BASIC_HTML_UPLOAD", "none");

function __autoload($class_name) {
  require_once("../inc/{$class_name}.php");
}


$APPCONFIG_FILE = "../conf/app.ini.php";
$APPCONFIG = parse_ini_file($APPCONFIG_FILE, true);


function main() {
  try {
    $validator = untaintVariables();

    $mergeFileName = getMergeFileName($validator);

    if ($validator->clean->checkStatus) {
      validateMergeFile($mergeFileName, $validator);
    } else {
      combineFiles($mergeFileName, $validator->clean->files);

      if ($validator->clean->uploadType === BASIC_HTML_UPLOAD) {
        validateMergeFile($mergeFileName, $validator);
      } else {
        reportUploadProgress();
      }
    }
  } catch (Exception $e) {
    reportErrors($e->getMessage());
  }
}

main();



function reportUploadProgress() {
  echo json_encode(array("status" => Results::SUCCESS,
      "message" => "UPLOAD_IN_PROGRESS"));
}

function validateMergeFile($tmpfilename, $validator) {
  try {
    if ($validator->clean->fileType === "gff") {
      processGTFFiles($tmpfilename);
    } else if ($validator->clean->fileType === "vcf") {
      processVcfFiles($tmpfilename);
    } else {
      processSequenceFiles($tmpfilename);
    }
  } catch (Exception $e) {
    reportErrors($e->getMessage());
  }
}

function processGTFFiles($tmpfilename) {
  global $APPCONFIG;

  $gtfTable = new GTFTable();

  $reader = new FileReader(getFilePath($tmpfilename, "rootdir"));
  $gtfTable->addGTFBlocks($reader->getDataLines());

  try {
    $checker = new IsoformCountChecker($APPCONFIG["database"], $gtfTable);
    $checklist = $checker->checkIsoformCounts();

    $overlapChecker = new OverlapChecker($gtfTable);
    $overlapChecklist = $overlapChecker->checkOverlap();
  } catch (Exception $e) {
    reportErrors($e->getMessage());
  }

  $gtfTable->writeGTFFile(getFilePath($tmpfilename, "rootdir"));

  reportResults(array(
      "webpath" => getFilePath($tmpfilename),
      "position" => $gtfTable->getPosition(),
      "checklist" => $checklist,
      "overlap" => array_values($overlapChecklist),
      "ucscDb" => $gtfTable->getUcscDb()
  ));
}

function processVcfFiles($tmpfilename) {
  global $APPCONFIG;

  $vcfTable = new VcfTable($APPCONFIG);

  $vcfTextFilePath = getFilePath($tmpfilename, "rootdir");

  $reader = new FileReader($vcfTextFilePath);

  $vcfTable->addVcfBlocks($reader->getDataLines());
  $vcfTable->writeVcfFile($vcfTextFilePath);
  $vcfTable->indexVcfFile($vcfTextFilePath);

  reportResults(array(
      "webpath" => getFilePath($tmpfilename),
      "position" => $vcfTable->getProjectName(),
      "customText" => getFilePath(
          sprintf("%s.%s", $tmpfilename, VcfTable::CUSTOMTRACK_SUFFIX)),
      "ucscDb" => $vcfTable->getUcscDb()
  ));
}

function processSequenceFiles($tmpfilename) {
  reportResults(array(
      "webpath" => getFilePath($tmpfilename)
  ));
}

function combineFiles($tmpfilename, $files_list) {
  $dataStore = array();

  foreach ($files_list as $fieldName => $file) {
    $reader = new FileReader($file["tmp_name"]);
    array_push($dataStore, join(FileReader::UNIX, $reader->getDataLines()));
  }

  createMergedFile($tmpfilename, join(FileReader::UNIX, $dataStore));
}

function getMergeFileName($validator) {
  $extension = ($validator->clean->fileType === "vcf") ?
          "vcf.txt" : $validator->clean->fileType;

  return sprintf("%s.%s", $validator->clean->sessionid, $extension);
}

function getFilePath($tmpfilename, $type = "webroot") {
  global $APPCONFIG;

  return sprintf("%s/%s/%s",
    $APPCONFIG["app"][$type], $APPCONFIG["app"]["trashdir"], $tmpfilename);
}

function createMergedFile($tmpfilename, $contents) {
  $tmpfilepath = getFilePath($tmpfilename, "rootdir");

  $fhOut = fopen($tmpfilepath, "a");
  fwrite($fhOut, $contents);
  fwrite($fhOut, FileReader::UNIX);
  fclose($fhOut);
}

function reportResults($result_data) {
  echo json_encode(array_merge(array(
      "status" => Results::SUCCESS,
      "message" => "",
      "position" => "unknown"
                  ), $result_data));
}

function reportErrors($error_msg) {
  echo json_encode(array(
      "status" => Results::FAILURE,
      "message" => $error_msg,
      "webpath" => ""));

  exit;
}

function untaintVariables() {
  $validator = validateVariables();

  if ($validator->has_errors()) {
    reportErrors($validator->list_errors());
  }

  return $validator;
}

function groupFieldsByFile($filelist) {
  $num_files = count($filelist["name"]);
  $file_keys = array_keys($filelist);

  $files = array();

  for ($i = 0; $i < $num_files; $i++) {
    foreach ($file_keys as $key) {
      $files[$i][$key] = $filelist[$key][$i];
    }
  }
  return $files;
}

function validateVariables() {
  $validator = new Validator($_POST);

  $checkFileTypeFunc = function ($t) {
    return in_array($t, array("gff", "pep", "fasta", "vcf"));
  };

  $variablesToCheck = array(
      new VType("string", "sessionid", "Session ID"),
      new VType("custom", "fileType", "File Type", true, $checkFileTypeFunc),
      new VType("string", "uploadType", "Upload Type", false),
      new VType("string", "checkStatus", "Check status request", false)
  );

  $validator->clean->uploadType = BASIC_HTML_UPLOAD;
  $validator->clean->checkStatus = false;

  foreach ($variablesToCheck as $v) {
    $validator->validate($v);
  }

  if ($validator->clean->checkStatus) {
    return $validator;
  }

  $files = $_FILES;

  if ($validator->clean->uploadType === BASIC_HTML_UPLOAD) {
    if (array_key_exists("ufile", $_FILES)) {
      $files = groupFieldsByFile($_FILES["ufile"]);
    }
  }

  $validator->validate_uploaded_files($files);

  return $validator;
}

