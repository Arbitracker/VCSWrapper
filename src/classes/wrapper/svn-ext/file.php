<?php
/**
 * PHP VCS wrapper SVN Ext file wrapper
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
 * @subpackage SvnExtWrapper
 * @version $Revision: 10 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/*
 * File implementation vor SVN Ext wrapper
 */
class vcsSvnExtFile extends vcsSvnExtResource implements vcsFile, vcsFetchable, vcsDiffable
{
    /**
     * @inheritdoc
     */
    public function getContents()
    {
        return file_get_contents( $this->root . $this->path );
    }

    /**
     * @inheritdoc
     */
    public function getMimeType()
    {
        $mimeType = $this->getResourceProperty( 'mime-type' );

        if ( !empty( $mimeType ) )
        {
            return $mimeType;
        }

        // If not set, fall back to application/octet-stream
        return 'application/octet-stream';
    }

    /**
     * @inheritdoc
     */
    public function getVersionedContent( $version )
    {
        if ( !in_array( $version, $this->getVersions(), true ) )
        {
            throw new vcsNoSuchVersionException( $this->path, $version );
        }

        if ( ( $content = vcsCache::get( $this->path, $version, 'content' ) ) === false )
        {
            // Execute command
            $content = svn_cat( $this->root . $this->path, $version );
            vcsCache::cache( $this->path, $version, 'content', $content );
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function getDiff( $version, $current = null )
    {
        $current = ( $current === null ) ? $this->getVersionString() : $current;

        if ( ( $diff = vcsCache::get( $this->path, $version, 'diff' ) ) === false )
        {
            list( $diffStream, $errors ) = svn_diff( $this->root . $this->path, $version, $this->root . $this->path, $current );
            $diffContents = '';
            while ( !feof( $diffStream ) )
            {
                $diffContents .= fread( $diffStream, 8192);
            }
            fclose( $diffStream );

            // Execute command
            $parser = new vcsUnifiedDiffParser();
            $diff   = $parser->parseString( $diffContents );
            vcsCache::cache( $this->path, $version, 'diff', $diff );
        }

        return $diff;
    }
}

