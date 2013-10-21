<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\CvsCli;

/**
 * Test for the CVS cli wrapper checkout implementation.
 */
class CheckoutTest extends RepositoryBaseTest
{
    /**
     * Initializes the the meta data cache used by the CVS wrapper.
     */
    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all CVS wrappers to store metadata
        // information
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->createTempDir() );
    }

    public function testInitializeInvalidCheckout()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        try {
            $checkout->initialize( '/hopefully/not/existing/cvs#repo' );
            $this->fail( 'Expected \SystemProcess\NonZeroExitCodeException.' );
        } catch ( \SystemProcess\NonZeroExitCodeException $e ) { /* Expected */ }
    }

    public function testInitializeCheckout()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testInitializeCheckoutWithVersion()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() . '#1.2' );

        $this->assertFileExists( $this->tempDir . '/file' );
        $this->assertFileExists( $this->tempDir . '/dir1/file' );
        $this->assertFileNotExists( $this->tempDir . '/dir1/file1' );
    }

    public function testUpdateCheckout()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $this->assertFalse( $checkout->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        // Create a repository copy
        $dataDir = $this->getRepositoryPath();
        $repoDir = $this->createTempDir() . '/cvs';

        self::copyRecursive( $dataDir, $repoDir );

        // Create a clean checkout of the cloned repository
        $checkin = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir . '/in' );
        $checkin->initialize( $repoDir . '#cvs' );

        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir . '/out' );
        $checkout->initialize( $repoDir . '#cvs' );

        // Manually add a new file
        file_put_contents( $this->tempDir . '/in/foo.txt', 'Foobar Bar Foo' );

        // Add file to repository
        $add = new \Arbit\VCSWrapper\CvsCli\Process();
        $add->workingDirectory( $this->tempDir . '/in' )
            ->argument( 'add' )
            ->argument( 'foo.txt' )
            ->execute();

        $commit = new \Arbit\VCSWrapper\CvsCli\Process();
        $commit->workingDirectory( $this->tempDir . '/in' )
               ->argument( 'commit' )
               ->argument( '-m' )
               ->argument( 'Test commit...' )
               ->execute();

        // No update, actual working copy
        $this->assertFalse( $checkin->update() );

        $this->assertFileNotExists( $this->tempDir . '/out/foo.txt' );
        $this->assertTrue( $checkout->update() );
        $this->assertFileExists( $this->tempDir . '/out/foo.txt' );
    }

    public function testUpdateCheckoutToOldVersion()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );
        $this->assertFileExists( $this->tempDir . '/dir1/file', 'Expected file "/dir1/file" in checkout.' );

        $checkout->update( '1.0' );
        $this->assertFileNotExists( $this->tempDir . '/dir1/file', 'Expected file "/dir1/file" not in checkout.' );
    }

    public function testUpdateCheckoutFromTagToHead()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() . '#milestone' );

        $this->assertFileNotExists( $this->tempDir . '/dir1/file1', 'Expected file "/dir1/file1" not in checkout.' );
        $checkout->update( 'HEAD' );
        $this->assertFileExists( $this->tempDir . '/dir1/file1', 'Expected file "/dir1/file1" in checkout.' );
    }

    public function testGetCheckout()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() . '#milestone' );

        $this->assertSame(
            $checkout->get(),
            $checkout
        );

        $this->assertSame(
            $checkout->get( '/' ),
            $checkout
        );
    }

    public function testGetInvalid()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() . '#milestone' );

        try {
            $checkout->get( '/../' );
            $this->fail( 'Expected \RuntimeException.' );
        } catch ( \RuntimeException $e ) { /* Expected */ }
    }

    public function testGetDirectory()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() . '#milestone' );

        $this->assertEquals(
            $checkout->get( '/dir1' ),
            new \Arbit\VCSWrapper\CvsCli\Directory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() . '#milestone' );

        $this->assertEquals(
            $checkout->get( '/file' ),
            new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' )
        );
    }
}
