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
            $root     = $this->app->path('#root:');
            $distroot = false;

            // find cockpit dist root
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder)) as $file) {
                if ($file->getFilename() == 'favicon.png') {
                    $distroot = dirname($file->getRealPath()).'/cockpit';
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
