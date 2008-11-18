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
     * List of all temporary directories created
     * 
     * @var array
     */
    protected $directories = array();

    /**
     * Create a unique temporary directory for cache contents.
     * 
     * @return void
     */
    public function setUp()
    {
        $this->tempDir = $this->createTempDir();
    }

    /**
     * Create a temporary directory
     *
     * Create a temporary writeable directory, which will be removed again at
     * the end of the test. The directory name is returned.
     *
     * @return string
     */
    protected function createTempDir()
    {
        do {
            $path = __DIR__ . '/tmp/cache_' . substr( md5( microtime() ), 0, 8 );
        } while ( is_dir( $path ) || file_exists( $path ) );

        mkdir( $this->directories[] = $path, 0777, true );
        return $path;
    }

    /**
     * Remove directory
     *
     * Delete the given directory and all of its contents recusively.
     * 
     * @param string $dir 
     * @return void
     */
    protected function removeRecursively( $dir )
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
                $this->removeRecursively( $path );
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
            foreach ( $this->directories as $dir )
            {
                if ( is_dir( $dir ) )
                {
                    $this->removeRecursively( $dir );
                }
            }
        }
    }
}

