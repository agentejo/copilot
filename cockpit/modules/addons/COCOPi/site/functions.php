<?php

/**
 * [parse_yaml description]
 * @param  string $text
 * @return array
 */
function parse_yaml($text) {
    return copi::helper('yaml')->fromString($text);
}

/**
 * [parse_yaml_file description]
 * @param  string $path
 * @return array
 */
function parse_yaml_file($path) {
    return copi::helper('yaml')->fromFile($path);
}


/**
 *
 * @param  string $path
 * @return string
 */
function url_to($path) {

    $path = trim($path);

    if (!$path) {
        return '';
    }

    if (substr($path, 0, 1) == '/') {
        return copi::$app->routeUrl($path);
    }

    if (strpos($path, ':') !== false && $file = copi::$app->path($path)) {
        return copi::$app->pathToUrl($file);
    }

    return $path;
}

/**
 * [thumb_url description]
 * @param  [type] $image
 * @param  [type] $width
 * @param  [type] $height
 * @param  array  $options
 * @return [type]
 */
function thumb_url($image, $width = null, $height = null, $options=array()) {

    if ($width && is_array($height)) {
        $options = $height;
        $height  = null;
    }

    $options = array_merge(array(
        "rebuild"       => false,
        "cachefolder"   => "cockpit:storage/thumbs",
        "quality"       => 100,
        "base64"        => false,
        "mode"          => "crop",
        "domain"        => false,
        "overlay"       => false,
        "overlay_pos"   => "center",
        "overlay_alpha" => 1
    ), $options);

    extract($options);

    $path  = copi::$app->path($image);
    $ext   = pathinfo($path, PATHINFO_EXTENSION);
    $url   = "data:image/gif;base64,R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEHAAIALAAAAAABAAEAAAICVAEAOw=="; // transparent 1px gif

    if (!is_file($path) || is_dir($path)) {
        return $url;
    }

    if (!in_array(strtolower($ext), array('png','jpg','jpeg','gif'))) {
        return $url;
    }

    if (is_null($width) && is_null($height)) {
        return copi::$app->pathToUrl($path);
    }

    if (!in_array($mode, ['crop', 'best_fit', 'resize','fit_to_width'])) {
        $mode = 'crop';
    }

    $method = $mode == 'crop' ? 'thumbnail':$mode;

    if ($base64) {

        try {

            $img = copi::helper("image")->take($path)->{$method}($width, $height);

            if ($overlay && $overlay = copi::$app->path($overlay)) {
                $img->overlay($overlay, $overlay_pos, $overlay_alpha);
            }

            $data = $img->base64data(null, $quality);

        } catch(Exception $e) {
            return $url;
        }

        $url = $data;

    } else {

        $filetime = filemtime($path);
        $savepath = copi::$app->path($cachefolder)."/".md5($path.json_encode($options))."_{$width}x{$height}_{$quality}_{$filetime}_{$mode}.{$ext}";

        if ($rebuild || !is_file($savepath)) {

            try {

                $img = copi::helper("image")->take($path)->{$method}($width, $height);

                if ($overlay && $overlay = copi::$app->path($overlay)) {
                    $img->overlay($overlay, $overlay_pos, $overlay_alpha);
                }

                $img->save($savepath, $quality);

            } catch(Exception $e) {
                return $url;
            }
        }

        $url = copi::$app->pathToUrl($savepath);

        if ($domain) {
            $url = rtrim(copi::$app->getSiteUrl(true), '/').$url;
        }
    }

    return $url;
}
