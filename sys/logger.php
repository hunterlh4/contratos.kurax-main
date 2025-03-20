<?php

function _log($filepath, $content, $action="a"){
	$myfile = fopen($filepath, $action) or die("Unable to open file!");
	fwrite($myfile, date('Y-m-d H:i:s ').$content."\r\n");
	fclose($myfile);
}

?>