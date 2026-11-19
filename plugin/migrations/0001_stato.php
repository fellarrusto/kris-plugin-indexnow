<?php
defined('KRIS_ROOT') || exit;

// Il tipo con la chiave di IndexNow del sito (un contenuto, id 0): la chiave si crea al primo invio
return ['up' => function (array $d): array {
    $d['schema']['types']['plg_indexnow_state'] ??= [
        'single' => true,
        'fields' => [
            ['name' => 'key', 'type' => 'text', 'localized' => false],
            ['name' => 'last', 'type' => 'text', 'localized' => false],
        ],
    ];
    return $d;
}];
