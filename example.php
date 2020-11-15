<?php

/*******************************************************************************
 * This is a complete and exhaustive example of A LOT features of the library.
 *
 * In theory you can save a file with just two lines of code as well, but we
 * want to provide a "showcase" here.
 ******************************************************************************/

declare(strict_types=1);

require 'vendor/autoload.php';

use Phauthentic\Infrastructure\Storage\Factories\LocalFactory;
use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageProcessor;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariantCollection;
use Phauthentic\Infrastructure\Storage\FileStorage;
use Phauthentic\Infrastructure\Storage\StorageAdapterFactory;
use Phauthentic\Infrastructure\Storage\StorageService;
use Intervention\Image\ImageManager;

/*******************************************************************************
 * Just a Utility function for this example and some output
 ******************************************************************************/

function readableSize(int $size, int $precision = 2)
{
    for ($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {}

    return round($size, $precision) . ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'][$i];
}

function memoryOutput()
{
    echo PHP_EOL;
    echo 'Peak Memory: ' . readableSize(memory_get_peak_usage(true)) . PHP_EOL;
    echo 'Memory: ' . readableSize(memory_get_usage(true)) . PHP_EOL;
    echo PHP_EOL;
}

memoryOutput();

/*******************************************************************************
 * Configuring the stores - Your DI container or bootstrapping should do this
 ******************************************************************************/

$ds = DIRECTORY_SEPARATOR;

$storageService = new StorageService(
    new StorageAdapterFactory()
);

$storageService->setAdapterConfigFromArray([
    'local' => [
        'class' => LocalFactory::class,
        'options' => [
            'root' => '.' . $ds . 'tmp' . $ds . 'storage1' . $ds
        ]
    ],
    'local2' => [
        'class' => LocalFactory::class,
        'options' => [
            'root' => '.' . $ds . 'tmp' . $ds . 'storage2' . $ds
        ]
    ]
]);

/*******************************************************************************
 * Build services - Your DI container should do this for you
 ******************************************************************************/

$pathBuilder = new PathBuilder();

$fileStorage = new FileStorage(
    $storageService,
    $pathBuilder
);

$imageManager = new ImageManager([
    'driver' => 'gd'
]);

$imageProcessor = new ImageProcessor(
    $fileStorage,
    $pathBuilder,
    $imageManager
);

/*******************************************************************************
 * OPTIONAL: Use a callback to persist the file information in your PDO instance
 * or any other ORM / database library for example. You can also send messages
 * to your event or message bus from here. This is a good an easy opportunity
 * to inject logging as well!
 *
 * You can do this also on your own after storing the file without using the
 * callbacks, but they're convenient and easy to use for things like that.
 ******************************************************************************/

$fileStorage->addCallback('afterSave', function (FileInterface $file) {
    echo 'afterSave called on ' . $file->filename() . PHP_EOL;
    echo 'File stored in ' . $file->path() . PHP_EOL . PHP_EOL;

    return $file;
});

$fileStorage->addCallback('afterRemove', function (FileInterface $file) {
    echo 'afterRemove called on ' . $file->filename() . PHP_EOL;
    echo 'File removed ' . $file->path() . PHP_EOL . PHP_EOL;

    return $file;
});

/*******************************************************************************
 * Storing files in a storage backend
 *
 * This is a very exhaustive example for demonstrating what can bed done,
 * setting the id would be already enough!
 ******************************************************************************/

$file = FileFactory::fromDisk('./tests/Fixtures/titus.jpg', 'local')
    // The UUID alone is usually enough if you don't need to associate
    // a model and collection to it
    ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
    // Change the orginal filename
    ->withFilename('foobar.jpg')
    // Add additional information
    ->addToCollection('avatar')
    ->belongsToModel('User', '1')
    // Add meta data
    ->withMetadata([
        'one' => 'two',
        'two' => 'one'
    ])
    ->withMetadataElement('bar', 'foo');

$file = $fileStorage->store($file);

echo var_export($file->toArray(), true);
echo PHP_EOL . PHP_EOL;

/*******************************************************************************
 * Creating manipulated versions of the file
 *
 * This is intentionally completely separated. You can - and should - run the
 * processors from a shell and not in the context of a HTTP request.
 ******************************************************************************/

$collection = ImageVariantCollection::create();

$collection->addNew('resizeAndFlip')
    ->flipHorizontal()
    ->resize(300, 300)
    ->optimize();

$collection->addNew('crop')
    ->crop(100, 100);

$file = $file->withVariants($collection->toArray());

$file = $imageProcessor
    // Optional: Process only specific variants
    ->processOnlyTheseVariants([
        'resizeAndFlip'
    ])
    ->process($file);

echo var_export($file->toArray(), true);
echo PHP_EOL;

/*******************************************************************************
 * Removing the file
 ******************************************************************************/

$fileStorage->remove($file);

/*******************************************************************************
 * Just some output
 ******************************************************************************/

memoryOutput();
