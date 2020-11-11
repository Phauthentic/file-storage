# Processors

Processors take a file object and do something with the file the object represents. The image processor for example generates thumbnails and other variations of an image file.

## Included processors

### Stack Processor

The stack processor takes a list of other processors and processes them in the order you added them to the list.

```php
$processor1 = new MyAudioFileProcessor();
$processor2 = new MyDocumentFileProcessor();

$processor = new StackProcessor([
    $processor1,
    $processor2
]);

$file = $processor->process($file);
```

## Implementing your own

It is recommended you implement your own processor for specific tasks. For example there is no generic way anything  queue related could be handled by this library. So implement your own `QueueProcessor` that will push tasks to a queue.

```php
class MyProcessor implements ProcessorInterface
{
    public function process(FileInterface $file): FileInterface
    {
        // Your code,
        // modify the file do whatever else you want

        return $file;
    }
}
```
