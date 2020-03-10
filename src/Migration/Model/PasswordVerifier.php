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
     * @param string $hash
     * @return bool
     */
    public function verify($password, $hash)
    {
        return password_verify($password, $hash);
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
}
