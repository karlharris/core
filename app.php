<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

if(!require_once('App/Autoloader.php'))
{
    die('Autoloader.php not found.');
}

router();
echo '<pre>';
print_r(router()->getPathParams());
print_r(router()->getRequestParams());
echo '</pre>';
/*if(is_dir(BP.'install'))
{

}*/