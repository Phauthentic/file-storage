<?php

/**
 * Phive for installing the dev tools
 * https://github.com/phar-io/phive
 */

$ds = DIRECTORY_SEPARATOR;

if (!file_exists('.' . $ds . 'phive.phar')) {
    echo 'Downloading Phive (https://phar.io/)...' . PHP_EOL;
	file_put_contents('.' . $ds . 'phive.phar', file_get_contents('https://phar.io/releases/phive.phar'));
}

$output = '';
exec('php .' . $ds . 'phive.phar install', $output);

echo implode(PHP_EOL, $output);
