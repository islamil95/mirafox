<?php

function getXhprofReportName() {
	$subPath = date("Y-m-d") . "/" . date("H");
	return "{$subPath}/" . md5($_SERVER['REQUEST_URI']) . microtime() . ".xhprof";
}

if (function_exists('xhprof_disable') && false) {
	$xhprofData = xhprof_disable();
	$saveStatTo = dirname($_SERVER["DOCUMENT_ROOT"], 2) . "/kwork_logs/xhprof/" . getXhprofReportName();
	mkdir(dirname($saveStatTo), 0775, true);
	file_put_contents($saveStatTo, "<?php return " . var_export($xhprofData, true));
}
