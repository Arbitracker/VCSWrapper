<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\BzrCli;

use \Arbit\VCSWrapper\TestCase;

/**
 * @group bazaar
 * Test for the SQLite cache meta data handler
 */
class CheckoutTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->createTempDir() );
    }

    public function testInitializeInvalidCheckout()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );

        try {
            $repository->initialize( 'file:///hopefully/not/existing/bzr/repo' );
            $this->fail( 'Expected \SystemProcess\NonZeroExitCodeException.' );
        } catch ( \SystemProcess\NonZeroExitCodeException $e ) { /* Expected */ }

    }

    public function testInitializeCheckout()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $this->assertFalse( $repository->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        $repDir = $this->createTempDir() . '/bzr';
        self::copyRecursive( realpath( __DIR__ . '/../../../data/bzr' ), $repDir );

        // Copy the repository to not change the test reference repository
        $checkin = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir . '/ci' );
        $checkin->initialize( 'file://' . $repDir );

        $checkout = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir . '/co' );
        $checkout->initialize( 'file://' . $repDir );

        // Manually execute update in repository
        file_put_contents( $this->tempDir . '/ci/another', 'Some test contents' );
        $bzr = new \Arbit\VCSWrapper\BzrCli\Process();
        $bzr->nonZeroExitCodeException = true;
        $bzr->workingDirectory( $this->tempDir . '/ci' );
        $bzr->argument( 'add' )->argument( 'another' )->execute();

        $bzr = new \Arbit\VCSWrapper\BzrCli\Process();
        $bzr->nonZeroExitCodeException = true;
        $bzr->workingDirectory( $this->tempDir . '/ci' );
        $bzr->argument( 'commit' )->argument( 'another' )->argument( '-m' )->argument( 'Test commit.' )->execute();

        $this->assertTrue( $checkin->update(), "Checkin repository should have had an update available." );

        $this->assertFileNotExists( $this->tempDir . '/co/another' );
        $this->assertTrue( $checkout->update(), "Checkout repository should have had an update available." );
        $this->assertFileExists( $this->tempDir . '/co/another' );
    }

    public function testGetVersionString()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $this->assertSame(
            "2",
            $repository->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $this->assertSame(
            array(
                "1",
                "2",
            ),
            $repository->getVersions()
        );
    }

    public function testUpdateCheckoutToOldVersion()
    {
#        $this->markTestSkipped( 'Downgrade seems not to remove files from checkout.' );

        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );
        $this->assertTrue(
            file_exists( $this->tempDir . '/dir1/file' ),
            'Expected file "/dir1/file" in checkout.'
        );

        $repository->update( "0" );

        $this->assertFalse(
            file_exists( $this->tempDir . '/dir1/file' ),
            'Expected file "/dir1/file" not in checkout.'
        );
    }

    public function testGetAuthor()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $this->assertEquals(
            'Richard Bateman <taxilian@gmail.com>',
            $repository->getAuthor()
        );
    }

    public function testGetLog()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $this->assertEquals(
            array(
                "1" => new \Arbit\VCSWrapper\LogEntry(
                    "1", "richard <richard@shaoden>", "Initial commit", 1276559935
                    ),
                "2" => new \Arbit\VCSWrapper\LogEntry(
                    "2", "Richard Bateman <taxilian@gmail.com>", "Second commit", 1276563712
                    ),
            ),
            $repository->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $this->assertEquals(
            new \Arbit\VCSWrapper\LogEntry(
                "1", "richard <richard@shaoden>", "Initial commit", 1276559935
            ),
            $repository->getLogEntry( "1" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        try {
            $repository->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testIterateCheckoutContents()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

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
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

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
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $repository->get( '/../' );
    }

    public function testGetDirectory()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $this->assertEquals(
            $repository->get( '/dir1' ),
            new \Arbit\VCSWrapper\BzrCli\Directory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/bzr' ) );

        $this->assertEquals(
            $repository->get( '/file' ),
            new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' )
        );
    }
}
