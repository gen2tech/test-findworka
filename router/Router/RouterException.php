<?php
/**
 * @ Package: Router - simple router class for php
 * @ Class: RouterException
 * @Author : Ogunyemi Oludayo / @gen2wind <ogunyemioludayo@gmail.com>
 * @Web    : https://github.com/gen2wind
 * @URL    : https://github.com/gen2wind/php-router
 * @Licence: The MIT License (MIT) - Copyright (c) - http://opensource.org/licenses/MIT
 */

namespace Inf\Router;

use Exception;

class RouterException
{
    /**
     * @var bool $debug Debug mode
     */
    public static $debug = false;

    /**
     * Create Exception Class.
     *
     * @param $message
     *
     * @return string
     * @throws Exception
     */
    public function __construct($message)
    {
        if (self::$debug) {
            throw new Exception($message, 1);
        } else {
            die('<h2>Opps! An error occurred.</h2> ' . $message);
        }
    }
}
