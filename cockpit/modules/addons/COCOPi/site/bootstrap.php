<?php

/**
 * app start time
 */
define('CP_START_TIME', microtime(true));

/**
 * dedicated to the good ol' php days
 * and all wp devs ;-)
 */
global $site;

$CP_CONFIG   = $app->retrieve('cocopi', []);
$CP_ROUTE    = '/';
$CP_BASE_URL = dirname(COCKPIT_BASE_URL);

if (COCOPI_FRONTEND) {
    $CP_ROUTE = str_replace([$CP_BASE_URL.'/index.php', $CP_BASE_URL], '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $CP_ROUTE = trim($CP_ROUTE) == '' ? '/' : $CP_ROUTE;
}

/*
 * SYSTEM DEFINES
 */
 define('CP_ROOT_DIR'     , dirname(COCKPIT_DIR));
 define('CP_TMP_DIR'      , $app->path('#tmp:'));
 define('CP_DOCS_ROOT'    , COCKPIT_DOCS_ROOT);
 define('CP_CONTENT_DIR'  , CP_ROOT_DIR.'/content');
 define('CP_SITE_DIR'     , CP_ROOT_DIR.'/site');
 define('CP_BASE_URL'     , $CP_BASE_URL);
 define('CP_BASE_ROUTE'   , CP_BASE_URL);
 define('CP_CURRENT_ROUTE', $CP_ROUTE);

// include helper functions
include(__DIR__.'/functions.php');

/**
 * create Lime\App
 */
 $site = new Lime\App(array_replace_recursive([
     // enable debug mode by default on local dev env:
     'debug'        => $app['debug'],
     'offline'      => false,
     'app.name'     => 'cocopi',
     'site.title'   => 'COCOPi',
     'base_url'     => CP_BASE_URL,
     'base_route'   => CP_BASE_ROUTE,
     'docs_root'    => CP_DOCS_ROOT,
     'route'        => CP_CURRENT_ROUTE,
     'session.name' => $app['session.name'],
     'sec-key'      => $app['sec-key'],
     'helpers'      => [
         "acl"      => "Lime\\Helper\\SimpleAcl",
         "assets"   => "Lime\\Helper\\Assets",
         "fs"       => "Lime\\Helper\\Filesystem",
         "image"    => "Lime\\Helper\\Image",
         "i18n"     => "Lime\\Helper\\I18n",
         "utils"    => "Lime\\Helper\\Utils",
         "coockie"  => "Lime\\Helper\\Cookie",
         "yaml"     => "Lime\\Helper\\YAML",
         "markdown" => "Lime\\Helper\\Markdown",
     ]
 ], $CP_CONFIG));


// cache config for later access
$site["site.config"] = $CP_CONFIG;

// init static site helper
copi::init($site);

/**
 * register path namespaces
 */

# base
$site->path('site'    , CP_ROOT_DIR);
$site->path('docroot' , CP_DOCS_ROOT);
$site->path('tmp'     , CP_TMP_DIR);
$site->path('content' , CP_CONTENT_DIR);
$site->path('theme'   , CP_SITE_DIR.'/theme');

# menu
$site->path('menu'    , CP_SITE_DIR.'/menu');

# assets
$site->path('assets'  , __DIR__.'/assets');
$site->path('assets'  , CP_SITE_DIR.'/assets');

# modules
$site->path('modules' , CP_SITE_DIR.'/modules');

# snippets
$site->path('snippets', __DIR__.'/snippets');
$site->path('snippets', CP_SITE_DIR.'/snippets');
$site->path('snippets', CP_SITE_DIR.'/theme/snippets');

# types
$site->path('types'   , __DIR__.'/types');
$site->path('types'   , CP_SITE_DIR.'/types');
$site->path('types'   , CP_SITE_DIR.'/theme/types');

# layouts
$site->path('layouts' , __DIR__.'/layouts');
$site->path('layouts' , CP_SITE_DIR.'/layouts');
$site->path('layouts' , CP_SITE_DIR.'/theme/layouts');

# cockpit
$site->path('cockpit', COCKPIT_DIR);

# set cache path
$site("cache")->setCachePath(CP_TMP_DIR);
$site("yaml")->setCachePath(CP_TMP_DIR);

/**
 * register view macros
 */
$site->service('renderer', function() use($site) {

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

            $replace['form']   = '<?php cockpit("forms")->form(expr); ?>';
            $replace['region'] = '<?php echo cockpit("regions")->render(expr); ?>';
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
if ($site['offline']) {

    $site->bind('/*', function() {

        return copi::view('theme:offline.html');
    });

    return;
}

/**
 * load modules
 */
$site->loadModules([CP_ROOT_DIR.'/site/modules']);

/**
 * bootstrap site
 */
include(CP_ROOT_DIR.'/site/bootstrap.php');

// bootstrap from theme
if (file_exists(CP_ROOT_DIR.'/site/theme/bootstrap.php')) {
    include(CP_ROOT_DIR.'/site/theme/bootstrap.php');
}


if (!COCOPI_FRONTEND) {
    return;
}

// set default timezone
date_default_timezone_set('UTC');

/*
 * SYSTEM EVENTS
 */

// map content pages to route
$site->on('site.init', function() {

    $this->bind('/*', function() {

        return copi::render_page_route();
    });

}, 1000);

// handle error pages
$site->on('after', function() {

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

        header('SITE_DURATION_TIME: '.CP_DURATION_TIME.'sec');
        header('SITE_MEMORY_USAGE: '.CP_MEMORY_USAGE.'mb');
    }

}, 100);
