<?php

/**
 * [parse_yaml description]
 * @param  [type] $text [description]
 * @return [type]       [description]
 */
function parse_yaml($text) {
    return copi::helper('yaml')->fromString($text);
}

/**
 * [parse_yaml_file description]
 * @param  [type] $path [description]
 * @return [type]       [description]
 */
function parse_yaml_file($path) {
    return copi::helper('yaml')->fromFile($path);
}

/**
 * [thumb_url description]
 * @param  [type] $image   [description]
 * @param  [type] $width   [description]
 * @param  [type] $height  [description]
 * @param  array  $options [description]
 * @return [type]          [description]
 */
function thumb_url($image, $width = null, $height = null, $options=array()) {

    if ($width && is_array($height)) {
        $options = $height;
        $height  = null;
    }

    $options = array_merge(array(
        "rebuild"     => false,
        "cachefolder" => "storage:thumbs",
        "quality"     => 100,
        "base64"      => false,
        "mode"        => "crop",
        "domain"      => false
    ), $options);

    extract($options);

    $path  = copi::$app->path($image);
    $ext   = pathinfo($path, PATHINFO_EXTENSION);
    $url   = "data:image/gif;base64,R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEHAAIALAAAAAABAAEAAAICVAEAOw=="; // transparent 1px gif

    if (!file_exists($path) || is_dir($path)) {
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
            $data = copi::helper("image")->take($path)->{$method}($width, $height)->base64data(null, $quality);
        } catch(Exception $e) {
            return $url;
        }

        $url = $data;

    } else {

        $filetime = filemtime($path);
        $savepath = copi::$app->path($cachefolder)."/".md5($path)."_{$width}x{$height}_{$quality}_{$filetime}_{$mode}.{$ext}";

        if ($rebuild || !file_exists($savepath)) {

            try {
                copi::helper("image")->take($path)->{$method}($width, $height)->save($savepath, $quality);
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
