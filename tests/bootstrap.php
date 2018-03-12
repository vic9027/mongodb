<?php
/**
 * @Copyright (c) 2018, sunny-daisy.
 * All Rights Reserved.
 *
 * phpunit bootstrap.php 
 *
 * @author      wenqiang1 <wenqiang1@staff.sina.com.cn>
 * @createdate  2018-03-06
 */


if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    throw new Exception('Can not find autoload.php');
}
