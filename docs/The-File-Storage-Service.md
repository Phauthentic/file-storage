# The File Storage Service

This service will take care of the following things for you:

 * Stores a file
   * Creating the path using a path builder for the file (if configured)
   * Creating a URL for the path (if configured)
 * Removes a file
   * Deleting all variants of the file as well
 * Removes a variant of the file

## Creating the service

Because we favour composition over inheritance you'll have to pass the Storage service to the FileStorage service when constructing it:

The example below should be handled by your DI container, or your applications bootstrapping process.

```php
$storageService = new StorageService(
    new StorageAdapterFactory()
);

$pathBuilder = new PathBuilder();

$fileStorage = new FileStorage(
    $storageService,
    $pathBuilder
);
```

Passing the path builder is not mandatory, but you **must** set a path to the file object before in this case, otherwise the operation will fail. It is strongly recommended use a path builder to create the path.

There is also a third argument you can pass, which is the UrlBuilder. It is not mandatory either but it is very useful to pre-generate the URL for a file. Especially if it is an outside URL like for AWS objects for example. If you want to do  this and how you do this is totally up to you. We recommend that you create your own URL builder in the case you want to use this feature.

## Storing a file

```php
$file = FileFactory::fromDisk('./tests/Fixtures/titus.jpg', 'local')
    ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');

$file = $fileStorage->store($file);
```

## Removing a file

```php
$file = // ... Reconstitute the file object
$file = $fileStorage->remove($file);
```

## Removing a variant from a file

```php
$file = // ... Reconstitute the file object
$file = $fileStorage->removeVariant($file, 'someVariantsName');
```
