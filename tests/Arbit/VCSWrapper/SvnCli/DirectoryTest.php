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
class DirectoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->createTempDir() );
    }

    public function testIterateRootDirContents()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );

        $dir = new \Arbit\VCSWrapper\SvnCli\Directory( $this->tempDir, '/' );

        $files = array();
        foreach ( $dir as $file ) {
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

    public function testRecursiveIterator()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );

        $dir      = new \Arbit\VCSWrapper\SvnCli\Directory( $this->tempDir, '/' );
        $iterator = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );

        $files = array();
        foreach ( $iterator as $file ) {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/binary',
                '/dir1/',
                '/dir1/file',
                '/dir2/',
                '/file'
            ),
            $files
        );
    }

    public function testIterateSubDirContents()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );

        $dir = new \Arbit\VCSWrapper\SvnCli\Directory( $this->tempDir, '/dir1/' );

        $files = array();
        foreach ( $dir as $file ) {
            $files[] = (string) $file;
        }

        $this->assertEquals(
            array(
                '/dir1/file'
            ),
            $files
        );
    }

    public function testGetDirectoryDiff()
    {
        $repository = new \Arbit\VCSWrapper\SvnCli\Checkout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../../../data/svn' ) );
        $dir = new \Arbit\VCSWrapper\SvnCli\Directory( $this->tempDir, '/dir1/' );

        $diff = $dir->getDiff( 2 );

        $this->assertEquals(
            '/dir1/file',
            $diff[0]->from
        );
        $this->assertEquals(
            '/dir1/file',
            $diff[0]->to
        );
        $this->assertEquals(
            array(
                new \Arbit\VCSWrapper\Diff\CollectionChunk(
                    0, 1, 1, 1,
                    array(
                        new \Arbit\VCSWrapper\Diff\Line( 1, 'Some test contents' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }
}
