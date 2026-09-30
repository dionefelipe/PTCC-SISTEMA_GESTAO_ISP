<?php

declare(strict_types=1);

function app_base_url(): string
{
    $root = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
    $doc = isset($_SERVER['DOCUMENT_ROOT'])
        ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT'])
        : '';

    if ($doc !== '' && strpos($root, $doc) === 0) {
        $rel = substr($root, strlen($doc));
        return rtrim($rel, '/') . '/';
    }

    return '/ETEC/TCC/SISTEMA_GESTAO/';
}

$BASE = app_base_url();
