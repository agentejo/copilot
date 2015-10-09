<?php

// ACL
$app("acl")->addResource("coco", ['manage.coco']);


$app->on('admin.init', function() use($app) {

    if (!$this->module('cockpit')->hasaccess('coco', ['manage.coco'])) {
        return;
    }

    // add coco js lib
    $this->on('app.layout.header', function(){

        // load only within coco module
        if(strpos($this['route'], '/coco') !== 0) return;

        // collect page types

        $types = [];

        foreach([
            copi::path('site:system/types'),
            copi::path('site:site/types'),
            copi::path('site:site/theme/types')
        ] as $fol) {

            if (!$fol) continue;

            foreach($this->helper('fs')->ls('*.yaml', $fol) as $file) {
                $types[$file->getBasename('.yaml')] = $this->helper('yaml')->fromFile($file->getRealPath());
            }
        }

        echo '<script>window.COPILOT_PAGE_TYPES = '.json_encode((object)$types).'</script>';

        echo $this->assets('coco:assets/js/coco.js');

    });

    $this->on('app.layout.contentbefore', function() {

        // load only within coco module
        if(strpos($this['route'], '/coco') !== 0) return;

        if (!$this->module('coco')->getLicense()) {
            $this->renderView('coco:views/partials/licensewarning.php');
        }
    });

    // bind admin routes /coco/*
    $this->bindClass('CoCo\\Controller\\Utils', 'coco/utils');
    $this->bindClass('CoCo\\Controller\\Admin', 'coco');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Copilot',
        'icon'  => 'paper-plane-o',
        'route' => '/coco',
        'active' => strpos($this['route'], '/coco') === 0
    ]);

    /**
     * listen to app search to filter content
     */
    $this->on('cockpit.search', function($search, $list) {


    });


    // dashboard widgets
    $this->on("admin.dashboard.aside", function() {
        //$this->renderView("coco:views/widgets/dashboard.php");
    }, 100);
});
