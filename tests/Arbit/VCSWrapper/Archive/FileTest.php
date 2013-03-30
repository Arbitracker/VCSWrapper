<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\Archive;

use \Arbit\VCSWrapper\TestCase;

/**
 * Test for the SQLite cache meta data handler
 */
class FileTest extends TestCase
{
    public function setUp()
    {
        if ( !class_exists( 'ZipArchive' ) ) {
            $this->markTestSkipped( 'Compile PHP with --enable-zip to get support for zip archive handling.' );
        }

        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->createTempDir() );
    }

    public function testGetFileContents()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( realpath( __DIR__ . '/../../../data/archive.zip' ) );
        $file = new \Arbit\VCSWrapper\Archive\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some test contents\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( realpath( __DIR__ . '/../../../data/archive.zip' ) );
        $file = new \Arbit\VCSWrapper\Archive\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetLocalFilePath()
    {
        $repository = new \Arbit\VCSWrapper\Archive\Checkout\Zip( $this->tempDir );
        $repository->initialize( realpath( __DIR__ . '/../../../data/archive.zip' ) );
        $file = new \Arbit\VCSWrapper\Archive\File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            $this->tempDir . '/dir1/file',
            $file->getLocalPath()
        );
    }
}
