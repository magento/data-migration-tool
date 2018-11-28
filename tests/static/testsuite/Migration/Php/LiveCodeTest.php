<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Test\Php;

use Magento\Framework\App\Utility;
use Magento\TestFramework\CodingStandard\Tool\CodeMessDetector;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use PHPMD\TextUI\Command;
use PHPUnit_Framework_TestCase;
use Magento\Framework\App\Utility\Files;

/**
 * Set of tests for static code analysis, e.g. code style, code complexity, copy paste detecting, etc.
 */
class LiveCodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected static $reportDir = '';

    /**
     * @var string
     */
    protected static $pathToSource = '';

    /**
     * @var string
     */
    protected static $magentoDir = '';

    /**
     * @var array
     */
    protected static $whiteList = [];

    /**
     * @var array
     */
    protected static $blackList = [];

    /**
     * Setup basics for all tests
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::$pathToSource = Utility\Files::init()->getPathToSource();
        self::$reportDir = self::$pathToSource . '/vendor/magento/data-migration-tool/tests/static/report';
        self::$magentoDir = require __DIR__ . '/../../../../../etc/magento_path.php';
        if (!is_dir(self::$reportDir)) {
            mkdir(self::$reportDir, 0770);
        }
        self::setupFileLists();
    }

    /**
     * Helper method to setup the black and white lists
     *
     * @param string $type
     * @return void
     */
    public static function setupFileLists($type = '')
    {
        if ($type != '' && !preg_match('/\/$/', $type)) {
            $type = $type . '/';
        }
        self::$whiteList = Files::init()->readLists(__DIR__ . '/_files/' . $type . 'whitelist/*.txt');
        self::$blackList = Files::init()->readLists(__DIR__ . '/_files/' . $type . 'blacklist/*.txt');
    }

    /**
     * Run the PSR2 code sniffs on the code
     *
     * @TODO: combine with testCodeStyle
     * @return void
     */
    public function testCodeStylePsr2()
    {
        $reportFile = self::$reportDir . '/phpcs_psr2_report.xml';
        $wrapper = new Wrapper();
        $codeSniffer = new CodeSniffer('PSR2', $reportFile, $wrapper);
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        if (version_compare($wrapper->version(), '1.4.7') === -1) {
            $this->markTestSkipped('PHP Code Sniffer Build Too Old.');
        }
        self::setupFileLists('phpcs');
        $result = $codeSniffer->run(self::$whiteList, self::$blackList, ['php']);
        $this->assertFileExists(
            $reportFile,
            'Expected ' . $reportFile . ' to be created by phpcs run with PSR2 standard'
        );
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): See detailed report in {$reportFile}"
        );
    }

    /**
     * Run the magento specific coding standards on the code
     *
     * @return void
     */
    public function testCodeStyle()
    {
        $reportFile = self::$reportDir . '/phpcs_report.xml';
        $wrapper = new Wrapper();
        $codeSniffer = new CodeSniffer(realpath(__DIR__ . '/_files/phpcs'), $reportFile, $wrapper);

        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        self::setupFileLists();
        $result = $codeSniffer->run(self::$whiteList, self::$blackList, ['php', 'phtml']);
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): See detailed report in {$reportFile}"
        );
    }

    /**
     * Run the annotations sniffs on the code
     *
     * @return void
     * @todo Combine with normal code style at some point.
     */
    public function testAnnotationStandard()
    {
        $reportFile = self::$reportDir . '/phpcs_annotations_report.xml';
        $wrapper = new Wrapper();
        $codeSniffer = new CodeSniffer(
            realpath(self::$magentoDir . '/dev/tests/static/framework/Magento/ruleset.xml'),
            $reportFile,
            $wrapper
        );
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        self::setupFileLists('annotation');

        $severity = 0; // Change to 5 to see the warnings
        $this->assertEquals(
            0,
            $result = $codeSniffer->run(self::$whiteList, self::$blackList, ['php'], $severity),
            "PHP Code Sniffer has found {$result} error(s): See detailed report in {$reportFile}"
        );
    }

    /**
     * Run mess detector on code
     *
     * @return void
     */
    public function testCodeMess()
    {
        $reportFile = self::$reportDir . '/phpmd_report.xml';
        $codeMessDetector = new CodeMessDetector(realpath(__DIR__ . '/_files/phpmd/ruleset.xml'), $reportFile);

        if (!$codeMessDetector->canRun()) {
            $this->markTestSkipped('PHP Mess Detector is not available.');
        }

        self::setupFileLists('phpmd');
        $this->assertEquals(
            Command::EXIT_SUCCESS,
            $codeMessDetector->run(self::$whiteList),
            "PHP Code Mess has found error(s): See detailed report in {$reportFile}"
        );

        // delete empty reports
        if (file_exists($reportFile)) {
            unlink($reportFile);
        }
    }
}
