<?php

namespace Copilot;

use copi;


/**
 * Class PageCollection
 * @package Copilot
 */
class PageCollection implements \Iterator {

    protected $position = false;

    protected $pages;
    protected $chain;

    /**
     * @param $folder
     * @return PageCollection
     */
    public static function fromFolder($folder) {

        $pages = [];

        foreach (copi::helper('fs')->ls($folder) as $file) {

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
     * @param $folder
     * @param null $criteria
     * @return PageCollection
     */
    public static function find($folder, $criteria = null) {

        if ($criteria && is_string($criteria)) {
            $criteria = create_function('$p', "return ({$criteria});");
        }

        $pages = [];

        if (file_exists($folder)) {

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder), \RecursiveIteratorIterator::SELF_FIRST);

            foreach($files as $filename => $file){

                if ($file->isFile() && in_array($file->getExtension(), ['md','html'])) {

                    $page = Page::fromCache($file->getRealPath());

                    if ($criteria && $criteria($page)) {
                        $pages[] = $page;
                    } else {
                        $pages[] = $page;
                    }
                }
            }
        }

        $collection = new self($pages);

        return $collection;
    }

    /**
     * @param $pages
     * @param null $chain
     */
    public function __construct($pages, $chain = null) {

        $this->pages    = $pages;
        $this->position = 0;
        $this->chain    = $chain;
    }

    /**
     * @return int
     */
    public function count() {
        return count($this->pages);
    }

    /**
     * @return null
     */
    public function first() {
        return isset($this->pages[0]) ? $this->pages[0] : null;
    }

    /**
     * @return null
     */
    public function last() {
        return isset($this->pages[0]) ? $this->pages[count($this->pages)-1] : null;
    }

    /**
     * @return PageCollection
     */
    public function reverse() {
        return $this->setPages(array_reverse($this->pages));
    }

    /**
     * @param $number
     * @return PageCollection
     */
    public function limit($number) {

        $pages = array_slice($this->pages, 0, $number);

        return $this->setPages($pages);
    }

    /**
     * @param $number
     * @return PageCollection
     */
    public function skip($number) {

        $pages = array_slice($this->pages, $number);

        return $this->setPages($pages);
    }

    /**
     * @param $criteria
     * @return $this|PageCollection
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
     * @return PageCollection
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
     * @return PageCollection
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
     * @param $criteria
     * @return PageCollection
     */
    public function filter($criteria) {

        if (is_string($criteria)) {
            $criteria = create_function('$p', "return ({$criteria});");
        }

        return $this->setPages(array_values(array_filter($this->pages, $criteria)));
    }

    /**
     * @param $expr
     * @param int $dir
     * @return $this
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
     *
     */
    public function sorted() {
       return $this->sort('$p->meta("sort")');
    }

    /**
     * @return null
     */
    public function end() {

        return $this->chain;
    }

    /**
     * @param $pages
     * @return PageCollection
     */
    protected function setPages($pages) {

        $collection = new static($pages, $this);

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
     * @return array
     */
    public function toArray() {
        $pages = [];

        foreach ($this->pages as $page) {
            $pages[] = $page->toArray();
        }

        return $pages;
    }

    /**
     * @return string
     */
    public function toJSON(){
        return json_encode($this->toArray());
    }

}
