<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\BzrCli;

/**
 * @group bazaar
 * Test for the SQLite cache meta data handler
 */
class FileTest extends RepositoryBaseTest
{
    /**
     * Default system timezone.
     *
     * @var string
     */
    private $timezone = null;

    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->createTempDir() );

        // Store default timezone
        $this->timezone = ini_get( 'date.timezone' );

        // Test data uses US/Mountain
        ini_set( 'date.timezone', 'US/Mountain' );
    }

    public function tearDown()
    {
        // Restore system timezone
        ini_set( 'date.timezone', $this->timezone );

        parent::tearDown();
    }

    public function testGetVersionString()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        $this->assertSame(
            "2",
            $file->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        $this->assertSame(
            array(
                "1",
                "2",
            ),
            $file->getVersions()
        );
    }

    public function testGetAuthor()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            'Richard Bateman <taxilian@gmail.com>',
            $file->getAuthor()
        );
    }

    public function testGetAuthorOldVersion()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            'richard <richard@shaoden>',
            $file->getAuthor( '1' )
        );
    }

    public function testGetAuthorInvalidVersion()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        try {
            $file->getAuthor( 'invalid' );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetLog()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        $log = $file->getLog();

        $this->assertEquals(
            array(
                "1" => new \Arbit\VCSWrapper\LogEntry(
                    "1", "richard <richard@shaoden>", "Initial commit", 1276559935
                    ),
                "2" => new \Arbit\VCSWrapper\LogEntry(
                    "2", "Richard Bateman <taxilian@gmail.com>", "Second commit", 1276563712
                    ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            new \Arbit\VCSWrapper\LogEntry(
                    "1", "richard <richard@shaoden>", "Initial commit", 1276559935
            ),
            $file->getLogEntry( "1" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        try {
            $file->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetFileContents()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some other test file\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetFileBlame()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Blame(
                    'Some test file',
                    "1",
                    'richard@shaoden',
                    1276495200
                ),
                new \Arbit\VCSWrapper\Blame(
                    'Another line in the file',
                    "1",
                    'richard@shaoden',
                    1276495200
                ),
                new \Arbit\VCSWrapper\Blame(
                    "Added a new line",
                    "2",
                    "taxilian@gmail.com",
                    1276495200
                ),
            ),
            $file->blame()
        );
    }

    public function testGetFileBlameInvalidVersion()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        try {
            $file->blame( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetFileDiff()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        $diff = $file->getDiff( "1", "2" );

        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Diff\Chunk(
                    1, 2, 1, 3,
                    array(
                        new \Arbit\VCSWrapper\Diff\Line( 3, 'Some test file' ),
                        new \Arbit\VCSWrapper\Diff\Line( 3, "Another line in the file" ),
                        new \Arbit\VCSWrapper\Diff\Line( 1, 'Added a new line' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }

    public function testGetFileDiffUnknownRevision()
    {
        $repository = new \Arbit\VCSWrapper\BzrCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\BzrCli\File( $this->tempDir, '/file' );

        try {
            $diff = $file->getDiff( "8" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }
}
