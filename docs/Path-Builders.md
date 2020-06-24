# Path Builders

Path builders are used to generate the path the file gets stored under in the storage system.

## Default Path Builder

### Options:

 * **randomPath**: 'sha1',
 * **sanitizeFilename**: true,
 * **beautifyFilename**: false,
 * **sanitizer**: null,
 * **pathTemplate**: '{model}{ds}{randomPath}{ds}{id}',
 * **manipulationTemplate**: '{filename}.{manipulation}.{extension}'

### Path Template Placeholders

 * **{ds}**: Is the directory separator of the system, or the one you configured.
 * **{filename}**: Is the filename
 * **{hashedFilename}**: A sha1() hashed filename string
 * **{extension}**: The extension of the filename without the dot.
 * **{id}**: The UUID of the file
 * **{strippedId}**: The UUID of the file without dashes
 * **{randomPath}**: A semi-random path to increase the depth and variability of the path. This will avoid running into limitations of some file systems.
 * **{mimeType}**: The mime type of the file. Be aware it *might* include invalid chars for a storage backend!
 * **{model}**: The model name
 * **{modelId}**: The id of the models entity
 * **{collection}**: The collection the file belongs into

The following placeholders are only valid when used in a path for a manipulated file.

 * **{manipulation}**: The name of the manipulation
 * **{hashedManipulation}**: A hashed and to six chars truncated version of the manipulation name.

## Implementing your own

To implement your own path builder simply implement the [PathBuilderInterface](../src/PathBuilder/PathBuilderInterface.php).

```php
MyPathBuilder implements PathBuilderInterface
{
    public function path(FileInterface $file, array $options = []): string
    {
        // Your code...
    }

    public function pathForManipulation(FileInterface $file, string $name, array $options = []): string
    {
        // Your code...
    }
}
```
