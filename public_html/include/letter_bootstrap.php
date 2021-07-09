<?php
function getFilesForInclude($directory, &$files) {
	$result = scandir($directory);
	foreach ($result as $file){
		if($file == "." || $file == ".."){
			continue;
		}
		$filename = $directory.$file;
		if(is_dir($filename)){
			getFilesForInclude($filename."/", $files);
			continue;
		}
		$files[] = $filename;
	}
}
$baseDir = App::config('basedir')."/Helpers/Letter/";
require_once $baseDir . "Letter.php";
require_once $baseDir . "Distribution/DistributionTemplates.php";
require_once $baseDir . "Distribution/DistributionTypes.php";
require_once $baseDir . "Service/ServiceLetter.php";
$files = [];
getFilesForInclude($baseDir, $files);
foreach ($files as $file) {
	require_once $file;
}

