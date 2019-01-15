<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Magento\Framework\App\ObjectManager;
use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;
use Migration\Exception;

/**
 * Handler to convert encoded value to value compatible with M2
 */
class Encrypt extends AbstractHandler
{
    const CRYPT_KEY = 'crypt_key';

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Encryption\CryptFactory
     */
    protected $cryptFactory;

    /**
     * @var \Migration\Config
     */
    protected $configReader;

    /**
     * @var string
     */
    protected $cryptKey;

    /**
     * Encrypt constructor.
     *
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Framework\Encryption\CryptFactory $cryptFactory
     * @param \Migration\Config $configReader
     */
    public function __construct(
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\Encryption\CryptFactory $cryptFactory,
        \Migration\Config $configReader
    ) {
        $this->cryptFactory  = $cryptFactory;
        $this->encryptor = $encryptor;
        $this->configReader = $configReader;

        $this->cryptKey = $this->configReader->getOption(self::CRYPT_KEY);
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $field = $this->field ?: 'value';
        $value  = $recordToHandle->getValue($field);
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

        $crypt = $this->cryptFactory->create([
            'key'        => $this->cryptKey,
            'cipher'     => $cypher,
            'mode'       => $mode,
            'initVector' => $initVector,
        ]);
        $decryptedValue = trim($crypt->decrypt(base64_decode((string)$encryptedValue)));
        $encodedValue = $this->encryptor->encrypt($decryptedValue);
        $recordToHandle->setValue($field, $encodedValue);
    }

    /**
     * @inheritdoc
     */
    public function validate(Record $record)
    {
        if (empty($this->cryptKey)) {
            throw new Exception("\"crypt_key\" option is not defined the configuration.");
        }

        parent::validate($record);
    }
}
