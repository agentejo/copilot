<?php

namespace Copilot\Lib;

use copi;

/**
 * Class Resource
 * @package Copilot
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
    protected $imageinfo;
    protected $dimensions;

    /**
     * @param $path
     */
    public function __construct($path) {

        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $this->path = $path;
        $this->meta = null;

        $this->_initPaths();
    }

    /**
     * @param null $key
     * @param null $default
     * @return \ContainerArray|null
     */
    public function meta($key=null, $default=null) {

        if (!$this->meta) {

            $meta = [];

            if (is_file($this->path.'.yaml')) {
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
     * @param $data
     * @param bool|true $extend
     * @return $this
     */
    public function updateMeta($data, $extend = true) {

        $meta = $extend ? $this->meta()->extend($data) : new \ContainerArray($data);

        $this->meta = $meta;

        copi::$app->helper('yaml')->toFile($this->path.'.yaml', $this->meta->toArray());

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
    public function relpath() {
        return $this->relpath;
    }

    /**
     * @return mixed
     */
    public function mime() {
        return $this->mime;
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
     * @return boolean
     */
    public function exists() {
        return $this->exists;
    }

    /**
     * @return string
     */
    public function base64() {
        return base64_encode($this->content());
    }

    /**
     * @return string
     */
    public function dataUri() {
        return 'data:'.$this->mime().';base64,'.$this->base64();
    }

    /**
     * @param null $format
     * @return int
     */
    public function size($format = null) {

        if (!$this->exists) {
            return 0;
        }

        $size = filesize($this->path);

        return $format ? copi::$app->helper('utils')->formatSize($size) : $size;
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
     * @return null|string
     */
    public function content() {

        return $this->exists() ? file_get_contents($this->path) : null;
    }

    /**
     * @return array|bool
     */
    public function imageSize() {

        if ($info = $this->imageInfo()) {
            return ['width' => $info[0], 'height'=>$info[1]];
        }

        return false;
    }

    /**
     * @return object
     */
    public function dimensions() {

        if (!$this->dimensions) {
            $dim = [ 'width' => 0, 'height'=> 0 ];

            if ($info = $this->imageInfo()) {
                $dim = ['width' => $info[0], 'height'=>$info[1]];
            }

            $this->dimensions = (object)$dim;
        }

        return $this->dimensions;
    }

    /**
     * @return int
     */
    public function width() {
        return $this->dimensions()->width;
    }

    /**
     * @return int
     */
    public function height() {
        return $this->dimensions()->height;
    }

    /**
     * @return int
     */
    public function ratio() {
        return ($this->dimensions()->width / $this->dimensions()->height);
    }

    /**
     * @return boolean
     */
    public function isPortrait() {
        return ($this->dimensions()->height > $this->dimensions()->width);
    }

    /**
     * @return boolean
     */
    public function isLandscape() {
        return ($this->dimensions()->height < $this->dimensions()->width);
    }

    /**
     * Checks if the dimensions of the asset are square
     *
     * @return boolean
     */
    public function isSquare() {
        return ($this->dimensions()->height == $this->dimensions()->width);
    }

    /**
     * landscape | portrait | square
     *
     * @return string
     */
    public function orientation() {
        if($this->isPortrait())  return 'portrait';
        if($this->isLandscape()) return 'landscape';
        if($this->isSquare())    return 'square';
    }

    /**
     * @return array|bool
     */
    public function imageInfo() {

        if (!$this->exists || !$this->isImage()) {
            return false;
        }

        if (!$this->imageinfo) {
            $this->imageinfo = getimagesize($this->path);
        }

        return $this->imageinfo;
    }

    /**
     * @return string
     */
    public function url() {

        if (!$this->exists) {
            return '';
        }

        return copi::$app->pathToUrl($this->path);
    }

    /**
     * @return mixed|string
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
     * @return PageCollection
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
     * @return misc
     */
    public function parent() {

        $page      = null;
        $indexfile = $this->dir.'/index';

        if (is_file("{$indexfile}.html")) {

            $page = Page::fromCache("{$indexfile}.html");

        } elseif(is_file("{$indexfile}.md")) {

            $page = Page::fromCache("{$indexfile}.md");
        }

        return $page;
    }

    /**
     * @return misc
     */
    public function page() {
        return $this->parent();
    }

    /**
     * @return bool
     */
    public function delete() {

        // delete meta
        if (is_file($this->path.'.yaml')) {
            unlink($this->path.'.yaml');
        }

        return unlink($this->path);
    }

    /**
     * @param $filename
     * @return $this
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
     * @return mixed
     */
    public function __toString() {
        return $this->path;
    }

    /**
     * @return array
     */
    public function toArray() {

        $this->meta();

        $array = get_object_vars($this);

        $array['size']        = $this->size();
        $array['fsize']       = $this->size(true);
        $array['isImage']     = $this->isImage();
        $array['imageSize']   = $this->isImage() ? $this->imageSize() : false;
        $array['imageInfo']   = $this->isImage() ? $this->imageInfo() : false;
        $array['dimensions']  = $this->dimensions();
        $array['orientation'] = $this->orientation();
        $array['mime']        = $this->mime();

        return $array;
    }

    /**
     * @return string
     */
    public function toJSON(){
        return json_encode($this->toArray());
    }

    protected function _initPaths() {

        $this->path     = str_replace(DIRECTORY_SEPARATOR, '/', $this->path);
        $this->relpath  = str_replace(CP_ROOT_DIR, '', $this->path);
        $this->filename = basename($this->path);
        $this->dir      = dirname($this->path);
        $this->url      = copi::pathToUrl($this->path);
        $this->ext      = pathinfo($this->path, \PATHINFO_EXTENSION);
        $this->exists   = is_file($this->path);
        $this->mime     = null;

        if ($this->exists && isset(\Lime\App::$mimeTypes[$this->ext])) {
            $this->mime = \Lime\App::$mimeTypes[$this->ext];
        }
    }

}
