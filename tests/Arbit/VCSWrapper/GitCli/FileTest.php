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
class FileTest extends RepositoryBaseTest
{
    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->createTempDir() );
    }

    public function testGetVersionString()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        $this->assertSame(
            "2037a8d0efd4e51a4dd84161837f8865cf7d34b1",
            $file->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        $this->assertSame(
            array(
                "43fb423f4ee079af2f3cba4e07eb8b10f4476815",
                "2037a8d0efd4e51a4dd84161837f8865cf7d34b1",
            ),
            $file->getVersions()
        );
    }

    public function testGetAuthor()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            'kore',
            $file->getAuthor()
        );
    }

    public function testGetAuthorOldVersion()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            'kore',
            $file->getAuthor( '2037a8d0efd4e51a4dd84161837f8865cf7d34b1' )
        );
    }

    public function testGetAuthorInvalidVersion()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        try {
            $file->getAuthor( 'invalid' );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetLog()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                '43fb423f4ee079af2f3cba4e07eb8b10f4476815' => new \Arbit\VCSWrapper\LogEntry(
                    "43fb423f4ee079af2f3cba4e07eb8b10f4476815", "kore", "- Added a first test file\n", 1226920616
                ),
                '2037a8d0efd4e51a4dd84161837f8865cf7d34b1' => new \Arbit\VCSWrapper\LogEntry(
                    "2037a8d0efd4e51a4dd84161837f8865cf7d34b1", "kore", "- Modified file\n", 1226921232
                ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            new \Arbit\VCSWrapper\LogEntry(
                    "2037a8d0efd4e51a4dd84161837f8865cf7d34b1", "kore", "- Modified file\n", 1226921232
            ),
            $file->getLogEntry( "2037a8d0efd4e51a4dd84161837f8865cf7d34b1" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        try {
            $file->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetFileContents()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some other test file\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetFileBlame()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Blame(
                    'Some test file',
                    '43fb423f4ee079af2f3cba4e07eb8b10f447681',
                    'kore',
                    1226920616
                ),
                new \Arbit\VCSWrapper\Blame(
                    'Another line in the file',
                    '2037a8d0efd4e51a4dd84161837f8865cf7d34b1',
                    'kore',
                    1226921232
                ),
            ),
            $file->blame()
        );
    }

    public function testGetFileBlameInvalidVersion()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        try {
            $file->blame( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetFileDiff()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        $diff = $file->getDiff( "43fb423f4ee079af2f3cba4e07eb8b10f4476815" );

        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Diff\Chunk(
                    1, 1, 1, 2,
                    array(
                        new \Arbit\VCSWrapper\Diff\Line( 3, 'Some test file' ),
                        new \Arbit\VCSWrapper\Diff\Line( 1, 'Another line in the file' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }

    public function testGetFileDiffUnknownRevision()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\GitCli\File( $this->tempDir, '/file' );

        try {
            $diff = $file->getDiff( "1" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }
}
