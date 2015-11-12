<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

class EncryptTest extends \PHPUnit_Framework_TestCase
{

    const CRYPT_KEY = 'crypt_key';

    /**
     * @var string
     */
    protected $backendModelName = '\Magento\Framework\Encryption\Crypt';

    /**
     * @var \Magento\Framework\Encryption\Encryptor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Encryption\CryptFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cryptFactory;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configReader;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->encryptor = $this->getMock('\Magento\Framework\Encryption\Encryptor', ['encrypt'], [], '', false);

        $this->configReader = $this->getMock('\Migration\Config', ['getOption'], [], '', false);

        $this->cryptFactory = $this->getMock(
            '\Magento\Framework\Encryption\CryptFactory',
            ['create'],
            [],
            '',
            false
        );
    }

    /**
     * @param array $attributeData
     * @param array $expected
     *
     * @dataProvider dataProviderEncryptionData
     * @return void
     */
    public function testHandle($attributeData, $expected)
    {
        $fieldName = 'value';

        list($key, $dbValue, $initVector, $encryptedValue, $cypher, $mode) = array_values($attributeData);
        list($decryptedValue, $newValue) = array_values($expected);

        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMock(
            'Migration\ResourceModel\Record',
            ['getValue', 'setValue', 'getFields'],
            [],
            '',
            false
        );
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)->willReturn($dbValue);
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $newValue);
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $oppositeRecord = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();

        $crypt = $this->getMock('\Magento\Framework\Encryption\Crypt', ['decrypt'], [
            $key, $cypher, $mode, $initVector,
        ], '', true);

        $crypt->expects($this->once())
            ->method('decrypt')
            ->with(base64_decode((string)$encryptedValue))
            ->willReturn($decryptedValue);

        $this->configReader->expects($this->once())
            ->method('getOption')
            ->with(self::CRYPT_KEY)
            ->will($this->returnValue($key));

        $this->cryptFactory->expects($this->once())
            ->method('create')
            ->with([
                'key'        => $key,
                'cipher'     => $cypher,
                'mode'       => $mode,
                'initVector' => $initVector,
            ])
            ->will($this->returnValue($crypt));

        $this->encryptor->expects($this->once())
            ->method('encrypt')
            ->with($decryptedValue)
            ->willReturn($newValue);

        $handler = new \Migration\Handler\Settings\Encrypt($this->encryptor, $this->cryptFactory, $this->configReader);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }

    /**
     * @return array
     */
    public function dataProviderEncryptionData()
    {
        return [
            [
                [
                    'key'            => '7979bbf5eedb156a709cca04bcd1ab3f',
                    'dbValue'        => '0:2:4db32e3c8ef3612a:+AFOl9Rr7yTVBAUukxOQbg==',
                    'initVector'     => '4db32e3c8ef3612a',
                    'encryptedValue' => '+AFOl9Rr7yTVBAUukxOQbg==',
                    'cypher'         => MCRYPT_RIJNDAEL_128,
                    'mode'           => MCRYPT_MODE_CBC,
                ],
                [
                    'decryptedValue' => '1350644470',
                    'newValue'       => '0:2:YU9IwW5apFqebOynZBiBnKZlssuBPt8O'
                        . ':QQB7G0RlWIFMWT8hXWBgi1kZ7oUj/iQ9mII1tiGEJYE=',
                ],
            ],
            [
                [
                    'key'            => '538e855c156dcb99aa1bef633a1b98b9',
                    'dbValue'        => '2klxhuOkPMF22vf24BCruA==',
                    'initVector'     => false,
                    'encryptedValue' => '2klxhuOkPMF22vf24BCruA==',
                    'cypher'         => MCRYPT_BLOWFISH,
                    'mode'           => MCRYPT_MODE_ECB,
                ],
                [
                    'decryptedValue' => '1350644470',
                    'newValue'       => '0:2:cCsFNUtbk1yrpF1V75KA3Z2UiBLQsCgS'
                        . ':7Ed+QCz/CV8DIJlRvVIyKhAf8IBgLih/9PLlQ/AEjIg=',
                ],
            ],
        ];
    }
}
