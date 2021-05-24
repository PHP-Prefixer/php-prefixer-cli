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

namespace Tests\Commands;

use Tests\TestCase;

/**
 * @coversNothing
 */
final class PrefixCommandTest extends TestCase
{
    public function testPrefix()
    {
        $this->artisan(
            'prefix',
            [
                'source-directory' => 'source-directory',
                'target-directory' => 'target-directory',
                'personal-access-token' => 'personal-access-token',
                'project-id' => 'project-id',
                '--github-access-token' => null,
            ]
        )
            ->expectsOutput('Simplicity is the ultimate sophistication.')
            ->assertExitCode(0);
    }
}
