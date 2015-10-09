<?php

/**
 *
 */
class DataCollection implements \Iterator {

    protected $position = 0;
    protected $items;

    /**
     * [create description]
     * @param  [type] $items [description]
     * @return [type]        [description]
     */
    public static function create($items) {

        $collection = new self($items);

        return $collection;
    }

    /**
     * [__construct description]
     * @param [type] $items [description]
     */
    public function __construct($items) {

        $this->items = $items;
    }

    /**
     * [count description]
     * @return [type] [description]
     */
    public function count() {
        return count($this->items);
    }

    /**
     * [first description]
     * @return [type] [description]
     */
    public function first() {
        return isset($this->items[0]) ? $this->items[0] : null;
    }

    /**
     * [last description]
     * @return [type] [description]
     */
    public function last() {
        return isset($this->items[0]) ? $this->items[count($this->items)-1] : null;
    }

    /**
     * [reverse description]
     * @return [type] [description]
     */
    public function reverse() {
        return $this->setItems(array_reverse($this->items));
    }

    /**
     * [limit description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function limit($number) {

        $items = array_slice($this->items, 0, $number);

        return $this->setItems($items);
    }

    /**
     * [skip description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function skip($number) {

        $items = array_slice($this->items, $number);

        return $this->setItems($items);
    }

    /**
     * [not description]
     * @param  [type] $criteria [description]
     * @return [type]           [description]
     */
    public function not($criteria) {

        return $this->filter("!({$criteria})");
    }

    /**
     * [filter description]
     * @param  [type] $criteria [description]
     * @return [type]           [description]
     */
    public function filter($criteria) {

        $criteria = create_function('$item', "return ({$criteria});");

        return $this->setItems(array_values(array_filter($this->items, $criteria)));

    }

    /**
     * [sort description]
     * @param  [type]  $expr [description]
     * @param  integer $dir  [description]
     * @return [type]        [description]
     */
    public function sort($expr, $dir = 1) {

        $cache    = [];
        $params   = explode(',', $expr);

        $getValue = function($page, $expr) use($cache) {

            if (!isset($cache[$expr])) {
                $cache[$expr] = create_function('$item', "return ({$expr});");
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

        usort($this->items, $callback);

        $this->position = 0;

        return $this;
    }

    /**
     * [setItems description]
     * @param [type] $items [description]
     */
    protected function setItems($items) {

        $collection = new self($items, $this);

        return $collection;
    }

    /**
     * [toArray description]
     * @return [type] [description]
     */
    public function toArray() {

        $items = [];

        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }

    /**
     * Iterator implementation
     */
    public function rewind() {
        if ($this->position !== false) $this->position = 0;
    }

    public function current() {
        return $this->items[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {

        return isset($this->items[$this->position]);
    }
}
