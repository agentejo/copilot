<?php

namespace COCOPi\Controller;

use copi;

class Utils extends \Cockpit\AuthController {

    public function updateFile() {

        if ($file = $this->param('file')) {

            $f = copi::resource($file['path']);

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

            $p = copi::page($page['path']);

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

    public function createPage() {

        $root = $this->app->param('root');
        $meta = $this->app->param('meta', []);

        $root = ltrim($root, '/');

        $meta = array_merge([
            'title' => '',
            'slug'  => '',
            'type'  => 'html'
        ], $meta);

        if (!$meta['title']) {
            return false;
        }

        $meta['slug'] = strtolower(str_replace([' '], ['-'], $meta['slug'] ? $meta['slug'] : $meta['title']));

        $type     = $meta['type'];
        $typedef  = [];

        if ($path = copi::path("types:{$type}.yaml")) {
            $typedef = $this->app->helper('yaml')->fromFile($path);
        }

        $type = array_replace_recursive([
            'name' => $type,
            'ext' => 'html',
            'content' => [
                'visible' => true,
                'type'    => isset($typedef, $typedef['ext']) && $typedef['ext'] == 'md' ? 'markdown':'html'
            ],
            'meta' => []
        ], (array)$typedef);


        $contentfolder = copi::path('content:');
        $pagepath      = $contentfolder.($root=='home' ? '':  $root.'/'.$meta['slug']).'/'.'index.'.($type['ext']=='md' ? 'md':'html');
        $time          = date('Y-m-d H:i:s', time());

        $content = [
            "uid: ".uniqid('pid-'),
            "type: ".$meta['type'],
            "created: ".$time,
            "modified: ".$time,
            "title: ".$meta['title'],
            "===\n",
        ];

        $this->app->helper('fs')->write($pagepath, implode("\n", $content));

        $url = '/'.str_replace([copi::path('site:'), '//'], ['', '/'], $pagepath);

        return json_encode(['relpath' => $url]);
    }

    public function deletePage() {

        $path = $this->app->param('path');

        if ($page = copi::page($path)) {
            return json_encode(["success" => $page->delete()]);
        }

        return false;
    }

    public function getPageResources() {

        $path  = $this->app->param('path');
        $page  = copi::page($path);
        $res   = [];

        if ($page) {
            $res = $page->files()->sorted()->toArray();
        }

        return json_encode($res);
    }

    public function renameResource() {

        $path = $this->app->param('path');
        $name = $this->app->param('name');

        if ($res = copi::resource($path)) {
            return json_encode($res->rename($name)->toArray());
        }

        return false;
    }

    public function deleteResource() {

        $path = $this->app->param('path');

        if ($res = copi::resource($path)) {
            return json_encode(["success" => $res->delete()]);
        }

        return false;
    }
}
