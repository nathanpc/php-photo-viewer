# PHP Photo Viewer

A super lightweight and retro-friendly PHP 5.4 photo viewer library and website.

## Usage

Using this website is extremely simple, all that you have to do is create a
folder named `photos` and drop your photos that you want displayed there. You
can organize them into folders, which subsequently will become albums in the
gallery. The thumbnails for each photo will be generated automatically by the
application and will be stored in a folder named `thumbs`.

## Requirements

This application was built to be retro-friendly both on the server side as well
as the client side, so its requirements are quite small and their versions very
old, so you can easily host this on anything built in the last 15 years as long
as you satisfy the following requirements:

- PHP 5.4
- [Exif PHP Extension](https://www.php.net/manual/en/book.exif.php)
- [ImageMagick PHP Extension](https://www.php.net/manual/en/book.imagick.php)

## Deployment

As is tradition with PHP applications of this era you can simply drop this
repository inside a folder of your web server and it should be up and running
already. The application works both inside a VirtualHost or in a subfolder of
your web server automatically, no need to configure anything.

You must create a `photos/` and a `thumbs/` folders for the storage of photos
and their thumbnails respectively. The thumbnails are generated as needed by the
application.

The `.htaccess` that's included with the project is simply to guard against
snooping inside the `.git` folder and other development files. It's not required
for the application to run and contains nothing of importance.

There are a couple of variables you can tweak inside the `index.php` source,
feel free to do so if the defaults are not of your liking. Also feel free to
edit the layout and styling of the application as much as you want.

## Usage as a Library

The application was intended to also work as a library for embedding photo
galleries into other websites, so the code is very modular and everything is
contained in the `photo_viewer.php` source file, the `index.php` is simply a
front-end for it.

You can import the `photo_viewer.php` into your application and use it
standalone to embed a similar feature right into your own website. Use this
repository as an example, read the documentation inside the source file and you
should have everything you need to embed this feature.

If you create any additional functionality please contribute back your changes
since it helps the community.

## License

This library is free software; you may redistribute and/or modify it under the
terms of the [Mozilla Public License 2.0](https://www.mozilla.org/en-US/MPL/2.0/).
