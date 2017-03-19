<?php

$files = scandir('/datafiles');
sort($files, SORT_NATURAL | SORT_FLAG_CASE);
foreach ($files as $file) {
	if (preg_match('~^[a-zA-Z0-9]+\.ixt$~', $file)) {
		echo "$file\n";
	}
}
