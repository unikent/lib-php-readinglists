readinglists-php
================

[![Latest Stable Version](https://poser.pugx.org/unikent/readinglists-php/v/stable.png)](https://packagist.org/packages/unikent/readinglists-php)

PHP library for helping developers with reading lists integrations

Add this to your composer require:
 * "unikent/readinglists-php": "dev-master"

Then get lists like so:
```
$api = new \ReadingLists\API();

$lists = $api->get_lists("EN902");

foreach ($lists as $list) {
    echo "---------------------------------\n";
    echo $list;
}
```
