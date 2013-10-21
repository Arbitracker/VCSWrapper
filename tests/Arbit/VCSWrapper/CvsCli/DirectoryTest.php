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
        $repository = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $repository->initialize( $this->getRepository() );

        $dir = new \Arbit\VCSWrapper\CvsCli\Directory( $this->tempDir, '/' );

        $files = array();
        foreach ( $dir as $file ) {
            // Stupid, but cvs also checks out the not versions .svn folders
            if ( strpos( (string) $file, '.svn' ) === false ) {
                $files[] = (string) $file;
            }
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/',
                '/file'
            ),
            $files
        );
    }

    public function testRecursiveIterator()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $dir      = new \Arbit\VCSWrapper\CvsCli\Directory( $this->tempDir, '/' );
        $iterator = new \RecursiveIteratorIterator( $dir, \RecursiveIteratorIterator::SELF_FIRST );

        $files = array();
        foreach ( $iterator as $file ) {
            // Stupid, but cvs also checks out the not versions .svn folders
            if ( strpos( (string) $file, '.svn' ) === false ) {
                $files[] = (string) $file;
            }
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/',
                '/dir1/file',
                '/dir1/file1',
                '/file'
            ),
            $files
        );
    }

    public function testIterateSubDirContents()
    {
        $checkout = new \Arbit\VCSWrapper\CvsCli\Checkout( $this->tempDir );
        $checkout->initialize( $this->getRepository() );

        $dir = new \Arbit\VCSWrapper\CvsCli\Directory( $this->tempDir, '/dir1/' );

        $files = array();
        foreach ( $dir as $file ) {
            // Stupid, but cvs also checks out the not versions .svn folders
            if ( strpos( (string) $file, '.svn' ) === false ) {
                $files[] = (string) $file;
            }
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/file',
                '/dir1/file1',
            ),
            $files
        );
    }
}
