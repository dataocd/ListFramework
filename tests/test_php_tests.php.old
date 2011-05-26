<?php

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(__FILE__) . '/../src/test_php.php';

/**
 * Simple test class.
 *
 * @package   Example
 * @author    James Phillips <james@dataocd.com>
 * @copyright 2011 James Phillips. All rights reserved.
 * @license   TBD
 * @version   Release: 0
 * @link      http://www.dataocd.com/
 */
class Listr_Example_UnitTest extends PHPUnit_Framework_TestCase
{
    /**
     * The used object.
     *
     * @var DataOCD_Example_List $list
     */
    protected $list = null;

    /**
     * Creates a new {@link DataOCD_Example_List} object.
     */
    public function setUp()
    {
        parent::setUp();

        $this->list = new DataOCD_Example_List();
    }

    /**
     * Successful test.
     */
    public function testAddSuccess()
    {
        $this->assertEquals(4, $this->list->add(1, 3));
    }

    /**
     * Successful test.
     */
    public function testSubSuccess()
    {
        $this->assertEquals( -2, $this->list->sub( 1, 3 ) );
    }
    
    /**
     * Failing test.
     */
    public function testSubFail()
    {
        $this->assertEquals( 0, $this->list->sub( 2, 1 ) );
    }
    
    /**
     * Test case with data provider.
     *
     * @dataProvider dataProviderOne
     */
    public function testDataProviderOneWillFail( $x, $y )
    {
        $this->assertEquals( 1, $this->list->sub( $x, $y ) );
    }
    
    /**
     * Test case with data provider.
     *
     * @dataProvider dataProviderTwo
     */
    public function testDataProviderAllWillFail( $x, $y )
    {
        $this->assertEquals( 1, $this->list->sub( $x, $y ) );
    }
    
    /**
     * Failing test.
     */
    public function testFail()
    {
        $this->fail('Failed because...');
    }
    
    /**
     * Skipping test.
     */
    public function testMarkSkip()
    {
        $this->markTestSkipped('Skipped because...');
    }
    
    /**
     * Skipping test.
     */
    public function testMarkIncomplete()
    {
        $this->markTestIncomplete('Incomplete because...');
    }
    
    /**
     * Example data provider.
     *
     * @return array(array)
     */
    public static function dataProviderOne()
    {
        return array(
            array( 2, 1 ),
            array( 3, 2 ),
            array( 7, 1 ),
            array( 9, 8 ),
        );
    }
    
    /**
     * Example data provider.
     *
     * @return array(array)
     */
    public static function dataProviderTwo()
    {
        return array(
            array( 17, 42 ),
            array( 13, 23 ),
            array( 42, 17 ),
            array( 23, 13 ),
        );
    }
}
