<?php

namespace CoCo\Controller;

use copi;

class Utils extends \Cockpit\AuthController {

    public function updateFile() {

        if ($file = $this->param('file')) {

            $f = new \Copilot\Resource($file['path']);

            $f->rename($file['filename']);
            $f->updateMeta((array)$file['meta']);

            return $f->toArray();
        }

        return false;
    }

    public function updateSettings() {

        if ($meta = $this->param('settings', null)) {
            $this->helper('yaml')->toFile($this->app->path('site:content').'/_meta.yaml', $meta);
            return $meta;
        }

        return false;
    }

    public function updatePage() {

        $page    = $this->param('page', null);
        $updates = $this->param('updates', null);

        if ($page && $updates) {

            $p = new \Copilot\Page($page['path']);

            if (trim($updates['slug'])) {
                $p->setSlug(strtolower(str_replace(' ', '-', $updates['slug'])));
            }

            $p->setVisibility($page['visible']);

            if (isset($page['rawmeta']['description']) && !trim($page['rawmeta']['description'])) unset($page['rawmeta']['description']);
            if (isset($page['rawmeta']['keywords']) && !trim($page['rawmeta']['keywords'])) unset($page['rawmeta']['keywords']);
            if (isset($page['rawmeta']['author']) && !trim($page['rawmeta']['author'])) unset($page['rawmeta']['author']);

            if (!isset($page['rawmeta']['uid'])){
                $page['rawmeta']['uid'] = uniqid('pid-');
            }

            $page['rawmeta']['modified'] = date('Y-m-d H:i:s', time());

            $meta = $this->app->helper('yaml')->toYAML($page['rawmeta']);

            file_put_contents($p->path(), implode("\n===\n\n", [$meta, $page['rawcontent']]));

            return $p->relpath();
        }

        return false;
    }

    public function updateResourcesOrder() {

        if ($order = $this->param('order', false)) {

            foreach($order as $index=>$path) {
                if ($res = copi::resource($path)) {
                    $res->updateMeta(['sort' => $index]);
                }
            }
        }
        return $order;
    }

    public function updatePagesOrder() {

        if ($order = $this->param('order', false)) {

            foreach($order as $index=>$path) {
                if ($page = copi::page($path)) {
                    $page->updateMeta(['sort' => $index]);
                }
            }
        }
        return $order;
    }
}
