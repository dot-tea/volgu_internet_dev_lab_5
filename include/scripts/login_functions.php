<?php
	function fetch_codes() {
		$serialized_codes = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/include/data/codes.txt');
		$codes = unserialize($serialized_codes);
		return $codes;
	}
?>