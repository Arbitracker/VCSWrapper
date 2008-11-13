<?php
/**
 * PHP VCS wrapper SVN Cli directory wrapper
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
 * Directory implementation vor SVN Cli wrapper
 */
class vcsSvnCliDirectory extends vcsSvnCliResource implements vcsDirectory
{
    /**
     * Array with children ressources of the directory, used for the iterator.
     * 
     * @var array
     */
    protected $ressources;

    /**
     * @inheritdoc
     */
    public function getVersion( $version )
    {
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return current( $this->ressources );
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        return next( $this->ressources );
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return key( $this->ressources );
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return !( current( $this->ressources ) === end( $this->ressources ) ) ;
    }
    
    /**
     * @inheritdoc
     */
    public function rewind()
    {
        return reset( $this->ressources );
    }
}

