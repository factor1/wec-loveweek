<?php

require_once 'Zend/Registry.php';

class Zend_View_Helper_AddContent
{
	public function addContent($path = '')
	{
        $config = Zend_Registry::get('config');

        if ($path) {
            $path = $config->application->pages . $path;

            if (file_exists($path)) {
                include $path;
            } else {
                echo '<p><b>ERROR: The content file could not be found:</b><br />' . $path . '</p>';
            }
        }
    }
}
