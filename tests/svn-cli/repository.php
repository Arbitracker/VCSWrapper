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
class vcsSvnCliRepositoryTests extends vcsTestCase
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

    public function testInitializeInvalidRepository()
    {
        $repository = new vcsSvnCliRepository( $this->tempDir );

        try
        {
            $repository->initialize( 'file:///hopefully/not/existing/svn/repo' );
            $this->fail( 'vcsRpositoryInitialisationFailedException' );
        } catch ( vcsRpositoryInitialisationFailedException $e )
        { /* Expected */ }

    }

    public function testInitializeRepositoryCheckout()
    {
        $repository = new vcsSvnCliRepository( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateRepositoryCheckout()
    {
        $repository = new vcsSvnCliRepository( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );
        $repository->update();

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testGetVersionString()
    {
        $repository = new vcsSvnCliRepository( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertSame(
            "3",
            $repository->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new vcsSvnCliRepository( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertSame(
            array( "1", "2", "3" ),
            $repository->getVersions()
        );
    }

    public function testCompareVersions()
    {
        $repository = new vcsSvnCliRepository( $this->tempDir );
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
        $repository = new vcsSvnCliRepository( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        $this->assertEquals(
            'kore',
            $repository->getAuthor()
        );
    }

    public function testGetLog()
    {
        $repository = new vcsSvnCliRepository( $this->tempDir );
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
            ),
            $repository->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new vcsSvnCliRepository( $this->tempDir );
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
        $repository = new vcsSvnCliRepository( $this->tempDir );
        $repository->initialize( 'file://' . realpath( __DIR__ . '/../data/svn' ) );

        try {
            $repository->getLogEntry( "no_such_version" );
            $this->fail( 'Expected vcsNoSuchVersionException.' );
        } catch ( vcsNoSuchVersionException $e )
        { /* Expected */ }
    }
}

