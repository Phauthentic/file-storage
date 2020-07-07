# File Storage

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/Phauthentic/file-storage/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Phauthentic/file-storage/)
[![Code Quality](https://img.shields.io/scrutinizer/g/Phauthentic/file-storage/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Phauthentic/file-storage/)

A framework agnostic file storage system.

Dealing with uploads, storing and managing the files has been very often painful and cumbersome. This library tries to make this more easy and convenient for you - no matter what framework you are using.

This library is pretty much the same as these plugins for [Laravel](https://github.com/spatie/laravel-medialibrary), [Yii](https://github.com/yii2tech/file-storage) and [Cake](https://github.com/burzum/cakephp-file-storage), but not tied to any framework or ORM and less tight coupled.

## Features

 * **Store files on almost everything:** Local disk, Amazon S3, Dropbox... and many more through the fantastic [league/flysystem](thephpleague/flysystem) library.
 * Framework agnostic
 * Image processing (optional feature / dependency)
 * Image optimization (optional feature / dependency)
 * Provides factories for the adapters
 * As lite as possible on dependencies

## Installation

```sh
composer require phauthentic/file-storage
```

## Documentation

Please start by reading [docs/index.md](/docs/index.md) in this repository.

## Example

Take a look at [example.php](example.php) or even run it:

```php
php example.php
```

The example should give you an exhaustive overview of the library.

## License

Copyright 2020 Florian Krämer

Licensed under the [MIT license](license.txt).
