readinglists-php
================

[![Latest Stable Version](https://poser.pugx.org/unikent/lib-php-readinglists/v/stable.png)](https://packagist.org/packages/unikent/lib-php-readinglists)

Full API docs available here: http://unikent.github.io/lib-php-readinglists/

PHP library for helping developers with reading lists integrations

Add this to your composer require:
 * "unikent/lib-php-readinglists": "dev-master"

Then get lists like so:
```
$api = new \unikent\ReadingLists\API();

$lists = $api->get_lists("EN902");

foreach ($lists as $list) {
    echo "---------------------------------\n";
    echo $list;
}
```
