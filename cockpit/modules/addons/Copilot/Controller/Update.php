<?php

namespace Copilot\Controller;

use copi;

class Update extends \Cockpit\AuthController {

    public function index() {

        return $this->render('copilot:views/update.php');
    }

    public function update($step = 0) {

        set_time_limit(0);

        if (!is_writable($this->app->path('site:'))) {
            return json_encode(["success" => false, "message" => 'Site root folder is not writable!']);
        }

        switch(intval($step)) {
            case 0:
                return $this->step0();
                break;
            case 1:
                return $this->step1();
                break;
            case 2:
                return $this->step2();
                break;
            case 3:
                return $this->step3();
                break;
            case 4:
                return $this->step4();
                break;
        }

        return false;
    }

    /**
     * Create backup zip
     */
    protected function step0() {

        $sourcefolder = $this->app->path('site:');
        $target       = $sourcefolder.date('Y-m-d.H-m').'.'.uniqid().'.backup.zip';
        $zip          = new \ZipArchive();

        if (!$zip->open($target, \ZIPARCHIVE::CREATE)) {
            return false;
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourcefolder), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {

            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);

            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ) continue;
            if (preg_match('/\.git/', $file)) continue;
            if (preg_match('/\.DS_Store/', $file)) continue;
            if (preg_match('/\.backup\.zip$/', $file)) continue;

            $file = realpath($file);

            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($sourcefolder, '', $file . '/'));
            }else if (is_file($file) === true){
                $zip->addFromString(str_replace($sourcefolder, '', $file), file_get_contents($file));
            }
        }

        $zip->close();

        return $this->app->pathToUrl($target);
    }

    /**
     * Download latest zip
     */
    protected function step1() {

        $zipurl  = 'https://github.com/copilotPi/copilotpi-kickstart/archive/master.zip';
        $message = false;
        $success = false;

        if (!is_writable($this->app->path('site:'))) {
            $message = 'Site root folder is not writable!';
        } else {

            if (file_put_contents($this->app->path("#tmp:")."/copilotpi-latest.zip", $handle = @fopen($zipurl, 'r'))) {
                $success = true;
            } else {
                $message = "Couldn't download latest copilotPi!";
            }

            @fclose($handle);
        }

        return json_encode(["success" => $success, "message" => $message]);

    }

    /**
     * Extract the contents of the zip
     */
    protected function step2() {

        $success = false;
        $message = false;

        if ($this->app->path("#tmp:copilotpi-latest.zip") && $this->app->helper("fs")->mkdir("#tmp:copilotpi-latest", 0777)) {

            $zip     = new \ZipArchive;
            $zipfile = $this->app->path("#tmp:copilotpi-latest.zip");

            if ($zip->open($zipfile) === true) {

                $folder  = $this->app->path("#tmp:copilotpi-latest");
                $success = $zip->extractTo($folder) ? $zip->close() : false;
            }
        }

        if (!$success) {
            $message = 'Extracting release file failed!';
        }

        return json_encode(["success" => $success, "message" => $message]);

    }

    /**
     * Copy latest files
     */
    protected function step3() {

        $success = false;

        if ($folder = $this->app->path("#tmp:copilotpi-latest")) {

            $fs       = $this->app->helper("fs");
            $root     = $this->app->path('site:');
            $distroot = false;

            // find cockpit dist root
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder)) as $file) {
                if ($file->getFilename() == 'favicon.png') {
                    $distroot = dirname($file->getRealPath());
                    break;
                }
            }

            if ($distroot) {

                // clean existing installation

                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root), \RecursiveIteratorIterator::SELF_FIRST);

                foreach ($files as $file) {

                    if (!$file->isFile()) continue;
                    if (preg_match('/(site\/|content\/|config\/|storage\/|modules\/addons|favicon\.png|\.backup\.zip)/', (string)$file)) continue;

                    @unlink($file->getRealPath());
                }

                $fs->removeEmptySubFolders($root);

                $fs->delete($distroot.'/content');
                $fs->delete($distroot.'/site');
                $fs->delete($distroot.'/cockpit/config');

                $fs->copy($distroot, $root);
            }

            $fs->delete($folder);

            $success = $distroot ? true : false;
        }

        if (!$success) {
            $message = 'Override current release failed!';
        }

        return json_encode(["success" => $success, "message" => $message]);

    }

    /**
     * Cleanup
     */
    protected function step4() {

        $this->module('cockpit')->clearCache();

        return '{"success": true}';

    }
}
