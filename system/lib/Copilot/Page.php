<?php

namespace Copilot;

use copi;

/**
 *
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

    /**
     * [fromCache description]
     * @param  [type]
     * @return [type]
     */
    public static function fromCache($path) {

        if (!isset(self::$pagesCache[$path])) {
            self::$pagesCache[$path] = new self($path);
        }

        return self::$pagesCache[$path];
    }

    /**
     * [__construct description]
     * @param [type]
     */
    public function __construct($path) {

        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $this->path     = $path;
        $this->meta     = null;
        $this->contents = null;
        $this->content  = null;
        $this->parts    = null;
        $this->depth    = null;

        $this->_initPaths();

    }

    /**
     * [meta description]
     * @param  [type] $key     [description]
     * @param  [type] $default [description]
     * @return [type]          [description]
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
     * [set description]
     * @param [type] $key   [description]
     * @param [type] $value [description]
     */
    public function set($key, $value) {

        $this->meta->extend([$key => $value]);

        return $this;
    }

    /**
     * [path description]
     * @return [type] [description]
     */
    public function path() {
        return $this->path;
    }

    /**
     * [contentpath description]
     * @return [type] [description]
     */
    public function contentpath() {
        return $this->contentpath;
    }

    /**
     * [relpath description]
     * @return [type] [description]
     */
    public function relpath() {
        return $this->relpath;
    }

    /**
     * [slug description]
     * @return [type] [description]
     */
    public function slug() {
        return $this->slug;
    }

    /**
     *
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
     * [ext description]
     * @return [type]
     */
    public function ext() {
        return $this->ext;
    }

    /**
     * [filename description]
     * @return [type]
     */
    public function filename() {
        return $this->filename;
    }

    /**
     * [dir description]
     * @return [type]
     */
    public function dir() {
        return $this->dir;
    }

    /**
     * [contentdir description]
     * @return [type]
     */
    public function contentdir() {
        return $this->contentdir;
    }

    /**
     * [url description]
     * @return [type]
     */
    public function url() {
        return $this->url;
    }

    /**
     * [permalink description]
     * @return [type] [description]
     */
    public function permalink() {
        return copi::$app->getSiteUrl(false).$this->url();
    }

    /**
     * [isIndex description]
     * @return boolean
     */
    public function isIndex() {
        return ($this->basename == 'index');
    }

    /**
     * [isRootIndex description]
     * @return boolean [description]
     */
    public function isRootIndex() {
        // @TODO make more pretty
        $content  = copi::$app->path('content:');
        return in_array($this->dir.'/'.$this->basename, [$content.'index', $content.'_index']);
    }

    /**
     * [isVisible description]
     * @return boolean
     */
    public function isVisible() {
        return (substr($this->isIndex() && !$this->isRootIndex()  ? basename($this->dir):$this->filename, 0, 1) !== '_');
    }

    /**
     * [isHidden description]
     * @return boolean
     */
    public function isHidden() {
        return !$this->isVisible();
    }

    /**
     *
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
     * [parents description]
     * @return [type]
     */
    public function parents() {

        $array = [];
        $page  = $this;

        while($page = $page->parent()) {
            $array[] = $page;
        }

        $pages = new PageCollection($array);

        return $pages;
    }

    /**
     * [parent description]
     * @return [type]
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
     * [children description]
     * @return [type]
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
     * [siblings description]
     * @return [type]
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
     * [pages description]
     * @param  [type] $path [description]
     * @return [type]       [description]
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
     * [page description]
     * @param  [type] $path [description]
     * @return [type]       [description]
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
     * [depth description]
     * @return [type]
     */
    public function depth() {

        if (is_null($this->depth)) {
            $this->depth = count(explode('/', str_replace(CP_ROOT_DIR.'/content', '', $this->dir))) - ($this->isIndex() ? 2 : 1);
        }

        return $this->depth;
    }

    /**
     * [data description]
     * @param  [type] $store [description]
     * @return [type]        [description]
     */
    public function data($store) {

        $store = $this->dir."/{$store}.yaml";

        return copi::data($store);
    }

    /**
     * [file description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function file($path) {

        $res  = new Resource($this->_getPath($path));

        return $res;
    }

    /**
     * [files description]
     * @param  [type] $path [description]
     * @return [type]       [description]
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
     * [image description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function image($path) {

        $img = $this->file($path);

        return ($img->exists() && $img->isImage()) ? $img : null;
    }

    /**
     * [images description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function images($path) {

        return $this->files($path)->filter('$item->exists() && $item->isImage()');
    }

    /**
     * [modified description]
     * @param  [type] $format [description]
     * @return [type]         [description]
     */
    public function modified($format = null) {

        $timestamp = filemtime($this->path);

        return $format ? date($format, $timestamp) : $timestamp;
    }

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
     * [content description]
     * @param  [type] $part [description]
     * @return [type]       [description]
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
     * [setContent description]
     * @param [type] $content [description]
     */
    public function setContent($content) {
        $this->content = $content;
    }

    public function getType() {
        return $this->meta('type', 'page');
    }

    public function getLayout() {

        $type = $this->getType();

        $typedefinition = [];

        if ($typepath = copi::path("types:{$type}.yaml")) {
            $typedefinition = copi::$app->helper('yaml')->fromFile($typepath);
        }

        $typedefinition = array_merge(['layout'=>'raw'], $typedefinition);
        $layout         = $this->meta('layout', $typedefinition['layout']);

        return $layout;
    }

    /**
     * [render description]
     * @param  [type] $slots [description]
     * @return [type]        [description]
     */
    public function render($slots = []) {

        $content = $this->content(null, $slots);
        $layout  = $this->getLayout();

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
     * [parts description]
     * @param  [type] $name [description]
     * @return [type]       [description]
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
     *
     */
    public function delete() {
        return copi::$app->helper('fs')->delete($this->isIndex() && !$this->isRootIndex() ? $this->dir : $this->path);
    }


    /**
     * [_meta description]
     * @return [type] [description]
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
     * [_collectMeta description]
     * @return [type]
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
     * [_getPath description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    protected function _getPath($path) {

        return (strpos($path, ':') !== false) ? copi::$app->path($path) : $this->dir.'/'.trim($path, '/');
    }

    /**
     * [_initPaths description]
     * @return [type] [description]
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
     * [__toString description]
     * @return string [description]
     */
    public function __toString() {
        return $this->content();
    }

    /**
    * [__toJSON description]
    * @return string [description]
     */
    public function toJSON(){
        return json_encode($this->jsonSerialize());
    }

    /**
     * [toArray description]
     * @return array [description]
     */
    public function toArray() {

        $this->meta();
        $this->depth();

        $array = get_object_vars($this);

        $array['type']       = $this->getType();
        $array['visible']    = $this->isVisible();
        $array['children']   = $this->children()->count();
        $array['isRoot']     = $this->isRootIndex();
        $array['rawcontent'] = $this->rawcontent();
        $array['rawmeta']    = $this->rawmeta();

        return $array;
    }
}
