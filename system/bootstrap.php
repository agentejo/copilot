<?php

/**
 * app start time
 */
define('CP_START_TIME', microtime(true));

/**
 * dedicated to the good ol' php days
 * and all wp devs ;-)
 */
global $copilot;

/*
 * Register default autoloader
 * to load libs from system/vendor
 */
spl_autoload_register(function($class){

    $class = str_replace('\\', '/', $class).'.php';

    foreach(['cockpit/lib', 'system/lib', 'vendor'] as $folder) {

        $class_path = CP_ROOT_DIR."/{$folder}/".$class;

        if (file_exists($class_path)) {
            return include_once($class_path);
        }
    }
});

/*
 * Collect needed paths + routes
 */
$CP_ROOT_DIR    = str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__));
$CP_DOCS_ROOT   = str_replace(DIRECTORY_SEPARATOR, '/', isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : $CP_ROOT_DIR);

# make sure that $_SERVER['DOCUMENT_ROOT'] is set correctly
if (strpos($CP_ROOT_DIR, $CP_DOCS_ROOT)!==0 && isset($_SERVER['SCRIPT_NAME'])) {
    $CP_DOCS_ROOT = str_replace(dirname(str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['SCRIPT_NAME'])), '', $CP_ROOT_DIR);
}

$CP_BASE        = trim(str_replace($CP_DOCS_ROOT, '', $CP_ROOT_DIR), "/");
$CP_BASE_URL    = strlen($CP_BASE) ? "/{$CP_BASE}": $CP_BASE;
$CP_BASE_ROUTE  = $CP_BASE_URL; // "{$CP_BASE_URL}/index.php"

$CP_ROUTE       = str_replace([$CP_BASE_URL.'/index.php', $CP_BASE_URL], '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$CP_ROUTE       = trim($CP_ROUTE) == '' ? '/' : $CP_ROUTE;

/*
 * SYSTEM DEFINES
 */
define('COPILOT'         , true);
define('CP_ROOT_DIR'     , $CP_ROOT_DIR);
define('CP_DOCS_ROOT'    , $CP_DOCS_ROOT);
define('CP_BASE_URL'     , $CP_BASE_URL);
define('CP_BASE_ROUTE'   , $CP_BASE_ROUTE);
define('CP_CURRENT_ROUTE', $CP_ROUTE);
define('CP_TMP_DIR'      , $CP_ROOT_DIR.'/storage/tmp');

$CP_CONFIG = Spyc::YAMLLoad($CP_ROOT_DIR.'/site/config/config.yaml');

// include helper functions
include(__DIR__.'/functions.php');

/**
 * create Lime\App
 */
$copilot = new Lime\App(array_replace_recursive(
    include($CP_ROOT_DIR.'/system/config/config.php'),
    $CP_CONFIG
));

// set default timezone
date_default_timezone_set($copilot['timezone']);

// cache config for later access
$copilot["site.config"] = $CP_CONFIG;

// init static copilot helper
copi::init($copilot);

/**
 * register path namespaces
 */

# base
$copilot->path('site'    , CP_ROOT_DIR);
$copilot->path('docroot' , CP_DOCS_ROOT);
$copilot->path('storage' , CP_ROOT_DIR.'/storage');
$copilot->path('tmp'     , CP_TMP_DIR);
$copilot->path('content' , CP_ROOT_DIR.'/content');
$copilot->path('uploads' , CP_ROOT_DIR.'/storage/uploads');
$copilot->path('theme'   , CP_ROOT_DIR.'/site/theme');

# config
$copilot->path('config'  , CP_ROOT_DIR.'/system/config');
$copilot->path('config'  , CP_ROOT_DIR.'/site/config');

# assets
$copilot->path('assets'  , CP_ROOT_DIR.'/system/assets');
$copilot->path('assets'  , CP_ROOT_DIR.'/site/assets');

$copilot->path('modules' , CP_ROOT_DIR.'/system/modules');
$copilot->path('modules' , CP_ROOT_DIR.'/site/modules');

# snippets
$copilot->path('snippets', CP_ROOT_DIR.'/system/snippets');
$copilot->path('snippets', CP_ROOT_DIR.'/site/snippets');
$copilot->path('snippets', CP_ROOT_DIR.'/site/theme/snippets');

# types
$copilot->path('types'  , CP_ROOT_DIR.'/system/types');
$copilot->path('types'  , CP_ROOT_DIR.'/site/types');
$copilot->path('types'  , CP_ROOT_DIR.'/site/theme/types');

# layouts
$copilot->path('layouts' , CP_ROOT_DIR.'/system/layouts');
$copilot->path('layouts' , CP_ROOT_DIR.'/site/layouts');
$copilot->path('layouts' , CP_ROOT_DIR.'/site/theme/layouts');

# data
$copilot->path('data' , CP_ROOT_DIR.'/storage/data');

# set cache path
$copilot("cache")->setCachePath(CP_TMP_DIR);
$copilot("yaml")->setCachePath(CP_TMP_DIR);

/**
 * check for bootsraping Cockpit
 */
if (!function_exists('cockpit') && file_exists(CP_ROOT_DIR.'/cockpit')) {
    include_once(CP_ROOT_DIR.'/cockpit/bootstrap.php');
}

if (defined('COCKPIT_DIR')) {
    $copilot->path('cockpit', COCKPIT_DIR);
}

/**
 * register view macros
 */
$copilot->service('renderer', function() use($copilot) {

    $renderer = new \Lexy();

    $renderer->setCachePath(CP_TMP_DIR);

    $renderer->extend(function($content){

        $replace = [

            'extend'   => '<?php $extend(expr); ?>',
            'base'     => '<?php $app->base(expr); ?>',
            'route'    => '<?php $app->route(expr); ?>',
            'trigger'  => '<?php $app->trigger(expr); ?>',
            'assets'   => '<?php echo $app->assets(expr); ?>',
            'markdown' => '<?php echo $app->helper("markdown")->parse(expr); ?>',
            'start'    => '<?php $app->start(expr); ?>',
            'end'      => '<?php $app->end(expr); ?>',
            'block'    => '<?php $app->block(expr); ?>',
            'url'      => '<?php echo $app->pathToUrl(expr); ?>',

            'render'   => '<?php echo copi::view(expr); ?>',
            'menu'     => '<?php echo copi::menu(expr); ?>',
            'snippet'  => '<?php copi::snippet(expr); ?>',
            'load'     => '<?php copi::$meta->assets->append(expr); ?>',
        ];

        // add macros for cockpit api
        if (function_exists('cockpit')) {

            $replace['form']   = '<?php cockpit()->module("forms")->form(expr); ?>';
            $replace['region'] = '<?php echo cockpit()->module("regions")->render(expr); ?>';
        }

        $content = preg_replace_callback('/\B@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function($match) use($replace) {

            if (isset($match[3]) && trim($match[1]) && isset($replace[$match[1]])) {
                return str_replace('(expr)', $match[3], $replace[$match[1]]);
            }

            return $match[0];

        }, $content);

        return $content;
    });

    return $renderer;
});

/**
 * return page in offline mode
 */
if ($copilot['offline']) {

    $copilot->bind('/*', function() {

        return copi::view('theme:offline.html');
    });

    return;
}

/**
 * load modules
 */
$copilot->loadModules([CP_ROOT_DIR.'/system/modules', CP_ROOT_DIR.'/modules']);

/**
 * bootstrap site
 */
include(CP_ROOT_DIR.'/site/bootstrap.php');

// bootstrap from theme
if (file_exists(CP_ROOT_DIR.'/site/theme/bootstrap.php')) {
    include(CP_ROOT_DIR.'/site/theme/bootstrap.php');
}

/*
 * SYSTEM EVENTS
 */

// map content pages to route
$copilot->on('copilot.init', function() {

    $this->bind('/*', function() {

        return copi::render_page_route();
    });

}, 1000);

// handle error pages
$copilot->on('after', function() {

    /**
     * some system info
     */
    define('CP_END_TIME'     , microtime(true));
    define('CP_DURATION_TIME', CP_END_TIME - CP_START_TIME);
    define('CP_MEMORY_USAGE' , (memory_get_peak_usage(false)/1024/1024));

    /**
     * load error layouts if status is 500 or 404
     */
    switch ($this->response->status) {

        case 500: // system error

            if ($this['debug']) {

                if ($this->req_is('ajax')) {
                    $this->response->body = json_encode(['error' => json_decode($this->response->body, true)]);
                } else {
                    $this->layout         = false;
                    $this->response->body = $this->render("layouts:error/500-debug.php", ['error' => json_decode($this->response->body, true)]);
                }

            } else {

                if ($this->req_is('ajax')) {
                    $this->response->body = '{"error": "500", "message": "system error"}';
                } else {
                    $this->layout         = false;
                    $this->response->body = copi::view("theme:error/500.html");
                }
            }

            break;

        case 404: // route | file not found

            if ($this->req_is('ajax')) {
                $this->response->body = '{"error": "404", "message":"File not found"}';
            } else {
                $this->layout         = false;
                $this->response->body = copi::view("theme:error/404.html");
            }
            break;
    }

    /**
     * send some debug information
     * back to client (visible in the network panel)
     */
    if ($this['debug'] && !headers_sent()) {

        header('COPILOT_DURATION_TIME: '.CP_DURATION_TIME.'sec');
        header('COPILOT_MEMORY_USAGE: '.CP_MEMORY_USAGE.'mb');
    }

}, 100);
