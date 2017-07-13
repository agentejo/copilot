<?php

ini_set('max_execution_time', 600);
ini_set('memory_limit','1024M');

// check for Zip extension
if (!extension_loaded('zip')) {
    return CLI::writeln('Backup failed: PHP Zip Extension not loaded', false);
}

$zipfile     = 'copilot-backup-'.date('Y-m-d-Hms').'.zip';
$source      = CP_ROOT_DIR;
$destination = CP_ROOT_DIR."/{$zipfile}";
$zip         = new ZipArchive();

CLI::writeln('Backup started', 'blue');
 
if ($zip->open($destination, ZIPARCHIVE::CREATE)) {

    $source = realpath($source);
    $files  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
    
    foreach ($files as $file) {
        
        $file = realpath($file);
        
        if (is_dir($file)) {
            $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
        } else if (is_file($file)) {
            $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
        }
    }
}

$zip->close();

CLI::writeln('Backup finished! Backup file: '.$zipfile, true);