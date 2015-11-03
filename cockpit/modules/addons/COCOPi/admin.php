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
                $types[$file->getBasename('.yaml')] = $this->helper('yaml')->fromFile($file->getRealPath());
            }
        }

        echo '<script>window.COCOPI_PAGE_TYPES = '.json_encode((object)$types).'</script>';
        echo $this->assets('cocopi:assets/js/cocopi.js');
    });

    // bind admin routes /cocopi/*
    $this->bindClass('cocopi\\Controller\\Update', 'cocopi/update');
    $this->bindClass('cocopi\\Controller\\Utils', 'cocopi/utils');
    $this->bindClass('cocopi\\Controller\\Admin', 'cocopi');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'COCOPi',
        'icon'  => 'paper-plane-o',
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
    $this->on("admin.dashboard.main", function() {

        $home  = copi::home();
        $pages = copi::pages('content:')->sorted();

        $this->renderView("cocopi:views/widgets/dashboard.php", compact('pages', 'home'));
    }, 100);
});
