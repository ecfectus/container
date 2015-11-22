<?php
/**
 * Created by PhpStorm.
 * User: leemason
 * Date: 22/11/15
 * Time: 14:00
 */

namespace LeeMason\Container;


class NotFoundException extends \InvalidArgumentException implements \Interop\Container\Exception\NotFoundException
{

}