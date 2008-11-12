<?php
/**
 * Base test cache for cache tests
 *
 * @version $Revision: 589 $
 * @license GPLv3
 */

/**
 * Base test case for cache tests, handling the creation and removel of
 * temporary test directories.
 */
class vcsTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Temporary directory for cache contents
     * 
     * @var string
     */
    protected $tempDir;

    /**
     * Create a unique temporary directory for cache contents.
     * 
     * @return void
     */
    public function setUp()
    {
        do {
            $path = __DIR__ . '/tmp/cache_' . substr( md5( microtime() ), 0, 8 );
        } while ( is_dir( $path ) || file_exists( $path ) );

        mkdir( $this->tempDir = $path, 0777, true );
    }

    /**
     * Remove directory
     *
     * Delete the given directory and all of its contents recusively.
     * 
     * @param string $dir 
     * @return void
     */
    protected function removeRecusrively( $dir )
    {
        $directory = dir( $dir );
        while ( ( $path = $directory->read() ) !== false )
        {
            if ( ( $path === '.' ) ||
                 ( $path === '..' ) )
            {
                continue;
            }
            $path = $dir . '/' . $path;

            if ( is_dir( $path ) )
            {
                $this->removeRecusrively( $path );
            }
            else
            {
                unlink( $path );
            }
        }

        rmdir( $dir );
    }

    /**
     * Remove the temporary cache directory if the test has failed.
     * 
     * @return void
     */
    public function tearDown()
    {
        if ( !$this->hasFailed() )
        {
            $this->removeRecusrively( $this->tempDir );
        }
    }
}

