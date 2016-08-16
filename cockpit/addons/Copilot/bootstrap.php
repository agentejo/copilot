<?php

if (!defined('COCOPI_FRONTEND')) {
    define('COCOPI_FRONTEND', false);
}

include(__DIR__.'/site/copi.php');

$this->module("copilot")->extend([

    '__call' => function($method = null, $args = []) {
        return call_user_func_array(['copi', $method], $args);
    },

    'find' => function() {
        return call_user_func_array(['copi', 'find'], func_get_args())->sort('$p->dir()')->toArray();
    },

    'pages' => function() {
        return call_user_func_array(['copi', 'pages'], func_get_args())->sorted()->toArray();
    },

    'getLicense' => function() {

        /**
         * Hello Code monkey ;-)
         *
         * Nothing really special here. A simple snippet to check the license code.
         * It's simple to hack. But please consider supporting this project by
         * buying a license instead. Be awesome.
         *
         * Anyway, have fun using Copilot!
         *
         * Greets
         * Artur
         *
         */

        $license = ['type' => 'trial'];
        $code    = (string)$this->app['copilot.license'];
        $data    = [];

        try {
            $data = (array)Firebase\JWT\JWT::decode($code, 'copilot', ['HS256']);
        } catch(Exception $e) {}

        if (isset($data['name'], $data['company'], $data['created'], $data['email'], $data['type'])) {
            $license = $data;
            $license['code'] = $code;
        }

        return (object)$license;
    }
]);

// REST
if (COCKPIT_REST) {

    $app->on('cockpit.rest.init', function($routes) {
        $routes['copilot'] = 'Copilot\\Controller\\RestApi';
    });
}

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) {

    copi::trigger('cockpit.bootstrap', [$this]);

    include_once(__DIR__.'/admin.php');
}
