<?php

define('COPILOT_SYSTEM', $this->path('site:system/bootstrap.php'));

if (COPILOT_SYSTEM) {

    /**
     * bootstrap coco
     */
    include_once(COPILOT_SYSTEM);
}

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST && COPILOT_SYSTEM) {

    copi::trigger('cockpit.bootstrap', [$this]);

    include_once(__DIR__.'/admin.php');
}

if (!COPILOT_SYSTEM) return;


$this->module("coco")->extend([

    'createPage' => function($root, $meta) {

        $root = ltrim($root, '/');

        $meta = array_merge([
            'title' => '',
            'slug'  => '',
            'type'  => 'page'
        ], $meta);

        if (!$meta['title']) {
            return false;
        }

        $meta['slug'] = strtolower(str_replace([' '], ['-'], $meta['slug'] ? $meta['slug'] : $meta['title']));

        $contentfolder = copi::path('content:');
        $pagepath      = $contentfolder.($root=='home' ? '':  $root.'/'.$meta['slug']).'/'.'index.'.($meta['type']=='markdown' ? 'md':'html');
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

        $url = str_replace(copi::path('site:'),'',$pagepath);

        return $url;
    },

    'deletePage' => function($path) {

        if ($page = copi::page($path)) {
            return $page->delete();
        }

        return false;
    },

    'getPageResources' => function($path, $asarray = false) {

        $page = copi::page($path);

        if (!$page) {
            return [];
        }

        return $asarray ? $page->files()->toArray(): $page->files();
    },

    'getLicense' => function() {                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  $l1c3=null;$d474=null;try{$d474=(array)JWT::decode(strval($this->app[base64_decode('Y29jby5saWNlbnNl')]),base64_decode('Y29jbw=='),[base64_decode('SFMyNTY=')]);}catch(Exception $e){}if(isset($d474[base64_decode('bmFtZQ==')],$d474[base64_decode('Y29tcGFueQ==')],$d474[base64_decode('Y3JlYXRlZA==')],$d474[base64_decode('ZW1haWw=')],$d474[base64_decode('dHlwZQ==')])){$l1c3=$d474;}return $l1c3;

        if (file_exists(__DIR__.'/license.lic')) {
            return $this->app->decode(file_get_contents(__DIR__.'/license.lic'));
        }

        return false;
    }
]);
