<?php

namespace Copilot\Lib;

/**
 * Class ResourceClass
 * @package Copilot
 */
class ResourceCollection extends \DataCollection {

    /**
     *
     */
    public function sorted() {
       return parent::sort('$item->meta("sort")');
    }
}
