<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision: 589 $
 * @license GPLv3
 */

/**
 * Tests for the XML xml serialization
 */
class vcsXmlSerializeTests extends vcsTestCase
{
    /**
     * Return test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    /**
     * Test minimal configuration serialization
     * 
     * @return void
     */
    public function testMinimalSerilization()
    {
        $xml = vcsXml::loadFile( __DIR__ . '/../data/xml/minimal.xml' );

        // This result should come out of the cache.
        eval( '$cachedXml = ' . var_export( $xml, true ) . ';' );

        $this->assertTrue( $cachedXml instanceof vcsXml );
        $this->assertEquals( $xml, $cachedXml );
    }

    /**
     * Test example configuration serialization
     * 
     * @return void
     */
    public function testExampleSerilization()
    {
        $xml = vcsXml::loadFile( __DIR__ . '/../data/xml/example.xml' );

        // This result should come out of the cache.
        eval( '$cachedXml = ' . var_export( $xml, true ) . ';' );

        $this->assertTrue( $cachedXml instanceof vcsXml );
        $this->assertEquals( $xml, $cachedXml );
    }
}

