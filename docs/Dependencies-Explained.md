# Dependencies

We think there is a need to explain why we have picked the dependencies we have, because we try to keep dependencies low. However, there are some cases that make sense to use existing libraries.

The dependencies have been selected based on the code quality and amount of further dependencies they would introduce.

## league/flysystem

The underlying storage abstraction but it's possible to replace it if you really want.

## intervention/image - optional

This **optional** library has been chosen over `league/glide` because glide has to many additional dependencies and features.

If you want image processing features out of the box please install `phauthentic/file-storage-image-processing` that will install this library as well.

## spatie/image-optimizer - optional

Same as above, this is **optional** and is part of `phauthentic/file-storage-image-processing`.
