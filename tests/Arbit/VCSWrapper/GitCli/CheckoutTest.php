<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\GitCli;

/**
 * Test for the SQLite cache meta data handler
 */
class CheckoutTest extends RepositoryBaseTest
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
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );

        try {
            $repository->initialize( 'file:///hopefully/not/existing/git/repo' );
            $this->fail( 'Expected \SystemProcess\NonZeroExitCodeException.' );
        } catch ( \SystemProcess\NonZeroExitCodeException $e ) { /* Expected */ }

    }

    public function testInitializeCheckout()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertFalse( $repository->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        $this->markTestSkipped( 'Git does not allow the necessary commit anymore by default - thus we can\'t test this properly.' );

        $repDir = $this->createTempDir() . '/git';
        self::copyRecursive( realpath( __DIR__ . '/../../../../../tmp/git' ), $repDir );

        // Copy the repository to not chnage the test reference repository
        $checkin = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir . '/ci' );
        $checkin->initialize( 'file://' . $repDir );

        $checkout = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir . '/co' );
        $checkout->initialize( 'file://' . $repDir );

        // Manually execute update in repository
        file_put_contents( $this->tempDir . '/ci/another', 'Some test contents' );
        $git = new \Arbit\VCSWrapper\GitCli\Process();
        $git->workingDirectory( $this->tempDir . '/ci' );
        $git->argument( 'add' )->argument( 'another' )->execute();

        $git = new \Arbit\VCSWrapper\GitCli\Process();
        $git->workingDirectory( $this->tempDir . '/ci' );
        $git->argument( 'commit' )->argument( 'another' )->argument( '-m' )->argument( '- Test commit.' )->execute();

        $git = new \Arbit\VCSWrapper\GitCli\Process();
        $git->workingDirectory( $this->tempDir . '/ci' );
        $git->argument( 'push' )->argument( 'origin' )->execute();

        $this->assertTrue( $checkin->update(), "Checkin repository should have had an update available." );

        $this->assertFileNotExists( $this->tempDir . '/co/another' );
        $this->assertTrue( $checkout->update(), "Checkout repository should have had an update available." );
        $this->assertFileExists( $this->tempDir . '/co/another' );
    }

    public function testGetVersionString()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertSame(
            "2037a8d0efd4e51a4dd84161837f8865cf7d34b1",
            $repository->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertSame(
            array(
                "43fb423f4ee079af2f3cba4e07eb8b10f4476815",
                "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf",
                "8faf65e1c48d4908d48a647c1d23df54e1e15e85",
                "2037a8d0efd4e51a4dd84161837f8865cf7d34b1",
            ),
            $repository->getVersions()
        );
    }

    public function testUpdateCheckoutToOldVersion()
    {
        $this->markTestSkipped( 'Downgrade seems not to remove files from checkout.' );

        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $this->assertTrue(
            file_exists( $this->tempDir . '/dir1/file' ),
            'Expected file "/dir1/file" in checkout.'
        );

        $repository->update( "43fb423f4ee079af2f3cba4e07eb8b10f4476815" );

        $this->assertFalse(
            file_exists( $this->tempDir . '/dir1/file' ),
            'Expected file "/dir1/file" not in checkout.'
        );
    }

    public function testCompareVersions()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertTrue(
            $repository->compareVersions( "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf", "2037a8d0efd4e51a4dd84161837f8865cf7d34b1" ) < 0
        );

        $this->assertTrue(
            $repository->compareVersions( "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf", "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf" ) == 0
        );

        $this->assertTrue(
            $repository->compareVersions( "8faf65e1c48d4908d48a647c1d23df54e1e15e85", "43fb423f4ee079af2f3cba4e07eb8b10f4476815" ) > 0
        );
    }

    public function testGetAuthor()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            'kore',
            $repository->getAuthor()
        );
    }

    public function testGetLog()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            array(
                "43fb423f4ee079af2f3cba4e07eb8b10f4476815" => new \Arbit\VCSWrapper\LogEntry(
                    "43fb423f4ee079af2f3cba4e07eb8b10f4476815", "kore", "- Added a first test file\n", 1226920616
                ),
                "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf" => new \Arbit\VCSWrapper\LogEntry(
                    "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf", "kore", "- Added some test directories\n", 1226921143
                ),
                "8faf65e1c48d4908d48a647c1d23df54e1e15e85" => new \Arbit\VCSWrapper\LogEntry(
                    "8faf65e1c48d4908d48a647c1d23df54e1e15e85", "kore", "- Renamed directory\n", 1226921195
                ),
                "2037a8d0efd4e51a4dd84161837f8865cf7d34b1" => new \Arbit\VCSWrapper\LogEntry(
                    "2037a8d0efd4e51a4dd84161837f8865cf7d34b1", "kore", "- Modified file\n", 1226921232
                ),
            ),
            $repository->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            new \Arbit\VCSWrapper\LogEntry(
                "8faf65e1c48d4908d48a647c1d23df54e1e15e85", "kore", "- Renamed directory\n", 1226921195
            ),
            $repository->getLogEntry( "8faf65e1c48d4908d48a647c1d23df54e1e15e85" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        try {
            $repository->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testIterateCheckoutContents()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

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
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
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
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        try {
            $repository->get( '/../' );
            $this->fail( 'Expected \RuntimeException.' );
        } catch ( \RuntimeException $e ) { /* Expected */ }
    }

    public function testGetDirectory()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            $repository->get( '/dir1' ),
            new \Arbit\VCSWrapper\GitCli\Directory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            $repository->get( '/file' ),
            new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' )
        );
    }
}
