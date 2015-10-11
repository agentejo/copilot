<?php

/**
 *
 */
class copi {

    public static $app;
    public static $meta;

    /**
     * @param $app
     */
    public static function init($app) {

        self::$app = $app;

        // init meta container

        self::$meta = new \ContainerArray([
            'route'       => $app["route"],
            'site'        => $app,
            'title'       => $app["site.title"],
            'keywords'    => '',
            'author'      => '',
            'description' => '',
            'page'        => null,
            'assets'      => new \ArrayObject([]),
            'data'        => new \ContainerArray([])
        ]);

        $app["site:meta"]      = self::$meta;
        $app->viewvars['meta'] = self::$meta;
        $app->viewvars['site'] = $app;
        $app->viewvars['app']  = $app;
    }

    /**
     * @return mixed
     */
    public static function run() {
        return self::$app->trigger('copilot.init')->run();
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args) {
        return call_user_func_array([self::$app, $method], $args);
    }

    /**
     * @return mixed|null
     */
    public static function home() {

        $pages = [
            "content:index.html",
            "content:index.md",
            "content:_index.html",
            "content:_index.md",
        ];

        $page = null;

        foreach([
            "content:index.html",
            "content:index.md",
            "content:_index.html",
            "content:_index.md",
        ] as $p) {

            if ($page = self::$app->path($p)) {
                $page = \Copilot\Page::fromCache($page);
                break;
            }
        }

        return $page;
    }

    /**
     * @param $path
     * @return mixed|null
     */
    public static function page($path) {

        if (strpos($path, ':') === false && !self::$app->isAbsolutePath($path)) {
            $path = "content:{$path}";
        }

        if ($page = self::$app->path($path)) {

            $page = \Copilot\Page::fromCache($page);

            return $page;
        }

        return null;
    }


    /**
     * @param $folder
     * @return \Copilot\PageCollection
     */
    public static function pages($folder) {

        if (strpos($folder, ':') === false && !self::$app->isAbsolutePath($folder)) {
            $path = "content:{$folder}";
        } else {
            $path = $folder;
        }

        return \Copilot\PageCollection::fromFolder($path);
    }

    /**
     * @param $folder
     * @param null $criteria
     * @return \Copilot\PageCollection
     */
    public static function find($folder, $criteria = null) {

        if (strpos($folder, ':') === false && !self::$app->isAbsolutePath($folder)) {
            $path = "content:{$folder}";
        } else {
            $path = $folder;
        }

        return \Copilot\PageCollection::find($path, $criteria);
    }

    /**
     * @param $snippet
     * @param array $slots
     */
    public static function snippet($snippet, $slots = []) {

        if (strpos($snippet, ':') === false && !self::$app->isAbsolutePath($snippet)) {
            $path = "snippets:{$snippet}.html";
        }

        if ($file = self::$app->path($path)) {

            echo self::view($file, $slots);
        }
    }

    /**
     * @param $menu
     * @param array $options
     */
    public static function menu($menu, $options = []) {


        if (strpos($menu, ':') === false && !self::$app->isAbsolutePath($menu)) {
            $path = "config:menu/{$menu}.yaml";
        } else {
            $path = $menu;
        }


        if ($file = self::$app->path($path)) {

            $options = array_merge([
                "class" => ""
            ], $options);

            if ($data = self::$app->helper('yaml')->fromFile($file)) {

                return self::snippet('menu/default', ['data' => $data, 'options' => $options]);
            }
        }
    }

    /**
     * @param $store
     * @return array|DataCollection
     */
    public static function data($store) {

        static $cache_datastore;

        if (isset($cache_datastore[$store])) {
            return $cache_datastore[$store];
        }

        if (strpos($store, ':') === false && !self::$app->isAbsolutePath($store)) {
            $path = "data:{$store}.yaml";
        } else {
            $path = $store;
        }

        if ($file = self::$app->path($path)) {

            if (is_null($cache_datastore)) {
                $cache_datastore = [];
            }

            $cache_datastore[$store] = self::$app->helper('yaml')->fromFile($file);

            return DataCollection::create($cache_datastore[$store]);
        }

        return [];
    }

    /**
     * @param $template
     * @param array $slots
     * @return string
     */
    public static function view($template, $slots = []) {

        $renderer = self::$app->renderer;
        $slots    = array_merge(self::$app->viewvars, $slots);
        $layout   = false;

        if (strpos($template, ' with ') !== false ) {
            list($template, $layout) = explode(' with ', $template, 2);
        }

        if (strpos($template, ':') !== false && $file = self::$app->path($template)) {
            $template = $file;
        }

        $slots['extend'] = function($from) use(&$layout) {
            $layout = $from;
        };

        if (!file_exists($template)) {
            return "Couldn't resolve {$template}.";
        }

        $output = $renderer->file($template, $slots);

        if ($layout) {

            if (strpos($layout, ':') !== false && $file = self::$app->path($layout)) {
                $layout = $file;
            }

            if(!file_exists($layout)) {
                return "Couldn't resolve {$layout}.";
            }

            $slots["content_for_layout"] = $output;

            $output = $renderer->file($layout, $slots);
        }

        return $output;
    }

    /**
     * @param $view
     * @param array $slots
     * @return bool|string
     */
    public static function render_page($view, $slots = []) {

        $view = self::$app->path($view);
        $meta = self::$meta;
        $site = self::$app;

        // page not found
        if (!$view) {
            return false;
        }

        // page or one of its parents is inactive
        if (strpos(str_replace(CP_ROOT_DIR, '', $view), '/_') !== false) {
            return false;
        }

        $page        = new Copilot\Page($view);
        $meta->page  = $page;

        $meta->extend($meta->page->meta());
        $site->path('current', $page->dir());

        return $page->render($slots);
    }

    /**
     * @param null $route
     * @param array $slots
     * @return bool|string
     */
    public static function render_page_route($route = null, $slots = []) {

        $view  = false;
        $route = is_null($route) ? self::$app['route'] : $route;

        $route = str_replace('../', '', trim($route, '/'));
        $path  = self::$app->path('content:'.(strlen($route) ? $route : ''));

        // prevent direct access to files in the content folder
        if ($path && is_file($path)) {
            return false;
        }

        if ($path && is_dir($path)) {
            $path = rtrim($path, '/');
            $path = "{$path}/index";
        } else {
            $path = "content:{$route}";
        }

        foreach(['html','md'] as $ext) {
            if ($view = self::$app->path("{$path}.{$ext}")) break;
        }

        return self::render_page($view, $slots);
    }

}
