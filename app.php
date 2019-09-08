<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

if(!PHP_VERSION_ID > 70300)
{
    header('Content-type: text/html; charset=utf-8', true, 503);
    echo '<h2>Error</h2>';
    echo 'Your server is running PHP version ' . PHP_VERSION . ' but karlharris/core requires at least PHP 7.3.0';
    echo '<h2>Fehler</h2>';
    echo 'Auf Ihrem Server läuft PHP version ' . PHP_VERSION . ', karlharris/core benötigt mindestens PHP 7.3.0';
    return;
}
if(!require_once('App/Autoloader.php'))
{
    die('Autoloader.php not found.');
}