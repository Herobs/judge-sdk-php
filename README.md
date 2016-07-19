Judge API SDK with PHP
======================
A simple judge api sdk write with php.

Install
-------
composer require herobs/judge-sdk-php

Usage
-----
```php
use Judge\Judge;

// $uri is the base judge api address, ID and SECRET is your identifier
$judge = new Judge($uri, ID, SECRET);

// add a problem
$problem = $judge->addProblem($problem);
// remove a problem
$judge->removeProblem($problem);
// add a judge record
$judge->add($record);
// query a judge record result
$judge->query($record);
```
