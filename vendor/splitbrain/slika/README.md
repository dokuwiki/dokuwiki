# Slika - simple image handling for PHP

This is a library that covers only the bare basics you need when handling images:

  * resizing
  * cropping
  * rotation

It can use either PHP's libGD or a locally installed ImageMagick binary.

## Installation

Use composer

    composer require splitbrain/slika

## Usage

Simply get an Adapter from the Slika factory, run some operations on it and call `save`.

Operations can be chained together. Consider the chain to be one command. Do not reuse the adapter returned by `run()`, it is a single use object. All operations can potentially throw a `\splitbrain\slika\Exception`.

Options (see below) can be passed as a second parameter to the `run` factory. 

```php
use \splitbrain\slika\Slika;
use \splitbrain\slika\Exception;

$options = [
    'quality' => 75
];

try {
    Slika::run('input.png', $options)
        ->resize(500,500)
        ->rotate(Slika::ROTATE_CCW
        ->save('output.jpg', 'jpg');
} catch (Exception $e) {
    // conversion went wrong, handle it
}
```

Please also check the [API Docs](https://splitbrain.github.io/slika/) for details.

## Operations 

### resize

All resize operations will keep the original aspect ratio of the image. There will be no distortion.

Keeping either width or height at zero will auto calculate the value for you.

```php
# fit the image into a bounding box of 500x500 pixels
Slika::run('input.jpg')->resize(500,500)->save('output.png', 'png');

# adjust the image to a maximum width of 500 pixels 
Slika::run('input.jpg')->resize(500,0)->save('output.png', 'png');

# adjust the image to a maximum height of 500 pixels 
Slika::run('input.jpg')->resize(0,500)->save('output.png', 'png');
```

### crop

Similar to resizing, but this time the image will be cropped to fit the new aspect ratio.

```php
Slika::run('input.jpg')->crop(500,500)->save('output.png', 'png');
```

### rotate

Rotates the image. The parameter passed is one of the EXIF orientation flags:

![orientation flags](https://i.stack.imgur.com/BFqgu.gif)

For your convenience there are three Constants defined:


* `Slika::ROTATE_CCW` counter clockwise rotation
* `Slika::ROTATE_CW` clockwise rotation
* `Slika::ROTATE_TOPDOWN` full 180 degree rotation 

```php
Slika::run('input.jpg')->rotate(Slika::ROTATE_CW)->save('output.png', 'png');
```

### autorotate

Rotates the image according to the EXIF rotation tag if found.

```php
Slika::run('input.jpg')->autorotate()->save('output.png', 'png');
```

## Options

Options can be passed as associatiave array as the second parameter in `Slika::run`.

The following options are availble currently:

| Option      | Default            | Description                                |
|-------------|--------------------|--------------------------------------------|
| `imconvert` | `/usr/bin/convert` | The path to ImageMagick's `convert` binary |
| `quality`   | `92`               | The quality when writing JPEG images       |
