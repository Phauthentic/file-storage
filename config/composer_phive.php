<?php

/**
 * Phive for installing the dev tools
 * https://github.com/phar-io/phive
 */

$ds = DIRECTORY_SEPARATOR;
$phiveUrl = 'https://phar.io/releases/phive.phar';
$keys = [
    '0x4AA394086372C20A',
    '0x8E730BA25823D8B5',
    '0x31C7E470E2138192',
    '0x4AA394086372C20A',
    '0x0F9684B8B16B7AB0',
    '0xBB5F005D6FFDD89E',
    '0xCF1A108D0E7AE720'
];

/******************************************************************************/

if (!file_exists('.' . $ds . 'phive.phar')) {
    echo 'Downloading Phive (' . $phiveUrl . ')...' . PHP_EOL;
    file_put_contents('.' . $ds . 'phive.phar', file_get_contents($phiveUrl));
}

$keys = implode(',', $keys);
$output = '';
$command = 'php .' . $ds . 'phive.phar install --target .' . $ds . 'bin --trust-gpg-keys ' . $keys;

exec($command, $output);

echo implode(PHP_EOL, $output);
