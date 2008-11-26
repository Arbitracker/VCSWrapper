<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

/**
 * Tests for the CVS Cli wrapper
 */
class vcsCvsCliFileTests extends vcsTestCase
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

    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        vcsCache::initialize( $this->createTempDir() );
    }

    public function testGetVersionString()
    {
        $checkout = new vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( realpath( __DIR__ . '/../data/cvs' ) . '#cvs' );

        $file = new vcsCvsCliFile( $this->tempDir, '/file' );
        $this->assertEquals( '1.2', $file->getVersionString() );

        $file = new vcsCvsCliFile( $this->tempDir, '/dir1/file' );
        $this->assertEquals( '1.1', $file->getVersionString() );
    }

    public function testGetVersions()
    {
        $checkout = new vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( realpath( __DIR__ . '/../data/cvs' ) . '#cvs' );

        $file = new vcsCvsCliFile( $this->tempDir, '/file' );
        $this->assertSame( array( '1.1', '1.2' ), $file->getVersions()  );

        $file = new vcsCvsCliFile( $this->tempDir, '/dir/file' );
        $this->assertSame( array( '1.1' ), $file->getVersions()  );
    }
}
