<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\Cache\MetaData;

use \Arbit\VCSWrapper\TestCase;

/**
 * Test for the SQLite cache meta data handler
 */
class SqliteTest extends TestCase
{
    public function setUp()
    {
        if ( !extension_loaded( 'sqlite3' ) ) {
            $this->markTestSkipped( 'sqlite3 extension required for this test.' );
        }

        parent::setUp();
    }

    public function testStoreCreationDate()
    {
        $cacheMetaData = new Sqlite( $this->tempDir );

        touch( $this->tempDir . ( $path = '/foo' ) );
        $cacheMetaData->created( $path, 123 );
    }

    public function testCreateCacheInNonexistingDir()
    {
        $cacheMetaData = new Sqlite( $this->tempDir . 'cache/' );

        touch( $this->tempDir . ( $path = '/foo' ) );
        $cacheMetaData->created( $path, 123 );
    }

    public function testReCreateCacheEntry()
    {
        $cacheMetaData = new Sqlite( $this->tempDir );

        touch( $this->tempDir . ( $path = '/foo' ) );
        $cacheMetaData->created( $path, 123 );
        $cacheMetaData->created( $path, 123 );
    }

    public function testUpdateAccessTime()
    {
        $cacheMetaData = new Sqlite( $this->tempDir );

        touch( $this->tempDir . ( $path = '/foo' ) );
        $cacheMetaData->created( $path, 123 );
        $cacheMetaData->accessed( $path );
    }

    public function testClearCache()
    {
        $cacheMetaData = new Sqlite( $this->tempDir );

        touch( $this->tempDir . ( $path = '/foo' ) );
        $cacheMetaData->created( $path, 123 );
        $cacheMetaData->accessed( $path );
        $cacheMetaData->cleanup( 0, 0. );

        $this->assertFalse(
            file_exists( $this->tempDir . $path ),
            'Cache file should have been purged'
        );
    }

    public function testClearOnlyFirstFile()
    {
        $cacheMetaData = new Sqlite( $this->tempDir );

        touch( $this->tempDir . ( $path1 = '/foo1' ) );
        $cacheMetaData->created( $path1, 10, 1 );
        touch( $this->tempDir . ( $path2 = '/foo2' ) );
        $cacheMetaData->created( $path2, 10, 2 );
        touch( $this->tempDir . ( $path3 = '/foo3' ) );
        $cacheMetaData->created( $path3, 10, 3 );

        $cacheMetaData->cleanup( 25, 1. );

        $this->assertFalse(
            file_exists( $this->tempDir . $path1 ),
            'Cache file 1 should have been purged'
        );

        $this->assertTrue(
            file_exists( $this->tempDir . $path2 ),
            'Cache file 2 should not have been purged'
        );

        $this->assertTrue(
            file_exists( $this->tempDir . $path3 ),
            'Cache file 3 should not have been purged'
        );
    }

    public function testUpdateAccessTimePurge()
    {
        $cacheMetaData = new Sqlite( $this->tempDir );

        touch( $this->tempDir . ( $path1 = '/foo1' ) );
        $cacheMetaData->created( $path1, 10, 1 );
        touch( $this->tempDir . ( $path2 = '/foo2' ) );
        $cacheMetaData->created( $path2, 10, 2 );
        touch( $this->tempDir . ( $path3 = '/foo3' ) );
        $cacheMetaData->created( $path3, 10, 3 );

        $cacheMetaData->accessed( $path1, 4 );

        $cacheMetaData->cleanup( 25, 1. );

        $this->assertTrue(
            file_exists( $this->tempDir . $path1 ),
            'Cache file 1 should not have been purged'
        );

        $this->assertFalse(
            file_exists( $this->tempDir . $path2 ),
            'Cache file 2 should have been purged'
        );

        $this->assertTrue(
            file_exists( $this->tempDir . $path3 ),
            'Cache file 3 should not have been purged'
        );
    }
}
