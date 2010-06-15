<?php
/**
 * PHP VCS wrapper Bzr Cli file wrapper
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
 * @subpackage BzrCliWrapper
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/**
 * File implementation vor Bazaar Cli wrapper
 *
 * @package VCSWrapper
 * @subpackage BzrCliWrapper
 * @version $Revision$
 */
class vcsBzrCliFile extends vcsBzrCliResource implements vcsFile, vcsBlameable, vcsDiffable
{
    /**
     * Returns the contents of this file
     *
     * @return string
     */
    public function getContents()
    {
        return file_get_contents( $this->root . $this->path );
    }

    /**
     * Returns the mimetype for this file.
     *
     * @return string Mimetype of this file
     */
    public function getMimeType()
    {
        // If not set, fall back to application/octet-stream
        return 'application/octet-stream';
    }

    /**
     * Returns blame information for each line in file.
     *
     * @param string $version
     * @return array(vcsBlameStruct)
     */
    public function blame( $version = null )
    {
        $version = ( $version === null ) ? $this->getVersionString() : $version;

        if ( !in_array( $version, $this->getVersions(), true ) ) {
            throw new vcsNoSuchVersionException( $this->path, $version );
        }

        $blame = vcsCache::get( $this->path, $version, 'blame' );
        if ( $blame === false ) {
            $shortHashCache = array();

            // Refetch the basic blamermation, and cache it.
            $process = new vcsBzrCliProcess();
            $process->workingDirectory( $this->root );

            // Execute command
            $process->argument( 'xmlannotate' );
            if ( $version !== null ) {
                $process->argument( '-r' . $version );
            }
            $process->argument( '.' . $this->path );
            $return = $process->execute();
            
            $xmlDoc = new SimpleXMLElement($process->stdoutOutput);

            // Convert returned lines into diff structures
            $blame = array();
            foreach ( $xmlDoc->entry AS $line ) {
                $user = $line["author"];
                $date = strtotime($line["date"]);
                $revno = $line["revno"];
                $line = $line;

                $blame[] = new vcsBlameStruct( $line, $revno, $user, $date );
            }

            vcsCache::cache( $this->path, $version, 'blame', $blame );
        }

        return $blame;
    }

    /**
     * Returns the diff between two different versions.
     *
     * @param string $version
     * @param string $current
     * @return vcsDiff
     */
    public function getDiff( $version, $current = null )
    {
        if ( !in_array( $version, $this->getVersions(), true ) ) {
            throw new vcsNoSuchVersionException( $this->path, $version );
        }

        $diff = vcsCache::get( $this->path, $version, 'diff' );
        if ($diff === false) {
            // Refetch the basic content information, and cache it.
            $process = new vcsBzrCliProcess();
            $process->workingDirectory( $this->root );
            $process->argument( 'diff' );
            if ($current !== null) {
                $process->argument( '-r' . $version . ".." . $current );
            } else {
                $process->argument( "-r" . $version );
            }
            $process->argument( '.' . $this->path );
            try {
                $process->execute();
            } catch ( pbsSystemProcessNonZeroExitCodeException $e ) {
                print_r($e);
                print_r($process);
            }

            // Parse resulting unified diff
            $parser = new vcsUnifiedDiffParser();
            $diff   = $parser->parseString( $process->stdoutOutput );
            vcsCache::cache( $this->path, $version, 'diff', $diff );
        }

        return $diff;
    }
}

