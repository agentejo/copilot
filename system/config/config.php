<?php

return [

    // enable debug mode by default on local dev env:
    'debug'        => preg_match('/(localhost|::1|\.dev)$/', $_SERVER['SERVER_NAME']),

    'offline'      => false,

    'app.name'     => 'copilot',
    'timezone'     => 'UTC',
    'site.title'   => 'Copilot',
    'base_url'     => CP_BASE_URL,
    'base_route'   => CP_BASE_ROUTE,
    'docs_root'    => CP_DOCS_ROOT,
    'route'        => CP_CURRENT_ROUTE,
    'session.name' => md5(CP_DOCS_ROOT),
    'sec-key'      => 'copilot-dz44-s8h7-v814-a49f1a45f5e1',

    'helpers'      => [

        "acl"      => "Lime\\Helper\\SimpleAcl",
        "assets"   => "Lime\\Helper\\Assets",
        "fs"       => "Lime\\Helper\\Filesystem",
        "image"    => "Lime\\Helper\\Image",
        "i18n"     => "Lime\\Helper\\I18n",
        "utils"    => "Lime\\Helper\\Utils",
        "coockie"  => "Lime\\Helper\\Cookie",
        "yaml"     => "Lime\\Helper\\YAML",
        "markdown" => "Lime\\Helper\\Markdown",
    ]
];
