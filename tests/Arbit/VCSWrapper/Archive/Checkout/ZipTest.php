<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\Archive\Checkout;

use \Arbit\VCSWrapper\TestCase;

/**
 * Test for the SQLite cache meta data handler
 */
class ZipTest extends TestCase
{
    public function setUp()
    {
        if ( !class_exists( 'ZipArchive' ) ) {
            $this->markTestSkipped( 'Compile PHP with --enable-zip to get support for zip archive handling.' );
        }

        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->createTempDir() );
    }

    public function testInitializeInvalidCheckout()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );

        try {
            $repository->initialize( 'file:///hopefully/not/existing/svn/repo' );
            $this->fail( 'Expected \RuntimeException.' );
        } catch ( \RuntimeException $e ) { /* Expected */ }

    }

    public function testInitializeInvalidArchive()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );

        try {
            $repository->initialize( __FILE__ );
            $this->fail( 'Expected \RuntimeException.' );
        } catch ( \RuntimeException $e ) { /* Expected */ }

    }

    public function testInitializeCheckout()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( __DIR__ . '/../../../../data/archive.zip' );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( realpath( __DIR__ . '/../../../../data/archive.zip' ) );

        $this->assertFalse( $repository->update(), "There are never updates available for archive checkouts." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testIterateCheckoutContents()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( realpath( __DIR__ . '/../../../../data/archive.zip' ) );

        $files = array();
        foreach ( $repository as $file ) {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/',
                '/dir2/',
                '/file'
            ),
            $files
        );
    }

    public function testGetCheckout()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( realpath( __DIR__ . '/../../../../data/archive.zip' ) );

        $this->assertSame(
            $repository->get(),
            $repository
        );

        $this->assertSame(
            $repository->get( '/' ),
            $repository
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetInvalid()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( realpath( __DIR__ . '/../../../../data/archive.zip' ) );

        $repository->get( '/../' );
    }

    public function testGetDirectory()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( realpath( __DIR__ . '/../../../../data/archive.zip' ) );

        $this->assertEquals(
            $repository->get( '/dir1' ),
            new \Arbit\VCSWrapper\Archive\Directory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( realpath( __DIR__ . '/../../../../data/archive.zip' ) );

        $this->assertEquals(
            $repository->get( '/file' ),
            new \Arbit\VCSWrapper\Archive\File( $this->tempDir, '/file' )
        );
    }
}
