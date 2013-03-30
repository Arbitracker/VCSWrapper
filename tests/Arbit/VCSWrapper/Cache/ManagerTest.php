<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\Cache;

use \Arbit\VCSWrapper\TestCase;

class vcsTestCacheableObject implements \Arbit\VCSWrapper\Cacheable
{
    public $foo = null;
    public function __construct( $foo )
    {
        $this->foo = $foo;
    }
    public static function __set_state( array $properties )
    {
        return new vcsTestCacheableObject( reset( $properties ) );
    }
}

/**
 * Test for the SQLite cache meta data handler
 */
class ManagerTest extends TestCase
{
    public function testCacheNotInitialized()
    {
        try {
            \Arbit\VCSWrapper\Cache\Manager::get( '/foo', 1, 'data' );
            $this->fail( 'Expected \Arbit\VCSWrapper\Cache\ManagerNotInitializedException.' );
        } catch ( \Arbit\VCSWrapper\Cache\ManagerNotInitializedException $e ) { /* Expected */ }
    }

    public function testValueNotInCache()
    {
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->tempDir, 100, .8 );
        $this->assertFalse(
            \Arbit\VCSWrapper\Cache\Manager::get( '/foo', 1, 'data' ),
            'Expected false, because item should not be in cache.'
        );
    }

    public function testCacheScalarValues()
    {
        $values = array( 1, .1, 'foo', true );
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->tempDir, 100, .8 );

        foreach ( $values as $nr => $value ) {
            \Arbit\VCSWrapper\Cache\Manager::cache( '/foo', (string) $nr, 'data', $value );
        }

        foreach ( $values as $nr => $value ) {
            $this->assertSame(
                $value,
                \Arbit\VCSWrapper\Cache\Manager::get( '/foo', $nr, 'data' ),
                'Wrong item returned from cache'
            );
        }
    }

    public function testCacheArray()
    {
        $values = array( 1, .1, 'foo', true );
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->tempDir, 100, .8 );
        \Arbit\VCSWrapper\Cache\Manager::cache( '/foo', '1', 'data', $values );

        $this->assertSame(
            $values,
            \Arbit\VCSWrapper\Cache\Manager::get( '/foo', '1', 'data' ),
            'Wrong item returned from cache'
        );
    }

    public function testInvalidCacheItem()
    {
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->tempDir, 100, .8 );

        try {
            \Arbit\VCSWrapper\Cache\Manager::cache( '/foo', '1', 'data', $this );
            $this->fail( 'Expected \RuntimeException.' );
        } catch ( \RuntimeException $e ) { /* Expected */ }
    }

    public function testCacheCacheableObject()
    {
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->tempDir, 100, .8 );
        \Arbit\VCSWrapper\Cache\Manager::cache( '/foo', '1', 'data', $object = new vcsTestCacheableObject( 'foo' ) );

        $this->assertEquals(
            $object,
            \Arbit\VCSWrapper\Cache\Manager::get( '/foo', '1', 'data' ),
            'Wrong item returned from cache'
        );
    }

    public function testPurgeOldCacheEntries()
    {
        $values = array( 1, .1, 'foo', true );
        \Arbit\VCSWrapper\Cache\Manager::initialize( $this->tempDir, 50, .8 );

        foreach ( $values as $nr => $value ) {
            \Arbit\VCSWrapper\Cache\Manager::cache( '/foo', (string) $nr, 'data', $value );
        }
        \Arbit\VCSWrapper\Cache\Manager::forceCleanup();

        $this->assertFalse(
            \Arbit\VCSWrapper\Cache\Manager::get( '/foo', 0, 'data' ),
            'Item 0 is not expected to be in the cache anymore.'
        );
        $this->assertFalse(
            \Arbit\VCSWrapper\Cache\Manager::get( '/foo', 1, 'data' ),
            'Item 1 is not expected to be in the cache anymore.'
        );
        $this->assertFalse(
            \Arbit\VCSWrapper\Cache\Manager::get( '/foo', 2, 'data' ),
            'Item 2 is not expected to be in the cache anymore.'
        );
        $this->assertTrue(
            \Arbit\VCSWrapper\Cache\Manager::get( '/foo', 3, 'data' ),
            'Item 3 is still expected to be in the cache.'
        );
    }
}
