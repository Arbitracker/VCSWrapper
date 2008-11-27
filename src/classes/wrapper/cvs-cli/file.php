<?php
/**
 * PHP VCS wrapper CVS Cli file wrapper
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
 * @subpackage CvsCliWrapper
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/*
 * File implementation vor CVS Cli wrapper
 */
class vcsCvsCliFile extends vcsCvsCliResource implements vcsFile, vcsBlameable, vcsFetchable, vcsDiffable
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
        // If not set, fall back to application/octet-stream
        return 'application/octet-stream';
    }

    /**
     * @inheritdoc
     */
    public function blame( $version = null )
    {

    }

    /**
     * @inheritdoc
     */
    public function getVersionedContent( $version )
    {
        $versions = array_merge( array( 'HEAD' ), $this->getVersions() );
        if ( !in_array( $version, $versions, true ) )
        {
            throw new vcsNoSuchVersionException( $this->path, $version );
        }

        if ( ( $content = vcsCache::get( $this->path, $version, 'content' ) ) === false )
        {
            // Refetch the basic contentrmation, and cache it.
            $process = new vcsCvsCliProcess();
            $process->workingDirectory( $this->root )
                    ->redirect( vcsCvsCliProcess::STDERR, vcsCvsCliProcess::STDOUT )
                    ->argument( 'update' )
                    ->argument( '-p' )
                    ->argument( '-r' )
                    ->argument( $version )
                    ->argument( '.' . $this->path )
                    ->execute();

            $output  = $process->stdoutOutput;
            $content = ltrim( substr( $output, strpos( $output, '***************' ) + 15 ) );
            vcsCache::cache( $this->path, $version, 'content', $content );
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function getDiff( $version, $current = null )
    {

    }
}

