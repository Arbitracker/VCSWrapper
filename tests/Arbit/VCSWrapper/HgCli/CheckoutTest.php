<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision: 955 $
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\HgCli;

/**
 * @group mercurial
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
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );

        try {
            $repository->initialize( 'file:///hopefully/not/existing/hg/repo' );
            $this->fail( 'Expected \SystemProcess\NonZeroExitCodeException.' );
        } catch ( \SystemProcess\NonZeroExitCodeException $e ) { /* Expected */ }

    }

    public function testInitializeCheckout()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertFalse( $repository->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        $repDir = $this->createTempDir() . '/hg';
        self::copyRecursive( $this->getRepositoryPath(), $repDir );

        // Copy the repository to not chnage the test reference repository
        $checkin = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir . '/ci' );
        $checkin->initialize( 'file://' . $repDir );

        $checkout = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir . '/co' );
        $checkout->initialize( 'file://' . $repDir );

        // Manually execute update in repository
        file_put_contents( $this->tempDir . '/ci/another', 'Some test contents' );
        $hg = new \Arbit\VCSWrapper\HgCli\Process();
        $hg->workingDirectory( $this->tempDir . '/ci' );
        $hg->argument( 'add' )->argument( 'another' )->execute();

        $hg = new \Arbit\VCSWrapper\HgCli\Process();
        $hg->workingDirectory( $this->tempDir . '/ci' );
        $hg->argument( 'commit' )->argument( 'another' )->argument( '-m' )->argument( 'Test commit.' )->execute();

        $hg = new \Arbit\VCSWrapper\HgCli\Process();
        $hg->workingDirectory( $this->tempDir . '/ci' );
        $hg->argument( 'push' )->execute();

        $this->assertTrue( $checkin->update(), "Checkin repository should have had an update available." );

        $this->assertFileNotExists( $this->tempDir . '/co/another' );
        $this->assertTrue( $checkout->update(), "Checkout repository should have had an update available." );
        $this->assertFileExists( $this->tempDir . '/co/another' );
    }

    public function testGetVersionString()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertSame(
            "b8ec741c8de1e60c5fedd98c350e3569c46ed630",
            $repository->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertSame(
            array(
                "9923e3bfe735ad54d67c38351400097e25aadabd",
                "04cae3af7ea2c880d7f70fab0583476dfc31e7ae",
                "662e49b777be9ee47ab924c02ae2da863d32536a",
                "b8ec741c8de1e60c5fedd98c350e3569c46ed630",
            ),
            $repository->getVersions()
        );
    }

    public function testUpdateCheckoutToOldVersion()
    {
#        $this->markTestSkipped( 'Downgrade seems not to remove files from checkout.' );

        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $this->assertTrue(
            file_exists( $this->tempDir . '/dir1/file' ),
            'Expected file "/dir1/file" in checkout.'
        );

        $repository->update( "9923e3bfe735ad54d67c38351400097e25aadabd" );

        $this->assertFalse(
            file_exists( $this->tempDir . '/dir1/file' ),
            'Expected file "/dir1/file" not in checkout.'
        );
    }

    public function testCompareVersions()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertTrue(
            $repository->compareVersions( "04cae3af7ea2c880d7f70fab0583476dfc31e7ae", "b8ec741c8de1e60c5fedd98c350e3569c46ed630" ) < 0
        );

        $this->assertTrue(
            $repository->compareVersions( "04cae3af7ea2c880d7f70fab0583476dfc31e7ae", "04cae3af7ea2c880d7f70fab0583476dfc31e7ae" ) == 0
        );

        $this->assertTrue(
            $repository->compareVersions( "662e49b777be9ee47ab924c02ae2da863d32536a", "9923e3bfe735ad54d67c38351400097e25aadabd" ) > 0
        );
    }

    public function testGetAuthor()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            't.tom',
            $repository->getAuthor()
        );
    }

    public function testGetLog()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            array(
                "9923e3bfe735ad54d67c38351400097e25aadabd" => new \Arbit\VCSWrapper\LogEntry(
                    "9923e3bfe735ad54d67c38351400097e25aadabd", "t.tom", "- Added a first test file", 1263330480
                ),
                "04cae3af7ea2c880d7f70fab0583476dfc31e7ae" => new \Arbit\VCSWrapper\LogEntry(
                    "04cae3af7ea2c880d7f70fab0583476dfc31e7ae", "t.tom", "- Added some test directories", 1263330600
                ),
                "662e49b777be9ee47ab924c02ae2da863d32536a" => new \Arbit\VCSWrapper\LogEntry(
                    "662e49b777be9ee47ab924c02ae2da863d32536a", "t.tom", "- Renamed directory", 1263330600
                ),
                "b8ec741c8de1e60c5fedd98c350e3569c46ed630" => new \Arbit\VCSWrapper\LogEntry(
                    "b8ec741c8de1e60c5fedd98c350e3569c46ed630", "t.tom", "- Modified file", 1263330660
                ),
            ),
            $repository->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            new \Arbit\VCSWrapper\LogEntry(
                "662e49b777be9ee47ab924c02ae2da863d32536a", "t.tom", "- Renamed directory", 1263330600
            ),
            $repository->getLogEntry( "662e49b777be9ee47ab924c02ae2da863d32536a" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        try {
            $repository->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testIterateCheckoutContents()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
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
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
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
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        try {
            $repository->get( '/../' );
            $this->fail( 'Expected \RuntimeException.' );
        } catch ( \RuntimeException $e ) { /* Expected */ }
    }

    public function testGetDirectory()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            $repository->get( '/dir1' ),
            new \Arbit\VCSWrapper\HgCli\Directory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $this->assertEquals(
            $repository->get( '/file' ),
            new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' )
        );
    }
}
