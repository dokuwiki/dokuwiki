Email-Address-Validator
=======================

This is a fork of [AddedBytes' EmailAddressValidator class](https://code.google.com/p/php-email-address-validation/).

## Changes ##
Changes include:

- [Composer](https://getcomposer.org/) support
- Refactored the class to be purely static
- Opened up methods for checking the "local part" (the bit before the `@`) and the "domain part" (after the `@`) 
to be public methods
- Additional code style and docblock fixing to properly follow the [PHP-FIG PSR-1](http://www.php-fig.org/psr/psr-1/) 
and [PSR-2](http://www.php-fig.org/psr/psr-2/) documents

Note that this class is still **un-namespaced** - i.e. it's still declared in the global namespace. The `composer.json` 
file is still set up to correctly load it when required, so this shouldn't be a problem in practice - it's just perhaps
not best-practice.

## Installation ##
Use [Composer](https://getcomposer.org/):
```
php composer.phar require aziraphale/email-address-validator:^2
```

If you don't want to use Composer (why not?!), just download the `EmailAddressValidator.php` file, save it with your project, and `require` it where needed.

Note that this updated version is **version 2.0.0**. I have kept the original class tagged as **version 1.0.10** (it was the 10th commit to the Google Code svn repository). If you want to use Composer to install the **old** class, simply specify `^1` as the version constraint (which will allow for backwards-compatible changes to be installed, if any get made, while never jumping to my modified class without your direct action):
```
php composer.phar require aziraphale/email-address-validator:^1
```

## Usage ##
Due to the aforementioned changes, the way of using this class has completely changed. However it has such a small and simple interface that these changes shouldn't be problematic.

As a recap, the **old usage** was like this:
```php
$validator = new EmailAddressValidator;
if ($validator->check_email_address('test@example.org')) {
    // Email address is technically valid
}
```

The **new syntax** is as follows (ensure you have already included Composer's `autoload.php` file!):
```php
if (EmailAddressValidator::checkEmailAddress("test@example.org")) {
    // Email address is technically valid
}
```

with a couple of additional methods in case they're helpful:
```php
if (EmailAddressValidator::checkLocalPortion("test")) {
    // "test" is technically a valid string to have before the "@" in an email address
}
if (EmailAddressValidator::checkDomainPotion("example.org")) {
    // "example.org" is technically a valid email address host
}
```
