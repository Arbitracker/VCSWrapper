<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\SvnCli;

use \Arbit\VCSWrapper\TestCase;

/**
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
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        $this->assertSame(
            "5",
            $file->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        $this->assertSame(
            array( "1", "5" ),
            $file->getVersions()
        );
    }

    public function testGetAuthor()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            'kore',
            $file->getAuthor()
        );
    }

    public function testGetAuthorOldVersion()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            'kore',
            $file->getAuthor( '1' )
        );
    }

    public function testGetAuthorInvalidVersion()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        try {
            $file->getAuthor( 'invalid' );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetLog()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                1 => new \Arbit\VCSWrapper\LogEntry(
                    '1',
                    'kore',
                    "- Added test file\n",
                    1226412609
                ),
                5 => new \Arbit\VCSWrapper\LogEntry(
                    '5',
                    'kore',
                    "- Added another line to file\n",
                    1226595170
                ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            new \Arbit\VCSWrapper\LogEntry(
                '1',
                'kore',
                "- Added test file\n",
                1226412609
            ),
            $file->getLogEntry( "1" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        try {
            $file->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetFileContents()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some test contents\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetFileVersionedFileContents()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            "Some test file\n",
            $file->getVersionedContent( "1" )
        );
    }

    public function testGetFileContentsInvalidVersion()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        try {
            $file->getVersionedContent( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetFileBlame()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Blame(
                    'Some test file',
                    '1',
                    'kore',
                    1226412609
                ),
                new \Arbit\VCSWrapper\Blame(
                    'A second line, in a later revision',
                    '5',
                    'kore',
                    1226595170
                ),
            ),
            $file->blame()
        );
    }

    public function testGetBinaryFileBlame()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/binary' );

        $this->assertEquals(
            false,
            $file->blame()
        );
    }

    public function testGetFileBlameInvalidVersion()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        try {
            $file->blame( "no_such_version" );
            $this->fail( 'Expected \UnexpectedValueException.' );
        } catch ( \UnexpectedValueException $e ) { /* Expected */ }
    }

    public function testGetFileDiff()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $file = new \Arbit\VCSWrapper\SvnCli\File( $this->tempDir, '/file' );

        $diff = $file->getDiff( 1 );


        $this->assertEquals(
            '/file',
            $diff[0]->from
        );
        $this->assertEquals(
            '/file',
            $diff[0]->to
        );
        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Diff\Chunk(
                    1, 1, 1, 2,
                    array(
                        new \Arbit\VCSWrapper\Diff\Line( 3, 'Some test file' ),
                        new \Arbit\VCSWrapper\Diff\Line( 1, 'A second line, in a later revision' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }
}
