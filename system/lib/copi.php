<?php

/**
 *
 */
class copi {

    public static $app;
    public static $meta;

    /**
     * [init description]
     * @param  [type] $app [description]
     * @return [type]      [description]
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
     * [run description]
     * @return [type] [description]
     */
    public static function run() {
        return self::$app->trigger('copilot.init')->run();
    }

    /**
     * [__callStatic description]
     * @param  [type] $method [description]
     * @param  [type] $args   [description]
     * @return [type]         [description]
     */
    public static function __callStatic($method, $args) {
        return call_user_func_array([self::$app, $method], $args);
    }

    /**
     * [home description]
     * @return [type]       [description]
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
     * [page description]
     * @param  [type] $path [description]
     * @return [type]       [description]
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
     * [pages description]
     * @param  [type] $folder [description]
     * @return [type]         [description]
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
     * [snippet description]
     * @param  [type] $snippet [description]
     * @param  [type] $slots   [description]
     * @return [type]          [description]
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
     * [menu description]
     * @param  [type] $menu    [description]
     * @param  [type] $options [description]
     * @return [type]          [description]
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
     * [data description]
     * @param  [type] $store [description]
     * @return [type]        [description]
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
     * [view description]
     * @param  [type] $template [description]
     * @param  [type] $slots    [description]
     * @return [type]           [description]
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
     * [render_page description]
     * @param  [type] $view  [description]
     * @param  [type] $slots [description]
     * @return [type]        [description]
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
     * [render_page_route description]
     * @param  [type] $route [description]
     * @param  [type] $slots [description]
     * @return [type]        [description]
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
