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

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
