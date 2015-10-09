<?php

namespace CoCo\Controller;

use copi;

class Admin extends \Cockpit\AuthController {

    public function index() {

        $home  = copi::home();
        $pages = copi::pages('content:');

        return $this->render('coco:views/index.php', compact('pages', 'home'));
    }

    public function settings() {

        $meta = (object)$this->helper('yaml')->fromFile('site:content/_meta.yaml');
        $info = json_decode($this->helper('fs')->read('coco:module.json'));

        return $this->render('coco:views/settings.php', compact('meta', 'info'));
    }

    public function page($path) {

        $path = $this->app->path(str_replace('/coco/page/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $page     = new \Copilot\Page($path);
        $type     = $page->getType();
        $typedef  = [];

        if ($path = copi::path("types:{$type}.yaml")) {
            $typedef = $this->app->helper('yaml')->fromFile($path);
        }

        $type = array_merge([
            'name' => $type,
            'fields' => []
        ], $typedef);

        return $this->render('coco:views/page.php', compact('page', 'type'));
    }

    public function pages($path) {

        $path = $this->app->path(str_replace('/coco/pages/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $page = new \Copilot\Page($path);

        return $this->render('coco:views/pages.php', compact('page'));
    }

    public function file($path) {

        $path = $this->app->path(str_replace('/coco/file/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $file = new \Copilot\Resource($path);

        return $this->render('coco:views/file.php', compact('file'));
    }

    public function files($path) {

        $path = $this->app->path(str_replace('/coco/files/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $page = new \Copilot\Page($path);

        return $this->render('coco:views/files.php', compact('page'));
    }

    public function finder() {

        return $this->render('coco:views/finder.php');
    }
}
