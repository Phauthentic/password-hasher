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
namespace PasswordHasher;

use RuntimeException;

/**
 * Default password hashing class.
 */
class DefaultPasswordHasher extends AbstractPasswordHasher
{

    /**
     * Hashing algo to use. Valid values are those supported by `$algo` argument
     * of `password_hash()`. Defaults to `PASSWORD_DEFAULT`
     *
     * @var int
     */
    protected $hashType = PASSWORD_DEFAULT;

    /**
     * Associative array of options. Check the PHP manual for supported options
     * for each hash type. Defaults to empty array.
     *
     * @var array
     */
    protected $hashOptions = [];

    /**
     * Set Hash Options
     *
     * @param array $options Associative array of options. Check the PHP manual for supported options for each hash type. Defaults to empty array.
     * @return $this
     */
    public function setHashOptions(array $options): self
    {
        $this->hashOptions = $options;

        return $this;
    }

    /**
     * Sets the hash type
     *
     * @param int $type Hashing algo to use. Valid values are those supported by `$algo` argument of `password_hash()`. Defaults to `PASSWORD_DEFAULT`
     * @return $this
     */
    public function setHashType(int $type): self
    {
        $this->hashType = $type;

        return $this;
    }

    /**
     * Generates password hash.
     *
     * @param string $password Plain text password to hash.
     * @return string Password hash or false on failure.
     */
    public function hash($password): string
    {
        $hash = password_hash(
            $password,
            $this->hashType,
            $this->hashOptions
        );

        if ($hash === false) {
            throw new RuntimeException('Failed to hash password.');
        }

        return $hash;
    }

    /**
     * Check hash. Generate hash for user provided password and check against existing hash.
     *
     * @param string $password Plain text password to hash.
     * @param string $hashedPassword Existing hashed password.
     * @return bool True if hashes match else false.
     */
    public function check($password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Returns true if the password need to be rehashed, due to the password being
     * created with anything else than the passwords generated by this class.
     *
     * @param string $password The password to verify
     * @return bool
     */
    public function needsRehash(string $password): bool
    {
        return password_needs_rehash($password, $this->hashType, $this->hashOptions);
    }
}