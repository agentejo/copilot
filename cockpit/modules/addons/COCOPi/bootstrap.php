<?php

if (!defined('COCOPI_FRONTEND')) {
    define('COCOPI_FRONTEND', false);
}

include(__DIR__.'/copi.php');
include(__DIR__.'/site/bootstrap.php');

$this->module("cocopi")->extend([


    'getLicense' => function() {

        /**
         * Hello Code monkey ;-)
         *
         * Nothing really special here. A simple snippet to check the license code.
         * It's simple to hack. But please consider supporting this project by
         * buying a license instead. Be awesome.
         *
         * Anyway, have fun using COCOPi!
         *
         * Greets
         * Artur
         *
         */

        $license = ['type' => 'trial'];
        $code    = (string)$this->app['cocopi.license'];
        $data    = [];

        try {
            $data = (array)JWT::decode($code, 'cocopi', ['HS256']);
        } catch(Exception $e) {}

        if (isset($data['name'], $data['company'], $data['created'], $data['email'], $data['type'])) {
            $license = $data;
            $license['code'] = $code;
        }

        return (object)$license;
    }
]);


// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) {

    copi::trigger('cockpit.bootstrap', [$this]);

    include_once(__DIR__.'/admin.php');
}
