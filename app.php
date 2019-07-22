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
if(!stream_resolve_include_path('App/DATABASE_LOCK'))
{
    echo '<h2>Warning</h2>';
    echo 'karlharris/core have to be installed to use database storage - run "php App/Cli.php core:db:install".<br>Or, if you just want to use templates and stuff create an "DATABASE_LOCK" file in the App directory to suppress this warning.';
    echo '<h2>Warnung</h2>';
    echo 'karlharris/core muss installiert sein um Datenbank-Funktionalitäten zu nutzen - führen Sie "php App/Cli.php core:db:install" aus.<br>Oder, wenn Sie nur templates etc nutzen möchten, erstellen Sie eine Datei mit dem Namen "DATABASE_LOCK" im App Verzeichnis, um diese Warnung zu umgehen.';
}