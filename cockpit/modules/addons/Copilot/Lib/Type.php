<?php

namespace Copilot\Lib;

use copi;

/**
 * Class Type
 * @package copilot
 */
class Type {

    public static function definition($type) {

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

        $typedef = array_merge(['layout'=>'raw'], $typedef);

        return $typedef;
    }
}
