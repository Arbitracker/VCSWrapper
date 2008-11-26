<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

/**
 * Tests for the CVS cli wrapper checkout implementation.
 */
class vcsCvsCliCheckoutTests extends vcsTestCase
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
     * Initializes the the meta data cache used by the CVS wrapper.
     */
    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all CVS wrappers to store metadata
        // information
        vcsCache::initialize( $this->createTempDir() );
    }

    public function testInitializeInvalidCheckout()
    {
        $checkout = new vcsCvsCliCheckout( $this->tempDir );
        try
        {
            $checkout->initialize( '/hopefully/not/existing/cvs#repo' );
            $this->fail( 'Expected pbsSystemProcessNonZeroExitCodeException.' );
        } catch ( pbsSystemProcessNonZeroExitCodeException $e )
        { /* Expected */ }
    }

    public function testInitializeCheckout()
    {
        $checkout = new vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( realpath( __DIR__ . '/../data/cvs' ) . '#cvs' );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $checkout = new vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( realpath( __DIR__ . '/../data/cvs' ) . '#cvs' );

        $this->assertFalse( $checkout->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        // Create a repository copy
        $dataDir = realpath( __DIR__ . '/../data/cvs' );
        $repoDir = $this->createTempDir() . '/cvs';

        self::copyRecursive( $dataDir, $repoDir );

        // Create a clean checkout of the cloned repository
        $checkin = new vcsCvsCliCheckout( $this->tempDir . '/in' );
        $checkin->initialize( $repoDir . '#cvs' );

        $checkout = new vcsCvsCliCheckout( $this->tempDir . '/out' );
        $checkout->initialize( $repoDir . '#cvs' );

        // Manually add a new file
        file_put_contents( $this->tempDir . '/in/foo.txt', 'Foobar Bar Foo' );

        // Add file to repository
        $add = new vcsCvsCliProcess();
        $add->workingDirectory( $this->tempDir . '/in' )
            ->argument( 'add' )
            ->argument( 'foo.txt' )
            ->execute();

        $commit = new vcsCvsCliProcess();
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
        $checkout = new vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( realpath( __DIR__ . '/../data/cvs' ) . '#cvs' );
        $this->assertFileExists( $this->tempDir . '/dir1/file', 'Expected file "/dir1/file" in checkout.' );

        $checkout->update( '1.0' );
        $this->assertFileNotExists( $this->tempDir . '/dir1/file', 'Expected file "/dir1/file" not in checkout.' );
    }

    public static function copyRecursive($source, $target)
    {
        // Create target directory
        mkdir( $target, 0755 );

        /* @var $fileInfo SplFileInfo */
        foreach ( new DirectoryIterator( $source ) as $fileInfo )
        {
            if ( $fileInfo->isDot() === true || $fileInfo->isReadable() === false )
            {
                continue;
            }
            $pathName = $target . '/' . $fileInfo->getFilename();
            if ( $fileInfo->isDir() === true )
            {
                self::copyRecursive( $fileInfo->getPathname(), $pathName );
            }
            else if ( $fileInfo->isFile() === true )
            {
                copy( $fileInfo->getPathname(), $pathName );
                chmod( $pathName, 0644 );
            }
        }
    }
}
