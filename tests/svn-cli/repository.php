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
}

