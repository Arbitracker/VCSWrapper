<?php
/**
 * PHP VCS wrapper SVN Cli resource wrapper
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
 * @subpackage SvnCliWrapper
 * @version $Revision: 10 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/*
 * Resource implementation vor SVN Cli wrapper
 */
class vcsSvnCliResource extends vcsResource implements vcsVersioned, vcsAuthored, vcsLogged
{
    /**
     * @inheritdoc
     */
    public function getVersionString()
    {
    }

    /**
     * @inheritdoc
     */
    public function getVersions()
    {
    }

    /**
     * @inheritdoc
     */
    public function getVersion( $version )
    {
    }

    /**
     * @inheritdoc
     */
    public static function compareVersions( $version1, $version2 )
    {
    }

    /**
     * @inheritdoc
     */
    public function getAuthor( $version = null )
    {
    }

    /**
     * @inheritdoc
     */
    public function getLog()
    {
    }

    /**
     * @inheritdoc
     */
    public function getLogEntry( $version )
    {
    }
}

