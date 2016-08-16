<?php

namespace Copilot\Controller;

use copi;
use Copilot\Lib\Type;

class Admin extends \Cockpit\AuthController {

    public function index() {

        $home  = copi::home();
        $pages = copi::pages('content:');

        return $this->render('copilot:views/index.php', compact('pages', 'home'));
    }

    public function settings() {

        $meta    = (object)$this->helper('yaml')->fromFile('site:content/_meta.yaml');
        $info    = json_decode($this->helper('fs')->read('copilot:module.json'));
        $license = $this->module('copilot')->getLicense();

        return $this->render('copilot:views/settings.php', compact('meta', 'info', 'license'));
    }

    public function page($path) {

        $path = $this->app->path(str_replace('/copilot/page/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $page     = copi::page($path);
        $type     = $this->getPageType($page);

        return $this->render('copilot:views/page.php', compact('page', 'type'));
    }

    public function pages($path) {

        $path = $this->app->path(str_replace('/copilot/pages/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $page = copi::page($path);
        $type = $this->getPageType($page);

        return $this->render('copilot:views/pages.php', compact('page', 'type'));
    }

    public function file($path) {

        $path = $this->app->path(str_replace('/copilot/file/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $file     = copi::resource($path);
        $page     = $file->page();
        $pagetype = $this->getPageType($page);

        return $this->render('copilot:views/file.php', compact('file', 'page', 'pagetype'));
    }

    public function files($path) {

        $path = $this->app->path(str_replace('/copilot/files/', 'site:', $this->app['route']));

        if (!$path) {
            return false;
        }

        $page = copi::page($path);

        return $this->render('copilot:views/files.php', compact('page'));
    }

    public function finder() {

        return $this->render('copilot:views/finder.php');
    }

    protected function getPageType($page) {

        $type     = $page->type();
        $typedef  = Type::definition($type);

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
