<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision: 589 $
 * @license GPLv3
 */

/**
 * Tests for the SQLite cache meta data handler
 */
class vcsSvnCliCheckoutTests extends vcsTestCase
{
    /**
     * Return test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
	public static function suite()
	{
		return new PHPUnit_Framework_TestSuite( __CLASS__ );
	}

    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        vcsCache::initialize( $this->createTempDir() );
    }

    public function testInitializeInvalidCheckout()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );

        try
        {
            $repository->initialize( 'file:///hopefully/not/existing/svn/repo' );
            $this->fail( 'Expected pbsSystemProcessNonZeroExitCodeException.' );
        } catch ( pbsSystemProcessNonZeroExitCodeException $e )
        { /* Expected */ }

    }

    public function testInitializeCheckout()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertFalse( $repository->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    /**
    * Recursively copy a file or directory.
    *
    * Recursively copy a file or directory in $source to the given
    * destination. If a depth is given, the operation will stop, if the given
    * recursion depth is reached. A depth of -1 means no limit, while a depth
    * of 0 means, that only the current file or directory will be copied,
    * without any recursion.
    *
    * You may optionally define modes used to create files and directories.
    *
    * @throws ezcBaseFileNotFoundException
    *      If the $sourceDir directory is not a directory or does not exist.
    * @throws ezcBaseFilePermissionException
    *      If the $sourceDir directory could not be opened for reading, or the
    *      destination is not writeable.
    *
    * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
    * @license http://ez.no/licenses/new_bsd New BSD License
    * @param string $source
    * @param string $destination
    * @param int $depth
    * @param int $dirMode
    * @param int $fileMode
    * @return void
    */
    static public function copyRecursive( $source, $destination, $depth = -1, $dirMode = 0775, $fileMode = 0664 )
    {
        // Check if source file exists at all.
        if ( !is_file( $source ) && !is_dir( $source ) )
        {
            throw new ezcBaseFileNotFoundException( $source );
        }

        // Destination file should NOT exist
        if ( is_file( $destination ) || is_dir( $destination ) )
        {
            throw new ezcBaseFilePermissionException( $destination, ezcBaseFileException::WRITE );
        }

        // Skip non readable files in source directory
        if ( !is_readable( $source ) )
        {
            return;
        }

        // Copy
        if ( is_dir( $source ) )
        {
            mkdir( $destination );
            // To ignore umask, umask() should not be changed with
            // multithreaded servers...
            chmod( $destination, $dirMode );
        }
        elseif ( is_file( $source ) )
        {
            copy( $source, $destination );
            chmod( $destination, $fileMode );
        }

        if ( ( $depth === 0 ) ||
            ( !is_dir( $source ) ) )
        {
            // Do not recurse (any more)
            return;
        }

        // Recurse
        $dh = opendir( $source );
        while ( ( $file = readdir( $dh ) ) !== false )
        {
            if ( ( $file === '.' ) ||
                ( $file === '..' ) )
            {
                continue;
            }

            self::copyRecursive(
                $source . '/' . $file,
                $destination . '/' . $file,
                $depth - 1, $dirMode, $fileMode
            );
        }
    }

    public function testUpdateCheckoutWithUpdate()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );

        // Copy the repository to not chnage the test reference repository
        $repDir = $this->createTempDir() . '/svn';
        self::copyRecursive( realpath( __DIR__ . '/../data/svn' ), $repDir );
        $repository->initialize( 'file://' . $repDir );

        // Manually execute update in repository
        file_put_contents( $file = $this->tempDir . '/another', 'Some test contents' );
        $svn = new vcsSvnCliProcess();
        $svn->argument( 'add' )->argument( $file )->execute();
        $svn = new vcsSvnCliProcess();
        $svn->argument( 'commit' )->argument( $file )->argument( '-m' )->argument( '- Test commit.' )->execute();

        $this->assertTrue( $repository->update(), "Repository should have had an update available." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testGetVersionString()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertSame(
            "5",
            $repository->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertSame(
            array( "1", "2", "3", "4", "5" ),
            $repository->getVersions()
        );
    }

    public function testUpdateCheckoutToOldVersion()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );
        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );

        $repository->update( "0" );

        $this->assertFalse(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" not in checkout.'
        );
    }

    public function testCompareVersions()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertTrue(
            $repository->compareVersions( "1", "2" ) < 0
        );

        $this->assertTrue(
            $repository->compareVersions( "2", "2" ) == 0
        );

        $this->assertTrue(
            $repository->compareVersions( "3", "2" ) > 0
        );
    }

    public function testGetAuthor()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertEquals(
            'kore',
            $repository->getAuthor()
        );
    }

    public function testGetLog()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertEquals(
            array(
                1 => new vcsLogEntry(
                    '1',
                    'kore',
                    "- Added test file\n",
                    1226412609
                ),
                2 => new vcsLogEntry(
                    '2',
                    'kore',
                    "- Added some test directories\n",
                    1226412647
                ),
                3 => new vcsLogEntry(
                    '3',
                    'kore',
                    "- Renamed directory\n",
                    1226412664
                ),
                4 => new vcsLogEntry(
                    '4',
                    'kore',
                    "- Added file in subdir\n",
                    1226592944
                ),
                5 => new vcsLogEntry(
                    '5',
                    'kore',
                    "- Added another line to file\n",
                    1226595170
                ),
            ),
            $repository->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertEquals(
            new vcsLogEntry(
                '2',
                'kore',
                "- Added some test directories\n",
                1226412647
            ),
            $repository->getLogEntry( "2" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        try {
            $repository->getLogEntry( "no_such_version" );
            $this->fail( 'Expected vcsNoSuchVersionException.' );
        } catch ( vcsNoSuchVersionException $e )
        { /* Expected */ }
    }

    public function testIterateCheckoutContents()
    {
        $repository = new vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $files = array();
        foreach ( $repository as $file )
        {
            $files[] = (string) $file;
        }

        $this->assertEquals(
            array(
                '/dir1/',
                '/dir2/',
                '/file'
            ),
            $files
        );
    }
}

