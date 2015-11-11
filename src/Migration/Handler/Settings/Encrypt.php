<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

use Magento\Framework\App\ObjectManager;
use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Handler to convert encoded value to value compatible with M2
 */
class Encrypt extends AbstractHandler
{
    const CRYPT_KEY = 'crypt_key';

    /**
     * @var string
     */
    protected $backendModelName = '\Magento\Framework\Encryption\Crypt';

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Migration\Config
     */
    protected $configReader;

    /**
     * @var string
     */
    protected $cryptKey;

    /**
     * Encode constructor.
     *
     * @param \Magento\Framework\Encryption\Encryptor        $encryptor
     * @param \Magento\Framework\ObjectManager\ObjectManager $objectManager
     * @param \Migration\Config                              $configReader
     */
    public function __construct(
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\ObjectManager\ObjectManager $objectManager,
        \Migration\Config $configReader
    ) {
        $this->objectManager = $objectManager;
        $this->encryptor     = $encryptor;
        $this->configReader  = $configReader;

        $this->cryptKey      = $this->configReader->getOption(self::CRYPT_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $value  = $recordToHandle->getValue('value');

        if (!$value) {
            return;
        }
        $this->validate($recordToHandle);

        $parts          = explode(':', $value, 4);
        $partsCount     = count($parts);
        if ($partsCount == 4) {
            $initVector     = $parts[2];
            $encryptedValue = $parts[3];
            $mode           = MCRYPT_MODE_CBC;
            $cypher         = MCRYPT_RIJNDAEL_128;
        } else {
            $initVector     = false;
            $encryptedValue = $value;
            $mode           = MCRYPT_MODE_ECB;
            $cypher         = MCRYPT_BLOWFISH;
        }

        $crypt = $this->objectManager->create($this->backendModelName, [
            'key'        => $this->cryptKey,
            'cipher'     => $cypher,
            'mode'       => $mode,
            'initVector' => $initVector,
        ]);

        $decryptedValue = trim($crypt->decrypt(base64_decode((string)$encryptedValue)));

        $encodedValue = $this->encryptor->encrypt($decryptedValue);

        $recordToHandle->setValue('value', $encodedValue);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Record $record)
    {
        if (empty($this->cryptKey)) {
            throw new Exception("cryptKey is not defined.");
        }

        parent::validate($record);
    }
}
