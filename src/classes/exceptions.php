<?php
/**
 * PHP VCS wrapper exceptions
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
 * @package VCSWrapper
 * @subpackage Cache
 * @version $Revision: 10 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/**
 * Base exception for all exceptions inside the CVSWrapper
 */
abstract class vcsException extends Exception
{
}

/**
 * Exception thrown, when the cache is used, but not initialized.
 */
class vcsCacheNotInitializedException extends vcsException
{
    /**
     * Construct exception
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct( 'Cache has not been initialized.' );
    }
}

/**
 * Exception thrown when a value is passed to the cache, which is not
 * cacheable.
 */
class vcsNotCacheableException extends vcsException
{
    /**
     * Construct exception
     * 
     * @param mixed $value
     * @return void
     */
    public function __construct( $value )
    {
        parent::__construct( 'Value of type ' . gettype( $value ) . ' cannot be cached. Only arrays, scalar values and objects implementing vcsCacheable are allowed.' );
    }
}

/**
 * Exception thrown when the initialization of a repository failed.
 */
class vcsRpositoryInitialisationFailedException extends vcsException
{
    /**
     * Construct exception
     * 
     * @param mixed $value
     * @return void
     */
    public function __construct( $message )
    {
        parent::__construct( 'Repository initialization failed with message: ' . $message );
    }
}

/**
 * Exception thrown when the update of a repository failed.
 */
class vcsRpositoryUpdateFailedException extends vcsException
{
    /**
     * Construct exception
     * 
     * @param mixed $value
     * @return void
     */
    public function __construct( $message )
    {
        parent::__construct( 'Repository update failed with message: ' . $message );
    }
}

