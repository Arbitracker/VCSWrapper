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
class DirectoryTest extends RepositoryBaseTest
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
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $dir = new \Arbit\VCSWrapper\HgCli\Directory( $this->tempDir, '/' );

        $files = array();
        foreach ( $dir as $file ) {
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

    public function testRecursiveIterator()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $dir      = new \Arbit\VCSWrapper\HgCli\Directory( $this->tempDir, '/' );
        $iterator = new \RecursiveIteratorIterator( $dir, \RecursiveIteratorIterator::SELF_FIRST );

        $files = array();
        foreach ( $iterator as $file ) {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/',
                '/dir1/file',
                '/dir2/',
                '/dir2/file',
                '/file'
            ),
            $files
        );
    }

    public function testIterateSubDirContents()
    {
        $repository = new \Arbit\VCSWrapper\HgCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $dir = new \Arbit\VCSWrapper\HgCli\Directory( $this->tempDir, '/dir1/' );

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
}
