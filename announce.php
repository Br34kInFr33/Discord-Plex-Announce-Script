<?php

define('ROOT_DIR', '/home/woot/plex/');
define('SCAN_DIR', ROOT_DIR.'/movies');
define('JOB_DIR', ROOT_DIR.'/logs/');
define('URL', 'WEB HOOK URL');
define('ANNOUNCER', 'Movie Announcer');

function exist_check($file_full, $file) {
	$file = pathinfo($file_full, PATHINFO_BASENAME);
	$found = false;
	
	$lines = file(JOB_DIR.'movie_log.txt');
	foreach($lines as $line)
	{
        if(strpos($line, $file) !== false)
		{
	        $found = true;
        }
    }
    
	if(!$found)
	{
	    announce_video($file_full, $file);
	}
}

function announce_video($file_full, $file) {
	$file = pathinfo($file_full, PATHINFO_BASENAME);
	$info = pathinfo($file);
	
    $hookObject = json_encode([
        "content" => $file,
        "username" => ANNOUNCER,
        "tts" => false,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

    $headers = [ 'Content-Type: application/json; charset=utf-8' ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $hookObject);
    $response   = curl_exec($ch);

	$fh = fopen(JOB_DIR.'movie_log.txt', 'a') or die;
	$string_data = $file.PHP_EOL;
	fwrite($fh, $string_data);
	fclose($fh);
	
	sleep(3);
}

function scan_folder() {
	$dir = SCAN_DIR;
	
	if (!is_dir($dir))
	{
		$ok = mkdir($dir);
		if (!$ok) die('Cannot create destination folder!');
	}
	
	$dh = opendir($dir);
	while ( $file = readdir($dh) )
	{
		if ($file == '.' || $file == '..') continue;
		$file_full = $dir.'/'.$file;
		if ($file_full == SCAN_DIR) continue;
		exist_check($file_full, $dir, $file);
	}
}

scan_folder();

?>
