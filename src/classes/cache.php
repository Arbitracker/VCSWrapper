<?php
/**
 * PHP VCS wrapper base metadata cache handler
 *
 * This file is part of vcs-wrapper.
 *
 * vcs-wrapper is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * vcs-wrapper is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with vcs-wrapper; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/*
 * Cache handler for VCS meta data
 *
 * Basic LRU (last recently used) cache, with a storage size limitation,
 * because the amount of diskspace used for all meta data information in a big
 * repository might be very space consuming.
 *
 * The size and access information is stored in a SQLite database, if
 * available, and directly in the file system otherwise.
 *
 * The filesystem is very slow to collect the oldest files, so the cleanup will
 * not happen automatically using the file system cache meta data storage, but
 * has to be triggered manually (cron or something). With the SQLite cache
 * metadata storage this will happen automatically.
 */
class vcsCache
{
    /**
     * Cache handler instance
     *
     * @var vcsMetaDataCache
     */
    protected static $instance = null;

    /**
     * Cache path, used to store the actual cache contents
     *
     * @var string
     */
    protected static $path;

    /**
     * Cache size in bytes. A cache size lower or equal 0 is used to
     * intentionally disable the cache.
     *
     * @var int
     */
    protected static $size;

    /**
     * Cache cleanup rate.
     *
     * The clean up rate defines how much of the cache contents will be left on
     * the device, when the cache size limit has been exceeded. The default
     * rate of .8 with a cache size of 1 MBdefines, that everything except the
     * most recently used 800 kB will be purged.
     *
     * @var float
     */
    protected static $cleanupRate;

    /**
     * Cache meta data handler
     *
     * Handler to store the cache meta data, like file access time and overall
     * storage volumne.
     *
     * @var vcsCacheMetaData
     */
    protected static $metaDataHandler;

    /**
     * Private constructor
     *
     * The cache is only accessed statically and should be configured using the
     * static initialize method. Therefore this constructor is protected to not
     * be called from the outside.a
     *
     * To disable caching completely, you may set the size to a value lower or
     * equal 0.
     *
     * @param string $path 
     * @param int $size 
     * @param float $cleanupRate 
     * @return void
     */
    protected function __construct( $path, $size = 1048576, $cleanupRate = .8 )
    {
        self::$path        = (string) $path;
        self::$size        = (int)    $size;
        self::$cleanupRate = (float)  $cleanupRate;
    }

    /**
     * Initialize cache
     *
     * Initialize cache with its settings. You need to provide a path to a
     * location where the cache may store its contents.
     *
     * Optionally you may pass a different cache size limit, which defaults to
     * 1MB in bytes, and a cleanup rate. The clean up rate defines how much of
     * the cache contents will be left on the device, when the cache size limit
     * has been exceeded. The default rate of .8 with a cache size of 1
     * MBdefines, that everything except the most recently used 800 kB will be
     * purged.
     *
     * @param string $path 
     * @param int $size 
     * @param float $cleanupRate 
     * @return void
     */
    public static function initialize( $path, $size = 1048576, $cleanupRate = .8 )
    {
        self::$instance = self::__construct( $path, $size, $cleanupRate );

        // Determine meta data handler to use for caching the cache metadata.
        if ( extension_loaded( 'sqlite3' ) )
        {
            self::$metaDataHandler = new vcsCacheSqliteMetaData( self::$path );
        }
        else
        {
            self::$metaDataHandler = new vcsCacheFileSystemMetaData( self::$path );
        }
    }

    /**
     * Get value from cache
     *
     * Get the metadata, identified by the $key from the cache, for the given
     * resource in the given version.
     *
     * This method returns false, if the item does not yet exist in the cache,
     * and the cached value otherwise.
     *
     * @param string $resource 
     * @param string $version 
     * @param string $key 
     * @return mixed
     */
    public static function get( $resource, $version, $key )
    {

    }

    /**
     * Cache item
     *
     * Cache the meta data, identified by the $key, for the given resource in
     * the given version. You may cache all scalar values, arrays and objects
     * which are implementing the interface vcsCacheable.
     *
     * @param string $resource 
     * @param string $version 
     * @param string $key 
     * @param mixed $value 
     * @return void
     */
    public static function cache( $resource, $version, $key, $value )
    {

    }

    /**
     * Force cache cleanup
     *
     * Force a check, if the cache currently exceeds its given size limit. If
     * this is the case this method will start cleaning up the cache.
     *
     * Depending on the used meta data storage and the size of the cache this
     * operation might take some time.
     *
     * @return void
     */
    public static function forceCleanup()
    {

    }
}

