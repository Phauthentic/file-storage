# URL Builders

The library provides the [UrlBuilderInterface](../src/UrlBuilder/UrlBuilderInterface.php) for you to implement URL builders, so that you can generate an URL for a file object.

Wrap your projects or frameworks URL building code in a custom URL builder or implement the URL builder interface in your URL building object.

## Local URL Builder

This is a *very* basic URL builder that doesn't have any knowledge about your applications routing or URL building.

All it does is it takes the path from a file and prefixes it with a base path you provide to the URL builder.

It is highly recommended to implementing your own path builder.

```php
$builder = new LocalUrlBuilder('http:///my.app/basepath/');

$build->url($file);
$build->urlForManipulation($file, 'someManipulationName');
```

## Implementing your own

To implement your own url builder implement the [UrlBuilderInterface](../src/UrlBuilder/UrlBuilderInterface.php).

```php
MyUrlBuilder implements UrlBuilderInterface
{
    public function url(FileInterface $file): string
    {
        // Your code...
    }

    public function urlForManipulation(FileInterface $file, string $version): string
    {
        // Your code...
    }
}
```
