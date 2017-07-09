<?php

// ACL
$app("acl")->addResource("copilot", ['manage.copilot']);


$app->on('admin.init', function() use($app) {

    if (!$this->module('cockpit')->hasaccess('copilot', ['manage.copilot'])) {
        return;
    }

    // add copilot js lib
    $this->on('app.layout.header', function(){

        // load only within copilot module
        if(strpos($this['route'], '/copilot') !== 0) return;

        // collect page types

        $types = [];

        foreach(copi::paths('types') as $folder) {

            if (!$folder) continue;

            foreach($this->helper('fs')->ls('*.yaml', $folder) as $file) {
                $type = $file->getBasename('.yaml');
                $types[$type] = $this->helper('yaml')->fromFile($file->getRealPath());

                if (isset($types[$type]['subtypes'])) {

                    foreach($types[$type]['subtypes'] as $subtype => $def) {
                        if (!isset($def['parents'])) {
                            $def['parents'] = $type;
                        }
                        $types["{$type}/{$subtype}"] = $def;
                    }
                }
            }
        }

        echo '<script>window.COPILOT_PAGE_TYPES = '.json_encode((object)$types).'</script>';
        echo $this->assets('copilot:assets/js/copilot.js');
    });

    // bind admin routes /copilot/*
    $this->bindClass('Copilot\\Controller\\Update', 'copilot/update');
    $this->bindClass('Copilot\\Controller\\Utils', 'copilot/utils');
    $this->bindClass('Copilot\\Controller\\Admin', 'copilot');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Copilot',
        'icon'  => 'copilot:icon.svg',
        'route' => '/copilot',
        'active' => strpos($this['route'], '/copilot') === 0
    ]);

    /**
     * listen to app search to filter pages
     */
    $this->on('cockpit.search', function($search, $list) {

        copi::find(function($page) use($search, $list) {

            if (stripos($page->meta('title', ''), $search) !== false) {

                $list[] = [
                    'icon'  => 'file-text-o',
                    'title' => $page->meta('title', $page->filename()),
                    'url'   => $this->routeUrl('/copilot/page'.$page->relpath())
                ];
            }
        });
    });


    // dashboard widgets
    $app->on("admin.dashboard.widgets", function($widgets) {

        $home    = copi::home();
        $pages   = copi::pages('content:')->sorted();
        $license = $this->module("copilot")->getLicense()->type;

        $widgets[] = [
            "name"   => "pages",
            "content" => $this->view("copilot:views/widgets/dashboard.php", compact('pages', 'home', 'license')),
            "area"    => 'main'
        ];

    }, 100);

    // load custom web components
    if (strpos($this['route'], '/copilot') === 0) {
        $app('admin')->data->components->append('copilot:assets/components/pagejumplist.html');
        $app('admin')->data->components->append('copilot:assets/layout-builder/field-htmlbuilder.html');
    }
});
