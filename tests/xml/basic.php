<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision: 589 $
 * @license GPLv3
 */

/**
 * Tests for the XML handler
 */
class vcsXmlTests extends vcsTestCase
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
     * Test if unknown users are handled correctly
     * 
     * @return void
     */
    public function testUnknownXmlFile()
    {
        try
        {
            $xml = vcsXml::loadFile( dirname( __FILE__ ) . '/../data/xml/not_existing_file.xml.xml' );
            $this->fail( 'Expected vcsNoSuchFileException.' );
        }
        catch( vcsNoSuchFileException $e )
        { /* Expected */ }
    }

    /**
     * Test XML file with parse errors
     * 
     * @return void
     */
    public function testParseErrors1()
    {
        try
        {
            $xml = vcsXml::loadFile( dirname( __FILE__ ) . '/../data/xml/broken_1.xml' );
            $this->fail( 'Expected vcsXmlParserException.' );
        }
        catch( vcsXmlParserException $e )
        { /* Expected */ }
    }

    /**
     * Test XML file with parse errors
     * 
     * @return void
     */
    public function testParseErrors2()
    {
        try
        {
            $xml = vcsXml::loadFile( dirname( __FILE__ ) . '/../data/xml/broken_2.xml' );
            $this->fail( 'Expected vcsXmlParserException.' );
        }
        catch( vcsXmlParserException $e )
        { /* Expected */ }
    }

    /**
     * Test XML file with parse errors
     * 
     * @return void
     */
    public function testParseErrors3()
    {
        try
        {
            $xml = vcsXml::loadFile( dirname( __FILE__ ) . '/../data/xml/broken_3.xml' );
            $this->fail( 'Expected vcsXmlParserException.' );
        }
        catch( vcsXmlParserException $e )
        { /* Expected */ }
    }

    /**
     * Test minimal valid XML file
     * 
     * @return void
     */
    public function testMinimalXmlFile()
    {
        $xml = vcsXml::loadFile( dirname( __FILE__ ) . '/../data/xml/minimal.xml' );

        $this->assertTrue(
            $xml instanceof vcsXml
        );
    }

    /**
     * Test minimal valid XML file
     * 
     * @return void
     */
    public function testMinimalXmlString()
    {
        $xml = vcsXml::loadString( file_get_contents( dirname( __FILE__ ) . '/../data/xml/minimal.xml' ) );

        $this->assertTrue(
            $xml instanceof vcsXml
        );
    }

    /**
     * Test XML file with text content
     * 
     * @return void
     */
    public function testTextContent()
    {
        $xml = vcsXml::loadFile( dirname( __FILE__ ) . '/../data/xml/text.xml' );

        $this->assertTrue(
            $xml instanceof vcsXmlNode
        );

        $this->assertEquals(
            "\n\tSome text\n\t\n\tMore text\n",
            (string) $xml
        );

        $this->assertEquals(
            "\n\t\tSub text\n\t",
            (string) $xml->sub[0]
        );
    }

    /**
     * Test minimal valid XML file
     * 
     * @return void
     */
    public function testXmlWithAttributes()
    {
        $xml = vcsXml::loadFile( dirname( __FILE__ ) . '/../data/xml/attributes.xml' );

        $this->assertTrue(
            $xml->element instanceof vcsXmlNodeList
        );

        $this->assertTrue(
            $xml->element[0] instanceof vcsXmlNode
        );

        $this->assertTrue(
            $xml->element[1] instanceof vcsXmlNode
        );

        $this->assertEquals(
            'value',
            $xml->element[0]['attribute']
        );

        $this->assertEquals(
            'value2',
            $xml->element[1]['attribute']
        );
    }

    /**
     * Test creation of node list from multilevel query
     * 
     * @return void
     */
    public function testMultilevelNodeListCreation()
    {
        $xml = vcsXml::loadFile( dirname( __FILE__ ) . '/../data/xml/multilevel.xml' );

        $this->assertTrue(
            $xml->section->module instanceof vcsXmlNodeList
        );

        $this->assertEquals(
            3,
            count( $xml->section->module )
        );

        $this->assertEquals(
            'mod2',
            $xml->section->module[1]['id']
        );
    }

    /**
     * Test node list iterator
     * 
     * @return void
     */
    public function testNodeListIterator()
    {
        $xml = vcsXml::loadFile( dirname( __FILE__ ) . '/../data/xml/multilevel.xml' );

        $modules = $xml->section->module;

        $ids = array( 'mod1', 'mod2', 'mod3' );

        foreach ( $modules as $nr => $module )
        {
            $this->assertEquals(
                $ids[$nr],
                $module['id']
            );
        }
    }
}

