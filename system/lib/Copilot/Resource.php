<?php

namespace Copilot;

use copi;

/**
 *
 */
class Resource {

    protected $path;
    protected $relpath;
    protected $url;
    protected $dir;
    protected $filename;
    protected $ext;
    protected $exists;
    protected $mime;
    protected $meta;

    /**
     * [__construct description]
     * @param [type] $path [description]
     */
    public function __construct($path) {

        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $this->path = $path;
        $this->meta = null;

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

            $meta = [];

            if (file_exists($this->path.'.yaml')) {
                $meta = copi::$app->helper('yaml')->fromFile($this->path.'.yaml');
            }

            $this->meta = new \ContainerArray($meta);
        }

        if ($key) {
            return $this->meta->get($key, $default);
        }

        return $this->meta;
    }

    /**
     *
     */
    public function updateMeta($data, $extend = true) {

        $meta = $extend ? $this->meta()->extend($data) : new \ContainerArray($data);

        $this->meta = $meta;

        copi::$app->helper('yaml')->toFile($this->path.'.yaml', $this->meta->toArray());

        return $this;
    }

    /**
     * [path description]
     * @return [type]
     */
    public function path() {
        return $this->path;
    }

    /**
     * [relpath description]
     * @return [type] [description]
     */
    public function relpath() {
        return $this->relpath;
    }

    /**
     * [mime description]
     * @return [type]
     */
    public function mime() {
        return $this->mime;
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
     * [exists description]
     * @return [type] [description]
     */
    public function exists() {
        return $this->exists;
    }

    /**
     * [size description]
     * @param  [type] $format [description]
     * @return [type]         [description]
     */
    public function size($format = null) {

        if (!$this->exists) {
            return 0;
        }

        $size = filesize($this->path);

        return $format ? copi::$app->helper('utils')->formatSize($size) : $size;
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

    /**
     * [content description]
     * @return [type] [description]
     */
    public function content() {

        return $this->exists() ? file_get_contents($this->path) : null;
    }

    public function imageSize() {

        if ($info = $this->imageInfo()) {
            return ['width' => $info[0], 'height'=>$info[1]];
        }

        return false;
    }

    public function imageInfo() {

        if (!$this->exists || !$this->isImage()) {
            return false;
        }

        return getimagesize($this->path);
    }

    /**
     * [url description]
     * @return [type] [description]
     */
    public function url() {

        if (!$this->exists) {
            return '';
        }

        return copi::$app->pathToUrl($this->path);
    }

    /**
     * [thumb_url description]
     * @return [type] [description]
     */
    public function thumb_url() {

        if (!$this->exists || !$this->isImage()) {
            return '';
        }

        $args = func_get_args();

        array_unshift($args, $this->path);

        return call_user_func_array('thumb_url', $args);
    }

    /**
     * [isImage description]
     * @return boolean [description]
     */
    public function isImage() {
        return preg_match('/\.(jpg|jpeg|gif|png)$/i', $this->path);
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
        $indexfile = $this->dir.'/index';

        if (file_exists("{$indexfile}.html")) {

            $page = Page::fromCache("{$indexfile}.html");

        } elseif(file_exists("{$indexfile}.md")) {

            $page = Page::fromCache("{$indexfile}.md");
        }

        return $page;
    }

    /**
     *
     */
    public function delete() {

        // delete meta
        if (file_exists($this->path.'.yaml')) {
            unlink($this->path.'.yaml');
        }

        return unlink($this->path);
    }

    /**
     *
     */
    public function rename($filename) {

        rename($this->path, $this->dir.'/'.$filename);

        // rename meta
        if (file_exists($this->path.'.yaml')) {
            rename($this->path.'.yaml', $this->dir.'/'.$filename.'.yaml');
        }

        $this->path = $this->dir.'/'.$filename;

        $this->_initPaths();

        return $this;
    }

    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString() {
        return $this->content();
    }

    /**
     * [toArray description]
     * @return array [description]
     */
    public function toArray() {

        $this->meta();

        $array = get_object_vars($this);

        $array['size']      = $this->size();
        $array['fsize']     = $this->size(true);
        $array['isImage']   = $this->isImage();
        $array['imageSize'] = $this->isImage() ? $this->imageSize() : false;
        $array['imageInfo'] = $this->isImage() ? $this->imageInfo() : false;

        return $array;
    }

    protected function _initPaths() {

        $this->relpath  = str_replace(CP_ROOT_DIR, '', $this->path);
        $this->filename = basename($this->path);
        $this->dir      = dirname($this->path);
        $this->url      = copi::pathToUrl($this->path);
        $this->ext      = pathinfo($this->path, \PATHINFO_EXTENSION);
        $this->exists   = file_exists($this->path);
        $this->mime     = null;

        // get mime
        if ($this->exists) {

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $this->mime = finfo_file($finfo, $this->path);
            finfo_close($finfo);
        }
    }

}
