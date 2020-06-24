# Example

```php
declare(strict_types=1);

require 'vendor/autoload.php';

use Phauthentic\Infrastructure\Storage\Factories\LocalFactory;
use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageProcessor;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageManipulation;
use Phauthentic\Infrastructure\Storage\FileStorage;
use Phauthentic\Infrastructure\Storage\StorageAdapterFactory;
use Phauthentic\Infrastructure\Storage\StorageService;
use Intervention\Image\ImageManager;

/*******************************************************************************
 * Configuring the stores - Your DIC or bootstrapping should do this
 ******************************************************************************/

$ds = DIRECTORY_SEPARATOR;

$storageService = new StorageService(
    new StorageAdapterFactory()
);

$storageService->loadAdapterConfigFromArray([
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
 * Build services - Your DIC should do this for you
 ******************************************************************************/

$pathBuilder = new PathBuilder();
$fileStorage = new FileStorage(
    $storageService,
    $pathBuilder
);
$imageManager = new ImageManager([
    'driver' => 'gd'
]);
$imageManipulator = new ImageProcessor(
    $fileStorage,
    $pathBuilder,
    $imageManager
);

/*******************************************************************************
 * Working with files
 *
 * This is a very exhaustive example for demonstrating what can bed done,
 * setting the id would be already enough!
 ******************************************************************************/
$file = FileFactory::fromDisk('./tests/Fixtures/titus.jpg', 'local')
    ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
    ->withFilename('foobar.jpg')
    ->addToCollection('avatar')
    ->belongsToModel('User', '1')
    ->withMetadata([
        'one' => 'two',
        'two' => 'one'
    ])
    ->withMetadataKey('bar', 'foo');

$file = $fileStorage->store($file);

/*******************************************************************************
 * Creating manipulated versions of the file
 ******************************************************************************/

$file = $file->withManipulations([
    'resizeAndFlip' => ImageManipulation::create('resizeAndFlip')
        ->flipHorizontal()
        ->resize(300, 300)
        ->optimize()
        ->toArray(),
    'crop' => ImageManipulation::create('crop')
        ->crop(100, 100)
        ->toArray()
]);

$file = $imageManipulator
    // You can limit the versions you want to process
    ->processOnlyTheseVersions([
        'resizeAndFlip'
    ])
    ->process($file);

echo var_export($file->toArray(), true);

/*******************************************************************************
 * Removing the file
 ******************************************************************************/

$fileStorage->remove($file);
```
