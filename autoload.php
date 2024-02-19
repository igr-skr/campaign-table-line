<?php

if ( ! defined('ABSPATH')) {
    exit;
}

\spl_autoload_register( function ( $class ) {
    if ( stripos($class, 'goldbach\CampaignTableLine') !== 0 ) return;

    $classFile = str_replace('\\', '/', substr($class, strlen('goldbach\CampaignTableLine') + 1) . '.php');

    require_once __DIR__ . '/' . $classFile;
});