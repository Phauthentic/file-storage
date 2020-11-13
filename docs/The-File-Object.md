# The File Object

The *File* object is the central object in this library around which all functionality has being built. The file object contains all information needed to store and retrieve the file later from a storage backend. It also has methods to add variants to the file that are checked and applied by a [*Processor*](Processors.md). This can be image or video processing for example in the most common cases.

The file object is more a [data transfer object](https://en.wikipedia.org/wiki/Data_transfer_object) than something that implements business logic. It is basically like an adapter between your apps understanding of what and where a file is to the actual storage system that works with the file itself.

**Be aware the file object that comes with this library is [immutable](https://en.wikipedia.org/wiki/Immutable_object)!**

## Creating a new file

You can either instantiate the file object directly or using the [FileFactory](../src/FileFactory.php) to do so.

Using the factory (recommended):

```php
$file = FileFactory::fromDisk(
    './tests/Fixtures/titus.jpg',
    'local'
)
->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');
```

File objects can be created as well from objects that implement the [PSR7](https://www.php-fig.org/psr/psr-7/) `UploadedFileInterface`:

```php
$file = FileFactory::fromUploadedFile(
    $uploadedFile,
    'local'
)
->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');
```

Manually creating a file object will require you to pass the information:

```php
$file = File::create(
    'some-horse.jpg',
    filesize('./tests/Fixtures/titus.jpg')
    'image/jpeg',
    'local' // The storage backend / instance to use
)
->withFile('./tests/Fixtures/titus.jpg')
->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');;
```

Instead of calling `withFile()` you can also use `withResource()` to re-use an already opened resource.

**Note:** It is *required* that you add a file to the object when you want to store the file in a storage backend. The service will check if the file object has file data set, if not you'll get an exception when you try to store that file object.

You'll also have to add the (uu)id to the file object if your intended path for the file should require it. The same applies to other data that might be relevant for your path.

## Serialization & Saving the File Object

The file object is serializable to json, and you can call `toArray()` on it to turn it into an array that you can either save in the structure you get or continue transforming it into whatever structure your persistence layer expects.

## Restoring the file object

You'll have to reconstruct the file object later from your persisted information when you want to come back to it later and work with new variants for example. Depending on your architecture, your domain model could also simply implement the `FileInterface` if this is more convenient for your.

You basically do the same as when storing a new file but without calling `withFile()` or `withResource()`.

```
// Some pseudo-code to illustrate the idea
$dbRow = $myDbConnection
    ->getTable('file_storage')
    ->getRow('914e1512-9153-4253-a81e-7ee2edc1d973');

$file = File::create(
    $dbRow->filename,
    $dbRow->filesize,
    $dbRow->mimetype
    $dbRow->storage
)
->withUuid($dbRow->id);
```

If you added metadata and variants to it or other things you'll have to restore the values by calling the according methods.

## Adding metadata

You can add metadata to the file object if you want to for whatever purpose you might have.

```php
$file
    ->withMetadata([
        'one' => 'two',
        'two' => 'one'
    ])
    ->withMetadataKey('bar', 'foo');
    // removes a specific key
    ->withoutMetadataKey('one');
    // removes all meta data
    ->withoutMetadata()
```

To access the metadata you can get all or just a key:

```php
$file->metaData();
```

```php
$file->metaDataKey('foo');
```

## Extending functionality

You can either extend the File object or implement your very own File object by implementing the [FileInterface](../src/FileInterface.php)
