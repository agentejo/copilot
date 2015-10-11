<?php

namespace Copilot;

use copi;

/**
 * Class Page
 * @package Copilot
 */
class Page {

    const CONTENT_MARKER = "\n===";

    protected static $metaCache  = [];
    protected static $pagesCache = [];

    protected $meta;
    protected $path;
    protected $contentpath;
    protected $relpath;
    protected $dir;
    protected $contentdir;
    protected $filename;
    protected $basename;
    protected $ext;
    protected $contents;
    protected $content;
    protected $url;
    protected $absUrl;
    protected $slug;
    protected $parts;

    protected $depth;
    protected $files;
    protected $layout;

    /**
     * @param $path
     * @return mixed
     */
    public static function fromCache($path) {

        if (!isset(self::$pagesCache[$path])) {
            self::$pagesCache[$path] = new self($path);
        }

        return self::$pagesCache[$path];
    }

    /**
     * @param $path
     */
    public function __construct($path) {

        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $this->path     = $path;
        $this->meta     = null;
        $this->contents = null;
        $this->content  = null;
        $this->parts    = null;
        $this->depth    = null;
        $this->layout   = null;

        $this->_initPaths();

    }

    /**
     * @param null $key
     * @param null $default
     * @return array|\ContainerArray|null
     */
    public function meta($key=null, $default=null) {

        if (!$this->meta) {
            $this->meta = $this->_meta();
        }

        if ($key) {
            return $this->meta->get($key, $default);
        }

        return $this->meta;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value) {

        $this->meta->extend([$key => $value]);

        return $this;
    }

    /**
     * @return mixed
     */
    public function path() {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function contentpath() {
        return $this->contentpath;
    }

    /**
     * @return mixed
     */
    public function relpath() {
        return $this->relpath;
    }

    /**
     * @return mixed
     */
    public function slug() {
        return $this->slug;
    }

    /**
     * @param $slug
     * @return $this
     */
    public function setSlug($slug) {

        if ($slug == $this->slug) {
            return $this;
        }

        if ($this->isIndex()) {

            $_dirname = dirname($this->dir);
            $_slugdir = ($this->isHidden() ? '_':'').$slug;

            copi::$app->helper('fs')->rename($this->dir, $_dirname.'/'.$_slugdir);
            $this->dir = $_dirname.'/'.$_slugdir;

        } else {

            $this->filename = ($this->isHidden() ? '_':'').$slug.'.'.$this->ext;
            copi::$app->helper('fs')->rename($this->path, $this->dir.'/'.$this->filename);

        }

        $this->path = $this->dir.'/'.$this->filename;

        $this->_initPaths();

        return $this;
    }

    /**
     * @return mixed
     */
    public function ext() {
        return $this->ext;
    }

    /**
     * @return mixed
     */
    public function filename() {
        return $this->filename;
    }

    /**
     * @return mixed
     */
    public function dir() {
        return $this->dir;
    }

    /**
     * @return mixed
     */
    public function contentdir() {
        return $this->contentdir;
    }

    /**
     * @return mixed
     */
    public function url() {
        return $this->url;
    }

    /**
     * @return string
     */
    public function permalink() {
        return copi::$app->getSiteUrl(false).$this->url();
    }

    /**
     * @return bool
     */
    public function isIndex() {
        return ($this->basename == 'index');
    }

    /**
     * @return bool
     */
    public function isRootIndex() {
        // @TODO make more pretty
        $content  = copi::$app->path('content:');
        return in_array($this->dir.'/'.$this->basename, [$content.'index', $content.'_index']);
    }

    /**
     * @return bool
     */
    public function isVisible() {
        return (substr($this->isIndex() && !$this->isRootIndex()  ? basename($this->dir):$this->filename, 0, 1) !== '_');
    }

    /**
     * @return bool
     */
    public function isHidden() {
        return !$this->isVisible();
    }

    /**
     * @param $visible
     * @return $this
     */
    public function setVisibility($visible) {

        $_dirname = dirname($this->dir);

        if ($visible && $this->isHidden()) {

            if ($this->isIndex() && !$this->isRootIndex()) {

                copi::$app->helper('fs')->rename($this->dir, $_dirname.'/'.substr(basename($this->dir), 1));
                $this->dir = $_dirname.'/'.substr(basename($this->dir), 1);

            } else {

                $this->filename = substr($this->filename, 1);
                copi::$app->helper('fs')->rename($this->path, $this->dir.'/'.$this->filename);
            }


        } elseif (!$visible && $this->isVisible()) {

            if ($this->isIndex() && !$this->isRootIndex()) {

                copi::$app->helper('fs')->rename($this->dir, $_dirname.'/_'.basename($this->dir));
                $this->dir = $_dirname.'/_'.basename($this->dir);

            } else {

                $this->filename = '_'.$this->filename;
                copi::$app->helper('fs')->rename($this->path, $this->dir.'/'.$this->filename);
            }
        }

        $this->path = $this->dir.'/'.$this->filename;

        $this->_initPaths();

        return $this;

    }

    /**
     * @param $reversed
     * @return PageCollection
     */
    public function parents($reversed = false) {

        $array = [];
        $page  = $this;

        while($page = $page->parent()) {
            $array[] = $page;
        }

        $pages = new PageCollection($reversed ? array_reverse($array):$array);

        return $pages;
    }

    /**
     * @return mixed|null
     */
    public function parent() {

        $page      = null;
        $indexfile = ($this->isIndex() ? dirname($this->dir) : $this->dir).'/index';

        if (file_exists("{$indexfile}.html")) {

            $page = self::fromCache("{$indexfile}.html");

        } elseif(file_exists("{$indexfile}.md")) {

            $page = self::fromCache("{$indexfile}.md");
        }

        return $page;
    }

    /**
     * @return $this|PageCollection
     */
    public function children() {

        if ($this->isIndex())  {

            $collection = PageCollection::fromFolder($this->dir)->not($this);

        } else {

            $collection = new PageCollection([]);
        }

        return $collection;
    }

    /**
     * @param null $filter
     * @return $this|PageCollection
     */
    public function siblings($filter = null) {

        if ($this->isIndex())  {

            if ($this->isRootIndex()) {

                $collection = new PageCollection([]);

            } else {

                $collection = PageCollection::fromFolder(dirname($this->dir))->not($this);
            }

        } else {

            $collection = PageCollection::fromFolder($this->dir)->not($this);

            if (!$this->isIndex()) {
                $collection = $collection->not($this->parent());
            }
        }

        // apply filter
        if ($filter && $collection->count()) {
            $collection = $collection->filter($filter);
        }

        return $collection;
    }

    /**
     * @param string $path
     * @return PageCollection
     */
    public function pages($path = '') {

        $dir = false;

        if (strpos($path, ':') !== false) {

            $dir = copi::$app->path($path);

        } else {

            $dir = $this->dir.'/'.trim($path, '/');
        }

        $pages = copi::pages($dir);

        return $pages;
    }

    /**
     * @param $path
     * @return mixed|null
     */
    public function page($path) {

        if (strpos($path, ':') !== false) {

            $path = copi::path($path);

        } else {

            if ($this->isIndex())  {
                $path = dirname($this->dir).'/'.trim($path, '/');
            } else {
                $path = $this->dir.'/'.trim($path, '/');
            }
        }

        return copi::page($path);
    }

    /**
     * @return int|null
     */
    public function depth() {

        if (is_null($this->depth)) {
            $this->depth = count(explode('/', str_replace(CP_ROOT_DIR.'/content', '', $this->dir))) - ($this->isIndex() ? 2 : 1);
        }

        return $this->depth;
    }

    /**
     * @param $store
     * @return array|\DataCollection
     */
    public function data($store) {

        $store = $this->dir."/{$store}.yaml";

        return copi::data($store);
    }

    /**
     * @param $path
     * @return Resource
     */
    public function file($path) {

        $res  = new Resource($this->_getPath($path));

        return $res;
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function files($path = '/') {

        if (!isset($this->files[$path])) {

            $files = [];

            foreach(copi::$app->helper('fs')->ls($this->_getPath($path)) as $file) {

                if ($file->isFile() && substr($file->getFilename(), 0, 1) !== '.' && !in_array($file->getExtension(), ['md', 'html', 'yaml'])) {
                    $files[] = new Resource($file->getRealPath());
                }
            }

            $this->files[$path] = new \DataCollection($files);
        }

        return $this->files[$path];
    }

    /**
     * @param $path
     * @return null|Resource
     */
    public function image($path) {

        $img = $this->file($path);

        return ($img->exists() && $img->isImage()) ? $img : null;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function images($path = '/') {

        return $this->files($path)->filter('$item->exists() && $item->isImage()');
    }

    /**
     * @param null $format
     * @return bool|int|string
     */
    public function modified($format = null) {

        $timestamp = filemtime($this->path);

        return $format ? date($format, $timestamp) : $timestamp;
    }

    /**
     * @param bool|true $parse
     * @return \ContainerArray|string
     */
    public function rawmeta($parse = true) {

        $content = $this->contents();
        $meta    = '';

        if ($dividerpos = strpos($content, self::CONTENT_MARKER)) {

            $meta = substr($content, 0, strpos($content, self::CONTENT_MARKER));
        }

        if ($parse) {
            $meta = copi::$app->helper('yaml')->fromString($meta);
            $meta = new \ContainerArray($meta);
        }

        return $meta;
    }

    /**
     * @return string
     */
    public function rawcontent() {

        $content = $this->contents();

        if ($dividerpos = strpos($content, self::CONTENT_MARKER)) {

            $content = substr($content, strpos($content, self::CONTENT_MARKER) + strlen(self::CONTENT_MARKER));
        }

        return trim($content);
    }

    public function contents() {

        if (is_null($this->contents)) {
            $this->contents = file_get_contents($this->path);
        }

        return $this->contents;
    }

    /**
     * @param null $part
     * @param array $slots
     * @return array|null
     */
    public function content($part = null, $slots = []) {

        if (is_null($this->content)) {

            $this->content = '';
            $content       = $this->contents();
            $content       = copi::view($this->path, array_merge(['page' => $this], $slots));

            if ($dividerpos = strpos($content, self::CONTENT_MARKER)) {

                $content = substr($content, strpos($content, self::CONTENT_MARKER) + strlen(self::CONTENT_MARKER));
            }

            if ($this->ext == 'md') {
                $content = copi::$app->helper('markdown')->parse($content);
            }

            // try to fix relative urls
            $this->content = copi::helper('utils')->fixRelativeUrls($content, $this->absUrl.'/');

            copi::$app->trigger('copilot.page.content', [$this]);
        }

        return $part ? $this->parts($part) : $this->content;
    }

    /**
     * @param $content
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function type() {
        return $this->meta('type', 'html');
    }

    /**
     * @return string
     */
    public function layout() {

        if (!$this->layout) {

            $type    = $this->type();
            $typedef = [];

            if ($typepath = copi::path("types:{$type}.yaml")) {
                $typedef = copi::$app->helper('yaml')->fromFile($typepath);
            }

            $typedef       = array_merge(['layout'=>'raw'], $typedef);
            $this->layout  = $this->meta('layout', $typedef['layout']);
        }

        return $this->layout;
    }

    /**
     * @param array $slots
     * @return string
     */
    public function render($slots = []) {

        $content = $this->content(null, $slots);
        $layout  = $this->layout();

        if ($layout) {

            $slots['page']         = $this;
            $slots['page_content'] = $content;

            if (strpos($layout, ':') !== false) {

                $layout = copi::$app->path($layout);

            } elseif (!copi::$app->isAbsolutePath($layout)) {

                $layout = "layouts:{$layout}.html";
            }

            $content = copi::view($layout, $slots);
        }

        // try to fix relative urls
        $content = copi::helper('utils')->fixRelativeUrls($content, $this->absUrl.'/');

        copi::trigger('copilot.page.render', [&$content]);

        return $content;
    }

    /**
     * @param null $name
     * @return array|null
     */
    public function parts($name = null) {

        if (is_null($this->parts)) {

            $content = $this->content();
            $parts   = ['content' => []];
            $current = 'content';

            foreach(explode("\n", $content) as $line) {

                $cline = trim($line);

                // start new part
                if (strpos($cline, '<!-- part:')===0) {
                    $current = trim(str_replace(['<!-- part:', '-->'], '', $cline));
                    $parts[$current] = [];
                    continue;
                }

                $parts[$current][] = $line;
            }

            // glue up lines
            foreach ($parts as $key => &$content) {
                $parts[$key] = implode("\n", $content);
            }

            $this->parts = $parts;
        }

        if ($name) {
            return isset($this->parts[$name]) ? $this->parts[$name] : null;
        }

        return $this->parts;
    }

    /**
     * @return mixed
     */
    public function delete() {
        return copi::$app->helper('fs')->delete($this->isIndex() && !$this->isRootIndex() ? $this->dir : $this->path);
    }


    /**
     * @return array|\ContainerArray
     */
    protected function _meta(){

        $meta = $this->_collectMeta();
        $code = file_get_contents($this->path);

        if ($dividerpos = strpos($code, self::CONTENT_MARKER)) {
            $code = substr($code, 0, $dividerpos);
        }

        if ($code) {
            $meta = array_merge($meta, copi::$app->helper('yaml')->fromString($code));
        }

        $meta = new \ContainerArray($meta);

        return $meta;
    }

    /**
     * @return array
     */
    protected function _collectMeta() {

        $meta = [];

        $dir  = $this->dir;

        while ($dir != CP_ROOT_DIR) {

            $metafile = "{$dir}/_meta.yaml";

            if (!isset(self::$metaCache[$metafile])) {

                self::$metaCache[$metafile] = file_exists($metafile) ? copi::$app->helper('yaml')->fromFile($metafile) : false;
            }

            if (self::$metaCache[$metafile]) {
                $meta = array_merge(self::$metaCache[$metafile], $meta);
            }

            $dir = dirname($dir);
        }

        return $meta;
    }

    /**
     * @param $path
     * @return string
     */
    protected function _getPath($path) {
        return (strpos($path, ':') !== false) ? copi::$app->path($path) : $this->dir.'/'.trim($path, '/');
    }

    /**
     *
     */
    protected function _initPaths() {

        $contentpath = copi::$app->path('content:');

        $this->ext         = pathinfo($this->path, \PATHINFO_EXTENSION);
        $this->dir         = dirname($this->path);
        $this->contentdir  = str_replace($contentpath, '/', $this->dir);
        $this->contentpath = str_replace($contentpath, '/', $this->path);
        $this->filename    = basename($this->path);
        $this->basename    = basename($this->path, '.'.$this->ext);
        $this->absUrl      = copi::pathToUrl($this->dir);
        $this->relpath     = str_replace(CP_ROOT_DIR, '', $this->path);
        $this->slug        = preg_replace('/^_/', '', ($this->basename == 'index') ? basename($this->dir) : $this->basename);
        $this->files       = []; // files cache

        $url = str_replace($contentpath, '/', $this->path);
        $url = copi::$app->routeUrl($url);
        $url = str_replace($this->filename, ($this->isIndex() ? '' : $this->basename), $url);

        $this->url = str_replace('/_', '/', $url);
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->content();
    }

    /**
     * @return string
     */
    public function toJSON(){
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array
     */
    public function toArray() {

        $this->meta();
        $this->depth();

        $array = get_object_vars($this);

        $array['type']       = $this->type();
        $array['visible']    = $this->isVisible();
        $array['children']   = $this->children()->count();
        $array['isRoot']     = $this->isRootIndex();
        $array['rawcontent'] = $this->rawcontent();
        $array['rawmeta']    = $this->rawmeta();
        $array['layout']     = $this->layout();

        return $array;
    }
}
