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
 * [parse_json_file description]
 * @param  string $path
 * @return array
 */
function parse_json_file($path) {
    return json_decode(copi::helper('fs')->read($path), true);
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

    } elseif (strpos($path, ':') !== false && $file = copi::$app->path($path)) {

        return copi::$app->pathToUrl($file);

    } elseif ($file = copi::$app->path("site:{$path}")) {

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
function thumb_url($image, $width = null, $height = null, $options = array()) {

    if ($width && is_array($height)) {
        $options = $height;
        $height  = null;
    }

    $options = array_merge(array(
        'rebuild'       => false,
        'cachefolder'   => 'site:storage/thumbs',
        'quality'       => 85,
        'base64'        => false,
        'mode'          => 'thumbnail',
        'filter'        => '',
        'domain'        => false,
        'overlay'       => false,
        'overlay_pos'   => 'center',
        'overlay_alpha' => 1
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

        $url = copi::$app->pathToUrl($path);

        if ($domain) {
            $url = rtrim(copi::$app->getSiteUrl(true), '/').$url;
        }

        return $url;
    }

    if (!$width || !$height) {

        list($w, $h, $type, $attr)  = getimagesize($path);

        if (!$width) $width = ceil($w * ($height/$h));
        if (!$height) $height = ceil($h * ($width/$w));
    }

    if (!in_array($mode, ['thumbnail', 'best_fit', 'resize','fit_to_width'])) {
        $mode = 'thumbnail';
    }

    $method   = $mode == 'crop' ? 'thumbnail' : $mode;
    $filetime = filemtime($path);
    $hash     = md5($path.json_encode($options))."_{$width}x{$height}_{$quality}_{$filetime}_{$mode}.{$ext}";
    $savepath = rtrim(copi::$app->path($cachefolder), '/')."/{$hash}";

    if ($rebuild || !is_file($savepath)) {

        try {

            $img = copi::helper("image")->take($path)->{$method}($width, $height);

            if ($overlay && $overlay = copi::$app->path($overlay)) {
                $img->overlay($overlay, $overlay_pos, $overlay_alpha);
            }

            $img->toFile($savepath, null, $quality);

        } catch(Exception $e) {
            return $url;
        }
    }

    if ($base64) {
        return "data:image/{$ext};base64,".base64_encode(file_get_contents($savepath));
    }

    $url = copi::$app->pathToUrl($savepath);

    if ($domain) {
        $url = rtrim(copi::$app->getSiteUrl(true), '/').$url;
    }

    return $url;
}


function render_layoutbuilder_elements($layout) {

    foreach ($layout as &$element) {

        $component = $element['component'];
        $settings  = $element['settings'];
        $children  = isset($element['children']) ? $element['children'] : [];

        echo copi::view("layouts:layout-builder/{$component}.html", compact('element', 'component', 'settings', 'children'));
    }
}
