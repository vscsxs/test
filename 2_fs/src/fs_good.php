<?php

$dir = getcwd();
chdir('/datafiles');
foreach (glob("[a-zA-Z0-9]*.ixt") as $filename) {
	echo "$filename\n";
}
chdir($dir);
