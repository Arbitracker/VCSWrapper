<?php
/**
 * PHP VCS wrapper abstract directory base class
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
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/*
 * Base class for directories in the VCS wrapper.
 *
 * This class should be extended by the various wrappers to represent
 * directories in the respective VCS. In the wrapper implementations this base
 * class should be extended with interfaces annotating the VCS features beside
 * basic directory iteration.
 */
abstract class vcsRepository extends vcsDirectory
{
    /**
     * Construct repository with repository root path
     *
     * Construct the repository with the repository root path, which will be
     * used to store the repository contents.
     *
     * @param string $root 
     * @return void
     */
    public function __construct( $root )
    {
        parent::__construct( $root, '/' );
    }

    /**
     * Check if there are updates available
     *
     * Checks if there are updates available for the repository and return the
     * avilability as a boolean state.
     * 
     * @return bool
     */
    abstract public function hasUpdates();

    /**
     * Update repository
     *
     * Update the repository to the most current state. This process may
     * especially require clearing of caches.
     * 
     * @return void
     */
    abstract public function update();
}

