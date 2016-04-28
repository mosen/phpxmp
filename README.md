# PhpXMP #

This package allows you to locate and extract XMP Metadata packets from various file formats in Pure PHP (which means
you can avoid the PECL extension).

It does not parse the contents of those XMP packets, that's up to you and there are a ton of XML parsers which will
handle that process.

## Testing ##

I am testing using the BlueSquare files supplied with Adobe's XMP Toolkit SDK. These are not in the repository due to
possible licensing concerns. You can place them into the `test_files/BlueSquare` directory to run tests.



