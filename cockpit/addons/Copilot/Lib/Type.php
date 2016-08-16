<?php

namespace Copilot\Lib;

use copi;

/**
 * Class Type
 * @package copilot
 */
class Type {

    protected static $cache = [];

    public static function definition($type) {

        $key = $type;

        if (!isset(self::$cache[$key])) {

            $subtype = false;
            $typedef = [];

            // is a subtype?
            if (strpos($type, '/')) {
                $parts   = explode('/', $type);
                $subtype = trim($parts[1]);
                $type    = trim($parts[0]);
            }

            if ($typepath = copi::path("types:{$type}.yaml")) {

                $typedef = copi::$app->helper('yaml')->fromFile($typepath);

                if ($subtype && isset($typedef['subtypes'][$subtype])) {
                    $typedef = $typedef['subtypes'][$subtype];
                }
            }

            self::$cache[$key] = array_replace_recursive([
                'layout'  => 'raw',
                'ext'     => 'html',
                'content' => [
                    'visible' => true,
                    'parse'   => false
                ]
            ], $typedef);

        }

        return self::$cache[$key];
    }
}
