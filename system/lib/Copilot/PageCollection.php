<?php

namespace Copilot;

use copi;

/**
 *
 */
class PageCollection implements \Iterator {

    protected $position = false;

    protected $pages;
    protected $chain;

    /**
     * [fromFolder description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public static function fromFolder($path) {

        $pages = [];

        foreach (copi::helper('fs')->ls($path) as $file) {

            if ($file->isDir()) {

                $page      = null;
                $indexfile = $file->getRealPath().'/index';

                if (file_exists("{$indexfile}.html")) {
                    $page = "{$indexfile}.html";
                } elseif(file_exists("{$indexfile}.md")) {
                    $page = "{$indexfile}.md";
                }

                if ($page) {
                    $pages[] = Page::fromCache($page);
                }

            } elseif (in_array($file->getExtension(), ['html', 'md']) && $file->getBasename('.'.$file->getExtension()) != 'index') {
                $pages[] = Page::fromCache($file->getRealPath());
            }
        }

        $collection = new self($pages);

        return $collection;
    }

    /**
     * [__construct description]
     * @param [type] $pages [description]
     */
    public function __construct($pages, $chain = null) {

        $this->pages    = $pages;
        $this->position = 0;
        $this->chain    = $chain;
    }

    /**
     * [count description]
     * @return [type] [description]
     */
    public function count() {
        return count($this->pages);
    }

    /**
     * [first description]
     * @return [type] [description]
     */
    public function first() {
        return isset($this->pages[0]) ? $this->pages[0] : null;
    }

    /**
     * [last description]
     * @return [type] [description]
     */
    public function last() {
        return isset($this->pages[0]) ? $this->pages[count($this->pages)-1] : null;
    }

    /**
     * [reverse description]
     * @return [type]
     */
    public function reverse() {
        return $this->setPages(array_reverse($this->pages));
    }

    /**
     * [limit description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function limit($number) {

        $pages = array_slice($this->pages, 0, $number);

        return $this->setPages($pages);
    }

    /**
     * [skip description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function skip($number) {

        $pages = array_slice($this->pages, $number);

        return $this->setPages($pages);
    }

    /**
     * [not description]
     * @param  [type] $criteria [description]
     * @return [type]           [description]
     */
    public function not($criteria) {

        if (!$criteria) {
            return $this;
        }

        if ($criteria instanceof Page) {

            $pages = [];

            foreach ($this->pages as &$page) {

                if ($page->path() !== $criteria->path()) {
                    $pages[] = $page;
                }
            }

            return $this->setPages($pages);
        }

        return $this->filter("!({$criteria})");
    }

    /**
     * [visible description]
     * @return [type] [description]
     */
    public function visible() {

        $pages = [];

        foreach ($this->pages as &$page) {

            if ($page->isVisible()) {
                $pages[] = $page;
            }
        }

        return $this->setPages($pages);
    }

    /**
     * [hidden description]
     * @return [type]
     */
    public function hidden() {

        $pages = [];

        foreach ($this->pages as &$page) {

            if (!$page->isVisible()) {
                $pages[] = $page;
            }
        }

        return $this->setPages($pages);
    }

    /**
     * [filter description]
     * @param  [type] $criteria [description]
     * @return [type]           [description]
     */
    public function filter($criteria) {

        $criteria = create_function('$p', "return ({$criteria});");

        return $this->setPages(array_values(array_filter($this->pages, $criteria)));

    }

    /**
     * [sort description]
     * @param  [type]  $expr [description]
     * @param  integer $dir  [description]
     * @return [type]        [description]
     */
    public function sort($expr, $dir = 1) {

        if (is_string($dir)) {
            $dir = ($dir=='desc') ? -1 : 1;
        }

        $cache    = [];
        $params   = explode(',', $expr);

        $getValue = function($page, $expr) use($cache) {

            if (!isset($cache[$expr])) {
                $cache[$expr] = create_function('$p', "return ({$expr});");
            }

            $value = $cache[$expr]($page);

            return $value;
        };


        $callback = function($a, $b) use($params, $getValue, $dir) {

            $result = 0;

            foreach ($params as $param) {

                $valA = $getValue($a, $param);
                $valB = $getValue($b, $param);

                if ($valA > $valB) {
                    $result = 1;
                } elseif ($valA < $valB) {
                    $result = -1;
                }

                if ($result !== 0) {

                    $result *= $dir;
                    break;
                }
            }

            return $result;
        };

        usort($this->pages, $callback);

        $this->position = 0;

        return $this;
    }

    /**
     * [end description]
     * @return [type]           [description]
     */
    public function end() {

        return $this->chain;
    }

    /**
     * [setPages description]
     * @param [type] $pages [description]
     */
    protected function setPages($pages) {

        $collection = new self($pages, $this);

        return $collection;
    }

    /**
     * Iterator implementation
     */
    public function rewind() {
        if ($this->position !== false) $this->position = 0;
    }

    public function current() {
        return $this->pages[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {

        return isset($this->pages[$this->position]);
    }

    /**
     * [toArray description]
     * @return [type]
     */
    public function toArray() {
        $pages = [];

        foreach ($this->pages as $page) {

            $pages[] = $page->toArray();
        }

        return $pages;
    }

    /**
    * [__toJSON description]
    * @return string [description]
     */
    public function toJSON(){
        return json_encode($this->toArray());
    }

}
