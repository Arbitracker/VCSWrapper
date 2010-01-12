<?php
/**
 * PHP VCS wrapper Hg Cli resource wrapper
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
 * @subpackage HgCliWrapper
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/**
 * Resource implementation vor Hg Cli wrapper
 *
 * @package VCSWrapper
 * @version $Revision$
 **/
abstract class vcsHgCliResource extends vcsResource implements vcsVersioned, vcsAuthored, vcsLogged
{
    /**
     * Current version of the given resource
     * 
     * @var string
     */
    protected $currentVersion = null;

    /**
     * Returns the latest information about this resource
     *
     * Get the base information, like version, author, etc for the current
     * resource in the current version.
     *
     * @author Tobias Tom <t.tom@succont.de>
     * @uses currentVersion
     * @uses getResourceLog
     * @uses vcsCache
     * @return vcsLogEntry
     */
    protected function getResourceInfo() 
    {
        if ($this->currentVersion !== null) {
            $info = vcsCache::get( $this->path, $this->currentVersion, 'info' );
        }
        if ($this->currentVersion === null || $info === false) {
            $log = $this->getResourceLog();

            // Fecth for specified version, if set
            $info = $this->currentVersion !== null ? $log[$this->currentVersion] : end( $log );
            vcsCache::cache( $this->path, $this->currentVersion = (string) $info->version, 'info', $info );
        }

        return $info;
    }

    /**
     * Returns the complete log for this resource.
     *
     * @author Tobias Tom <t.tom@succont.de>
     * @uses currentVersion
     * @uses path
     * @uses vcsLogEntry
     * @uses vcsHgCliProcess
     * @uses vcsCache
     * @return array Resource log as array (keys are version, values are vcsLogEntry instance)
     */
    protected function getResourceLog() 
    {
        $log = vcsCache::get( $this->path, $this->currentVersion, 'log' );
        if ($log === false) {
            // Refetch the basic logrmation, and cache it.
            $process = new vcsHgCliProcess();
            $process->workingDirectory( $this->root );

            // Fetch for specified version, if set
            if ( $this->currentVersion !== null ) {
                $process->argument( '-r ' . $this->currentVersion );
            }

            // Execute log command
            $process->argument( 'log' );
            $process->argument( '--template' )->argument( '{node}\t{author|email}\t{date|isodate}\t{desc}\n' );
            $process->argument( '.' . $this->path );
            $process->execute();

            // Parse commit log
            $lines = explode( "\n", $process->stdoutOutput );
            if (!$lines) {
                return array();
            }
            $lineCount  = count( $lines );
            $log        = array();
            $lastCommit = null;
            foreach( $lines AS $line ) {
                if ( !$line ) {
                    continue;
                }

                list( $node, $author, $date, $desc ) = explode( "\t", $line, 4 );
                
                $atPosition = strpos( $author, '@' );
                if ( $atPosition ) {
                    $author = substr( $author, 0, $atPosition );
                }
                
                $log[$node] = new vcsLogEntry( $node, $author, $desc, strtotime( $date ) );
            }
            $log = array_reverse( $log );
            $last = end( $log );

            $this->currentVersion = (string) $last->version;
            // Cache extracted data
            vcsCache::cache( $this->path, $this->currentVersion, 'log', $log );
        }

        return $log;
    }

    /**
     * Returns a property of this resource.
     *
     * This method is not implemented because mercurial has no properties.
     *
     * @author Tobias Tom <t.tom@succont.de>
     * @param string $property Name of the property to return
     * @return string Always returns an emtpy string
     */
    protected function getResourceProperty( $property ) 
    {
        $property; // stupid, but surpresses phpcs warnings...
        return '';
    }

    /**
     * Returns the current version.
     *
     * @author Tobias Tom <t.tom@succont.de>
     * @uses getResourceInfo
     * @return string Current version of this resource
     */
    public function getVersionString() 
    {
        $info = $this->getResourceInfo();
        return $info->version;
    }

    /**
     * Returns all version for this resource.
     *
     * @author Tobias Tom <t.tom@succont.de>
     * @uses getResourceLog
     * @return array All known version of this resource
     */
    public function getVersions() 
    {
        $versions = array();
        $log = $this->getResourceLog();

        foreach ( $log as $entry )
        {
            $versions[] = (string) $entry->version;
        }

        return $versions;
    }

    /**
     * Compares two versions.
     *
     * @author Tobias Tom <t.tom@succont.de>
     * @uses getVersions
     * @param string $version1 First version number
     * @param string $version2 Second version number
     * @return integer Returns -1 if the first version is lower than the second, 0 if they are equal, and 1 if the second is lower.
     */
    public function compareVersions( $version1, $version2 ) 
    {
        $versions = $this->getVersions();
        $key1 = array_search( $version1, $versions );
        $key2 = array_search( $version2, $versions );

        if ($key1 === false || $key2 === false)
        {
            return 0;
        }

        return $key1 - $key2;
    }

    /**
     * Returns the author for the given resource.
     *
     * @author Tobias Tom <t.tom@succont.de>
     * @uses getResourceLog
     * @uses path
     * @throws vcsNoSuchVersionException Thrown if the version was not found
     * @param string $version Version for which the author will be returned
     * @return string Author of the given version 
     */
    public function getAuthor( $version = null ) 
    {
        $version = $version === null ? $this->getVersionString() : $version;
        $log = $this->getResourceLog();

        if ( !isset( $log[$version] ) ) {
            throw new vcsNoSuchVersionException( $this->path, $version );
        }

        return $log[$version]->author;
    }

    /**
     * Returns the resource log for this resource.
     *
     * @author Tobias Tom <t.tom@succont.de>
     * @uses getResourceLog
     * @return string Resource log for this resource
     */
    public function getLog() 
    {
        return $this->getResourceLog();
    }

    /**
     * Returns the log entry for he given version.
     *
     * @author Tobias Tom <t.tom@succont.de>
     * @uses getResourceLog
     * @uses path
     * @throws vcsNoSuchVersionException Thrown if the version was not found
     * @param string $version Version for which the log entry will be returned
     * @return string Log entry for the given version
     */
    public function getLogEntry( $version ) 
    {
        $log = $this->getResourceLog();

        if ( !isset( $log[$version] ) )
        {
            throw new vcsNoSuchVersionException( $this->path, $version );
        }

        return $log[$version];
    }
}

