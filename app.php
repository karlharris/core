<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

if(!require_once('App/Autoloader.php'))
{
    die('Autoloader.php not found.');
}

echo '<pre>';
echo "<br><br>router()->getControllerClass()<br>---------------------------<br>";
print_r(router()->getControllerClass());
echo "<br><br>router()->getController()<br>---------------------------<br>";
print_r(router()->getController());
echo "<br>router()->getActionName()<br>---------------------------<br>";
print_r(router()->getActionName());
echo "<br><br>router()->getUriParams()<br>---------------------------<br>";
print_r(router()->getUriParams());
echo '</pre>';