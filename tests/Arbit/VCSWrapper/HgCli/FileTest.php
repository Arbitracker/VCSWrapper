<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision: 955 $
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\HgCli;

use \Arbit\VCSWrapper\TestCase;

/**
 * @group mercurial
 * Test for the SQLite cache meta data handler
 */
class FileTest extends TestCase
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
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        $this->assertSame(
            "b8ec741c8de1e60c5fedd98c350e3569c46ed630",
            $file->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        $this->assertSame(
            array(
                "9923e3bfe735ad54d67c38351400097e25aadabd",
                "b8ec741c8de1e60c5fedd98c350e3569c46ed630",
            ),
            $file->getVersions()
        );
    }

    public function testGetAuthor()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            't.tom',
            $file->getAuthor()
        );
    }

    public function testGetAuthorOldVersion()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            't.tom',
            $file->getAuthor( 'b8ec741c8de1e60c5fedd98c350e3569c46ed630' )
        );
    }

    public function testGetAuthorInvalidVersion()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        try {
            $file->getAuthor( 'invalid' );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetLog()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                '9923e3bfe735ad54d67c38351400097e25aadabd' => new \Arbit\VCSWrapper\LogEntry(
                    "9923e3bfe735ad54d67c38351400097e25aadabd", "t.tom", "- Added a first test file", 1263330480
                ),
                'b8ec741c8de1e60c5fedd98c350e3569c46ed630' => new \Arbit\VCSWrapper\LogEntry(
                    "b8ec741c8de1e60c5fedd98c350e3569c46ed630", "t.tom", "- Modified file", 1263330660
                ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            new \Arbit\VCSWrapper\LogEntry(
                    "b8ec741c8de1e60c5fedd98c350e3569c46ed630", "t.tom", "- Modified file", 1263330660
            ),
            $file->getLogEntry( "b8ec741c8de1e60c5fedd98c350e3569c46ed630" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        try {
            $file->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetFileContents()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some other test file\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetFileBlame()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Blame(
                    'Some test file',
                    '9923e3bfe735ad54d67c38351400097e25aadabd',
                    't.tom',
                    1263330521
                ),
                new \Arbit\VCSWrapper\Blame(
                    'Another line in the file',
                    'b8ec741c8de1e60c5fedd98c350e3569c46ed630',
                    't.tom',
                    1263330677
                ),
            ),
            $file->blame()
        );
    }

    public function testGetFileBlameInvalidVersion()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        try {
            $file->blame( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetFileDiff()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        $diff = $file->getDiff( "9923e3bfe735ad54d67c38351400097e25aadabd" );

        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Diff\CollectionChunk(
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
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/hg' ) );
        $file = new \Arbit\VCSWrapper\HgCli\File( $this->tempDir, '/file' );

        try {
            $diff = $file->getDiff( "1" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }
}
