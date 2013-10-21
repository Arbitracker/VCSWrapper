<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\CvsCli;

/**
 * Test for the CVS Cli wrapper
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
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' );
        $this->assertEquals( '1.2', $file->getVersionString() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file' );
        $this->assertEquals( '1.3', $file->getVersionString() );
    }

    public function testGetVersions()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' );
        $this->assertSame( array( '1.1', '1.2' ), $file->getVersions()  );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file' );
        $this->assertSame( array( '1.1', '1.2', '1.3' ), $file->getVersions()  );
    }

    public function testCompareVersions()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' );

        $this->assertEquals( 0, $file->compareVersions( '1.1', '1.1' ) );
        $this->assertLessThan( 0, $file->compareVersions( '1.1', '1.2' ) );
        $this->assertGreaterThan( 0, $file->compareVersions( '1.3', '1.2' ) );
    }

    public function testGetAuthor()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' );
        $this->assertEquals( 'manu', $file->getAuthor() );
    }

    public function testGetAuthorWithVersion()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' );
        $this->assertEquals( 'manu', $file->getAuthor( '1.1' ) );
    }

    public function testGetAuthorWithInvalidVersion()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' );
        $this->setExpectedException('\UnexpectedValueException');
        $file->getAuthor( '1.10' );
    }

    public function testGetLog()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );
        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            array(
                '1.1' => new \Arbit\VCSWrapper\LogEntry(
                    '1.1',
                    'manu',
                    '- Added file in subdir',
                    1227507833
                ),
                '1.2' => new \Arbit\VCSWrapper\LogEntry(
                    '1.2',
                    'manu',
                    '- A',
                    1227804262
                ),
                '1.3' => new \Arbit\VCSWrapper\LogEntry(
                    '1.3',
                    'manu',
                    '- Test file modified.',
                    1227804446
                ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' );
        $this->assertEquals(
            new \Arbit\VCSWrapper\LogEntry(
                '1.2',
                'manu',
                '- Added another line to file',
                1227507961
            ),
            $file->getLogEntry( '1.2' )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' );

        $this->setExpectedException( '\UnexpectedValueException' );

        $file->getLogEntry( "no_such_version" );
    }

    public function testGetFileContents()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file1' );
        $this->assertEquals( "Another test file\n", $file->getContents() );
    }

    public function testGetFileMimeType()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file1' );
        $this->assertEquals( 'application/octet-stream', $file->getMimeType() );
    }

    public function testGetFileVersionedFileContents()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file' );
        $this->assertEquals( "Some test contents\n", $file->getVersionedContent( '1.1' ) );
    }

    public function testGetFileContentsInvalidVersion()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/file' );
        $this->setExpectedException( '\UnexpectedValueException' );
        $file->getVersionedContent( 'no_such_version' );
    }

    public function testGetFileBlame()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file' );
        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Blame(
                    'Some test contents',
                    '1.1',
                    'manu',
                    1227481200
                ),
                new \Arbit\VCSWrapper\Blame(
                    'More test contents',
                    '1.2',
                    'manu',
                    1227740400
                ),
                new \Arbit\VCSWrapper\Blame(
                    'And another test line',
                    '1.3',
                    'manu',
                    1227740400
                ),
            ),
            $file->blame()
        );
    }

    public function testGetFileBlameWithVersion()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file' );
        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Blame(
                    'Some test contents',
                    '1.1',
                    'manu',
                    1227481200
                ),
            ),
            $file->blame( '1.1' )
        );
    }

    public function testGetFileBlameWithInvalidVersion()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file' );
        $this->setExpectedException( '\UnexpectedValueException' );
        $file->blame( 'no_such_version' );
    }

    public function testGetFileDiff()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $file = new \Arbit\VCSWrapper\CvsCli\File( $this->tempDir, '/dir1/file' );
        $diff = $file->getDiff( '1.1' );

        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Diff\Chunk(
                    1, 1, 1, 3,
                    array(
                        new \Arbit\VCSWrapper\Diff\Line( 3, 'Some test contents' ),
                        new \Arbit\VCSWrapper\Diff\Line( 1, 'More test contents' ),
                        new \Arbit\VCSWrapper\Diff\Line( 1, 'And another test line' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }
}
