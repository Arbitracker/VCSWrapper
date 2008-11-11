<?php
/**
 * PHP VCS wrapper SVN-Cli based repository wrapper
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
 * @subpackage Core
 * @version $Revision: 10 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/*
 * Handler for SVN repositories
 */
class vcsSvnCliRepository extends vcsRepository // implements vcsVersioned, vcsAuthored, vcsLogged
{
    /**
     * Initialize repository
     *
     * Initialize repository from the given URL. Optionally username and
     * password may be passed to the method, if required for the repository.
     *
     * @param string $url 
     * @param string $user 
     * @param string $password 
     * @return void
     */
    public function initialize( $url, $user = null, $password = null )
    {

    }

    /**
     * Check if there are updates available
     *
     * Checks if there are updates available for the repository and return the
     * avilability as a boolean state.
     * 
     * @return bool
     */
    public function hasUpdates()
    {

    }

    /**
     * Update repository
     *
     * Update the repository to the most current state. This process may
     * especially require clearing of caches.
     * 
     * @return void
     */
    public function update()
    {
        
    }
}

