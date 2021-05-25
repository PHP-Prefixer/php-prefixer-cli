<?php

/*
 * @package     PHP Prefixer REST API CLI
 *
 * @author      Desarrollos Inteligentes Virtuales, SL. <team@div.com.es>
 * @copyright   Copyright (c)2019-2021 Desarrollos Inteligentes Virtuales, SL. All rights reserved.
 * @license     MIT
 *
 * @see         https://php-prefixer.com
 */

namespace Just\AMockUp;

use Dotenv\Dotenv;
use League\Uri\Modifiers\Normalize;
use Stringy\Stringy;

/**
 * @coversNothing
 */
class OneMoreSampleClass extends TestCase
{
    public function __construct()
    {
        $test = Stringy::create('Is this a test?')->ensureRight(' Oh, yes!');
        $normalize = new Normalize();
        $dotenv = new Dotenv();

        // To be prefixed
        $thisView = view('MyView');

        // Not be prefixed
        $a = 'view';

        $arr = [];

        // To be prefixed
        $v = value('a-value');

        // Not be prefixed
        $v = $arr['value'];
    }
}
