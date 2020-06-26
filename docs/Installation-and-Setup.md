# Installation and Setup

## Installation

Install it via [Composer](https://getcomposer.org/)

```sh
composer require phauthentic/file-storage
```

If you want to manually install it we assume you know what you're doing by not using Composer.

## Development Tools

We are using [Phive](https://github.com/phar-io/phive) for most of the dev tools. This provides a few advantges, mostly that we get away with a lot less dev dependencies that can cause additional conflicts.

To install phpunit and other dev tools run

```sh
composer phive
```

It will download Phive and execute it to install phar versions of these dev tools:

 * [phpunit](https://phpunit.de/)
 * [phpcs](https://github.com/squizlabs/PHP_CodeSniffer/)
 * [phpcbf](https://github.com/squizlabs/PHP_CodeSniffer/)
 * [phpstan](https://phpstan.org/)
 * [grumphp](https://github.com/phpro/grumphp)

To register grumphp git hooks run

```sh
.\bin\grumphp git:init
```
