<?php
/**
 * Redirect to the new URL.
 * 
 * This is here temporary to do our part in avoiding url rot.
 *
 * @todo See if this can be moved elsewhere.
 */

require_once(dirname(__DIR__) . '/ngg-config.php');
require_once(dirname(__DIR__) . '/nggallery.php');
require_once(dirname(__DIR__) . '/src/rss/generator.php');

wp_redirect(\NextCellent\RSS\Generator::imageFeedUrl(), 301);
exit();