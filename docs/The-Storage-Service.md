# The Storage Service

### Constructing the service

```php
use Phauthentic\Infrastructure\Storage\StorageAdapterFactory;
use Phauthentic\Infrastructure\Storage\StorageService;

$service = new StorageService(
    new StorageAdapterFactory()
);
```

The service takes a `AdapterCollectionInterface` as *optional* second argument, in the case you want to replace it.

### Loading Adapters from a Config Array

You can add an array of configured adapters to the service. The array has to be a two dimensional array, were the first levels key is the name under which the adapter instance and configuration is stored.

The second level of the array structure **must** contain a `class` and `options` key. The class hast to be the full qualified class name or an alias that can be resolved by the factory. The options array contains all the options passed to the adapter factory.

**Note:** *The adapters are lazy loaded!* Just by calling this method you don't get instances of the adapters. You must call `adapter($name)` and the service will then instantiate an adapter for you if it was not already instantiated.

```php
$service->setAdapterConfigFromArray([
    'local' => [
        'class' => 'Local',
        'options' => [
            $this->tmp
        ]
    ]
]);
```

### Getting an adapter

To get an adapter instance from the service just call it with the adapter() method and pass the name under which you configured it.

```php
$service->adapter('local');
```

### Storing a file

You can provide a path to a file in your system

```php
$service->storeFile('local', '/some/path/file.txt', '/the/file/to/store.txt');
```

or a resource:

```php
$resource = fopen('/the/file/to/store.txt', 'rb');
$service->storeResource('local', '/some/path/file.txt', $resource);
```

### Deleting a file

```php
$service->removeFile('local', '/some/path/file.txt');
```

### Checking if a file exists

```php
$service->fileExists('local', '/some/path/file.txt');
```
