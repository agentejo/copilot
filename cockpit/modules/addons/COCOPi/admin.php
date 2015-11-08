<?php

// ACL
$app("acl")->addResource("cocopi", ['manage.cocopi']);


$app->on('admin.init', function() use($app) {

    if (!$this->module('cockpit')->hasaccess('cocopi', ['manage.cocopi'])) {
        return;
    }

    // add cocopi js lib
    $this->on('app.layout.header', function(){

        // load only within cocopi module
        if(strpos($this['route'], '/cocopi') !== 0) return;

        // collect page types

        $types = [];

        foreach([
            __DIR__.'/site/types',
            copi::path('site:site/types'),
            copi::path('site:site/theme/types')
        ] as $fol) {

            if (!$fol) continue;

            foreach($this->helper('fs')->ls('*.yaml', $fol) as $file) {
                $type = $file->getBasename('.yaml');
                $types[$type] = $this->helper('yaml')->fromFile($file->getRealPath());

                if (isset($types[$type]['subtypes'])) {

                    foreach($types[$type]['subtypes'] as $subtype => $def) {
                        $def['parents'] = $type;
                        $types["{$type}/{$subtype}"] = $def;
                    }
                }
            }
        }

        echo '<script>window.COCOPI_PAGE_TYPES = '.json_encode((object)$types).'</script>';
        echo $this->assets('cocopi:assets/js/cocopi.js');
    });

    // bind admin routes /cocopi/*
    $this->bindClass('COCOPi\\Controller\\Update', 'cocopi/update');
    $this->bindClass('COCOPi\\Controller\\Utils', 'cocopi/utils');
    $this->bindClass('COCOPi\\Controller\\Admin', 'cocopi');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Pages',
        'icon'  => 'clone',
        'route' => '/cocopi',
        'active' => strpos($this['route'], '/cocopi') === 0
    ]);

    /**
     * listen to app search to filter pages
     */
    $this->on('cockpit.search', function($search, $list) {

        copi::find(null, function($page) use($search, $list) {

            if (stripos($page->meta('title', ''), $search) !== false) {

                $list[] = [
                    'icon'  => 'file-text-o',
                    'title' => $page->meta('title', $page->filename()),
                    'url'   => $this->routeUrl('/cocopi/page'.$page->relpath())
                ];
            }
        });
    });


    // dashboard widgets
    $app->on("admin.dashboard.widgets", function($widgets) {

        $home  = copi::home();
        $pages = copi::pages('content:')->sorted();

        $widgets[] = [
            "name"   => "pages",
            "content" => $this->view("cocopi:views/widgets/dashboard.php", compact('pages', 'home')),
            "area"    => 'main'
        ];

    }, 100);
});
