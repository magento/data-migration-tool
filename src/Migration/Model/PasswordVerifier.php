<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Model;

/**
 * Class PasswordVerifier
 */
class PasswordVerifier
{
    /**
     * Verify password
     *
     * @param string $password
     * @param string $passwordHash
     * @return bool|void
     */
    public function verify($password, $passwordHash)
    {
        $passwordHashExplode = explode(':', $passwordHash);
        $hash = $passwordHashExplode[0];
        $salt = $passwordHashExplode[1] ?? '';
        if ($this->isBcrypt($hash)) {
            return password_verify($password, $hash);
        } else if ($this->isSha512($hash)) {
            return hash('sha512', $salt . $password) === $hash;
        }
        return;
    }

    /**
     * Check if hash is Bcrypt algorithm
     *
     * @param string $hash
     * @return bool
     */
    public function isBcrypt($hash)
    {
        if (stripos($hash, '$2y$') === 0) {
            return true;
        }
        return false;
    }

    /**
     * Check if hash is sha-512 algorithm
     *
     * @param string $hash
     * @return bool
     */
    public function isSha512($hash)
    {
        $hash = explode(':', $hash)[0];
        if (strlen($hash) === 128) {
            return true;
        }
        return false;
    }
}
