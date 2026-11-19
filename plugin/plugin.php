<?php
defined('KRIS_ROOT') || exit;

use Kris\Plugin\Context;

/**
 * IndexNow: dopo una pubblicazione (e, se attivo, dopo un salvataggio sul sito
 * pubblico) manda ad api.indexnow.org gli indirizzi delle pagine cambiate. La
 * chiave del sito si crea al primo invio ed è servita dall'endpoint «chiave»,
 * nella radice del sito (index.php?kris_plugin=indexnow&endpoint=chiave): così
 * vale per tutti gli indirizzi del sito.
 */
return function (Context $kris): void {
    $state = fn() => $kris->items('plg_indexnow_state');

    $key = function () use ($state): string {
        return $state()->atomic(function ($items): string {
            $item = $items->get(0) ?? ['id' => 0, 'values' => []];
            $key = (string) ($item['values']['key'] ?? '');
            if (preg_match('/^[a-f0-9]{32}$/', $key) !== 1) {
                $key = bin2hex(random_bytes(16));
                $item['values']['key'] = $key;
                $items->save($item);
            }
            return $key;
        });
    };

    $send = function (array $urls, string $why) use ($kris, $key, $state): void {
        $base = $kris->siteUrl();
        $host = (string) parse_url($base, PHP_URL_HOST);
        // Solo indirizzi pubblici del sito: niente localhost, niente altri domini
        $urls = array_values(array_unique(array_filter($urls, fn($u) => is_string($u) && (str_starts_with($u, $base . "/") || $u === $base))));
        if (!$urls || $host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.test') || str_ends_with($host, '.localhost')) return;
        $k = $key();
        $body = ['host' => $host, 'key' => $k, 'keyLocation' => $base . '/' . $kris->url('endpoint:chiave'), 'urlList' => array_slice($urls, 0, 10000)];
        try {
            $r = $kris->http()->post('https://api.indexnow.org/indexnow', $body);
            $kris->log("{$why}: " . count($body['urlList']) . " indirizzi, risposta {$r['status']}" . ($r['status'] >= 400 ? ' ' . mb_substr($r['body'], 0, 200) : ''));
            $state()->atomic(function ($items) use ($r) {
                $item = $items->get(0) ?? ['id' => 0, 'values' => []];
                $item['values']['last'] = date('c') . ' ' . $r['status'];
                $items->save($item);
            });
        } catch (\Throwable $e) {
            $kris->log("{$why}: invio non riuscito, " . $e->getMessage());
        }
    };

    $kris->on('endpoint:chiave', fn(array $req) => ['status' => 200, 'text' => $key()]);

    $kris->on('published', function (array $p) use ($send): void {
        $urls = [];
        foreach ($p['pages'] as $page) array_push($urls, ...$page['urls']);
        $send($urls, 'Pubblicazione');
    });

    $kris->on('content_saved', function (array $p) use ($kris, $send): void {
        if (($kris->settings()['on_save'] ?? false) !== true) return;
        $urls = [];
        foreach ($p['changes'] as $c) array_push($urls, ...$kris->urls($c['type'], $c['id']));
        $send($urls, 'Salvataggio');
    });
};
