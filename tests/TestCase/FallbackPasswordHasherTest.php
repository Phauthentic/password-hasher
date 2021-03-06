<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Authentication\Test\TestCase\PasswordHasher;

use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use Phauthentic\PasswordHasher\FallbackPasswordHasher;
use Phauthentic\PasswordHasher\Md5PasswordHasher;
use Phauthentic\PasswordHasher\PasswordHasherCollection;
use PHPUnit\Framework\TestCase;

/**
 * Test case for FallbackPasswordHasher
 */
class FallbackPasswordHasherTest extends TestCase
{
    /**
     * testExceptionForEmptyHasherCollection
     *
     * @return void
     */
    public function testExceptionForEmptyHasherCollection(): void
    {
        $this->expectException(\RuntimeException::class);
        $hasherCollection = new PasswordHasherCollection();
        new FallbackPasswordHasher($hasherCollection);
    }

    /**
     * Tests that only the first hasher is user for hashing a password
     *
     * @return void
     */
    public function testHash()
    {
        $legacy = new Md5PasswordHasher();
        $simple = new DefaultPasswordHasher();
        $hasherCollection = new PasswordHasherCollection([
            $legacy,
            $simple
        ]);

        $hasher = new FallbackPasswordHasher($hasherCollection);
        $this->assertSame($legacy->hash('foo'), $hasher->hash('foo'));
    }

    /**
     * Tests that the check method will check with configured hashers until a match
     * is found
     *
     * @return void
     */
    public function testCheck()
    {
        $legacy = new Md5PasswordHasher();
        $simple = new DefaultPasswordHasher();
        $hasherCollection = new PasswordHasherCollection([
            $legacy,
            $simple
        ]);
        $hasher = new FallbackPasswordHasher($hasherCollection);

        $hash = $simple->hash('foo');
        $otherHash = $legacy->hash('foo');
        $this->assertTrue($hasher->check('foo', $hash));
        $this->assertTrue($hasher->check('foo', $otherHash));
    }

    /**
     * Tests that the check method will work with configured hashers including different
     * configs per hasher.
     *
     * @return void
     */
    public function testCheckWithConfigs()
    {
        $simple = new DefaultPasswordHasher();
        $legacy = new Md5PasswordHasher();
        $collection = new PasswordHasherCollection([
            $legacy,
            $simple
        ]);
        $hasher = new FallbackPasswordHasher($collection);

        $hash = $simple->hash('foo');
        $legacyHash = $legacy->hash('foo');
        $this->assertTrue($hash !== $legacyHash);
        $this->assertTrue($hasher->check('foo', $hash));
        $this->assertTrue($hasher->check('foo', $legacyHash));
    }

    /**
     * Tests that the password only needs to be re-built according to the first hasher
     *
     * @return void
     */
    public function testNeedsRehash()
    {
        $legacy = new Md5PasswordHasher();
        $collection = new PasswordHasherCollection([
            new DefaultPasswordHasher(),
            $legacy
        ]);
        $hasher = new FallbackPasswordHasher($collection);

        $otherHash = $legacy->hash('foo');
        $this->assertTrue($hasher->needsRehash($otherHash));

        $simple = new DefaultPasswordHasher();
        $hash = $simple->hash('foo');
        $this->assertFalse($hasher->needsRehash($hash));
    }
}
