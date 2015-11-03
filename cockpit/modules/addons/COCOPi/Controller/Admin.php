<?php

namespace COCOPi\Controller;

use copi;

class Admin extends \Cockpit\AuthController {

    public function index() {

        $home  = copi::home();
        $pages = copi::pages('content:');

        return $this->render('cocopi:views/index.php', compact('pages', 'home'));
    }

    public function settings() {

        $meta    = (object)$this->helper('yaml')->fromFile('site:content/_meta.yaml');
        $info    = json_decode($this->helper('fs')->read('cocopi:module.json'));
        $license = $this->module('cocopi')->getLicense();

        return $this->render('cocopi:views/settings.php', compact('meta', 'info', 'license'));
    }

    public function page($path) {

        $path = $this->app->path(str_replace('/cocopi/page/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $page     = copi::page($path);
        $type     = $this->getPageType($page);

        return $this->render('cocopi:views/page.php', compact('page', 'type'));
    }

    public function pages($path) {

        $path = $this->app->path(str_replace('/cocopi/pages/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $page = copi::page($path);
        $type = $this->getPageType($page);

        return $this->render('cocopi:views/pages.php', compact('page', 'type'));
    }

    public function file($path) {

        $path = $this->app->path(str_replace('/cocopi/file/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $file = copi::resource($path);

        return $this->render('cocopi:views/file.php', compact('file'));
    }

    public function files($path) {

        $path = $this->app->path(str_replace('/cocopi/files/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $page = copi::page($path);

        return $this->render('cocopi:views/files.php', compact('page'));
    }

    public function finder() {

        return $this->render('cocopi:views/finder.php');
    }

    protected function getPageType($page) {

        $type     = $page->type();
        $typedef  = [];

        if ($path = copi::path("types:{$type}.yaml")) {
            $typedef = $this->app->helper('yaml')->fromFile($path);
        }

        $type = array_replace_recursive([
            'name' => $type,
            'ext' => 'html',
            'content' => [
                'visible' => true,
                'type'    => $page->ext() == 'md' ? 'markdown':'html'
            ],
            'meta' => []
        ], (array)$typedef);

        return $type;
    }
}
