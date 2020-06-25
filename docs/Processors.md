# Processors

Processors take a file object and do something with the file the object represents. The image processor for example generates thumbnails and other variations of an image file.

# Implementing your own

```php
class MyProcessor implements ProcessorInterface
{
    public function process(FileInterface $file): FileInterface
    {
        // Your code
    }
}
```
