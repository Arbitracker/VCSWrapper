<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\SvnExt;

/**
 * Test for the SQLite cache meta data handler
 */
class CheckoutTest extends RepositoryBaseTest
{
    public function setUp()
    {
        if ( !extension_loaded( 'svn' ) ) {
            $this->markTestSkipped( 'Svn extension required to run this test.' );
        }

        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->createTempDir() );
    }

    public function testInitializeInvalidCheckout()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );

        try {
            // Silence error to skip PHPUnits error conversion and test custom
            // error handling.
            @$repository->initialize( 'file:///hopefully/not/existing/svn/repo' );
            $this->fail( 'Expected \Arbit\VCSWrapper\CheckoutFailedException.' );
        } catch ( \Arbit\VCSWrapper\CheckoutFailedException $e ) { /* Expected */ }

    }

    public function testInitializeCheckout()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertFalse( $repository->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        // Copy the repository to not chnage the test reference repository
        $repDir = $this->createTempDir() . '/svn';
        self::copyRecursive( $this->getRepositoryPath(), $repDir );

        // Create two repositories one for the checkin one for the test checkout
        $checkin = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir . '/ci' );
        $checkin->initialize( 'file://' . $repDir );

        $checkout = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir . '/co' );
        $checkout->initialize( 'file://' . $repDir );

        // Manually execute update in repository
        file_put_contents( $file = $this->tempDir . '/ci/another', 'Some test contents' );
        $svn = new \Arbit\VCSWrapper\SvnCli\Process();
        $svn->argument( 'add' )->argument( $file )->execute();
        $svn = new \Arbit\VCSWrapper\SvnCli\Process();
        $svn->argument( 'commit' )->argument( $file )->argument( '-m' )->argument( '- Test commit.' )->execute();

        $this->assertTrue( $checkin->update(), "Repository should have had an update available." );

        $this->assertFileNotExists( $this->tempDir . '/co/another' );
        $this->assertTrue( $checkout->update(), "Repository should have had an update available." );
        $this->assertFileExists( $this->tempDir . '/co/another' );
    }

    public function testGetVersionString()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertSame(
            "6",
            $repository->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertSame(
            array( "1", "2", "3", "4", "5", "6" ),
            $repository->getVersions()
        );
    }

    public function testUpdateCheckoutToOldVersion()
    {
        return $this->markTestSkipped( 'Update to earlier versions seems not to be supported by pecl/svn.' );

        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );

        $repository->update( "0" );

        $this->assertFalse(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" not in checkout.'
        );
    }

    public function testCompareVersions()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertTrue(
            $repository->compareVersions( "1", "2" ) < 0
        );

        $this->assertTrue(
            $repository->compareVersions( "2", "2" ) == 0
        );

        $this->assertTrue(
            $repository->compareVersions( "3", "2" ) > 0
        );
    }

    public function testGetAuthor()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            'kore',
            $repository->getAuthor()
        );
    }

    public function testGetLog()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            array(
                1 => new \Arbit\VCSWrapper\LogEntry(
                    '1',
                    'kore',
                    "- Added test file\n",
                    1226412609
                ),
                2 => new \Arbit\VCSWrapper\LogEntry(
                    '2',
                    'kore',
                    "- Added some test directories\n",
                    1226412647
                ),
                3 => new \Arbit\VCSWrapper\LogEntry(
                    '3',
                    'kore',
                    "- Renamed directory\n",
                    1226412664
                ),
                4 => new \Arbit\VCSWrapper\LogEntry(
                    '4',
                    'kore',
                    "- Added file in subdir\n",
                    1226592944
                ),
                5 => new \Arbit\VCSWrapper\LogEntry(
                    '5',
                    'kore',
                    "- Added another line to file\n",
                    1226595170
                ),
                6 => new \Arbit\VCSWrapper\LogEntry(
                    '6',
                    'kore',
                    "# Added binary to repository\n",
                    1228676322
                ),
            ),
            $repository->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            new \Arbit\VCSWrapper\LogEntry(
                '2',
                'kore',
                "- Added some test directories\n",
                1226412647
            ),
            $repository->getLogEntry( "2" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        try {
            $repository->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testIterateCheckoutContents()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $files = array();
        foreach ( $repository as $file ) {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/binary',
                '/dir1/',
                '/dir2/',
                '/file'
            ),
            $files
        );
    }

    public function testGetCheckout()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertSame(
            $repository->get(),
            $repository
        );

        $this->assertSame(
            $repository->get( '/' ),
            $repository
        );
    }

    public function testGetInvalid()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        try {
            $repository->get( '/../' );
            $this->fail( 'Expected \RuntimeException.' );
        } catch ( \RuntimeException $e ) { /* Expected */ }
    }

    public function testGetDirectory()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            $repository->get( '/dir1' ),
            new \Arbit\VCSWrapper\SvnExt\Directory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $repository = new \Arbit\VCSWrapper\SvnExt\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            $repository->get( '/file' ),
            new \Arbit\VCSWrapper\SvnExt\File( $this->tempDir, '/file' )
        );
    }
}
