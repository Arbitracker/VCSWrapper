<?php
/**
 * PHP VCS wrapper CVS Cli resource wrapper
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
 * Resource implementation vor CVS Cli wrapper
 */
abstract class vcsCvsCliResource extends vcsResource implements vcsVersioned, vcsAuthored, vcsLogged
{
    /**
     * Current version of the given resource
     *
     * @var string
     */
    protected $currentVersion = null;

    /**
     * Get resource base information
     *
     * Get the base information, like version, author, etc for the current
     * resource in the current version.
     *
     * @return vcsXml
     */
    protected function getResourceInfo()
    {
        if ( ( $this->currentVersion !== null ) &&
             ( ( $info = vcsCache::get( $this->path, $this->currentVersion, 'info' ) ) !== false ) )
        {
            return $info;
        }

        $version = $this->currentVersion;
        if ( $version === null )
        {
            $version = 'HEAD';
        }

        $process = new vcsCvsCliProcess();
        $process->workingDirectory( $this->root )
                ->redirect( vcsCvsCliProcess::STDERR, vcsCvsCliProcess::STDOUT )
                ->argument( 'log' )
                ->argument( '-r' . $version )
                ->argument( '.' . $this->path )
                ->execute();

        $regexp = '(\-{27}(\r\n|\r|\n)
                   (?# Get revision number )
                   revision\s+(?P<revision>[\d\.]+)(\r\n|\r|\n)
                   (?# Get commit date )
                   date:\s+(?P<date>[^;]+);\s+
                   (?# Get commit author )
                   author:\s+(?P<author>[^;]+);\s+
                   (?# Skip everything else )
                   [^\n\r]+;(\r\n|\r|\n)
                   (?# Get commit message )
                   (?P<message>[\r\n\t]*|.*)$)xs';

        $output = rtrim( substr( rtrim( $process->stdoutOutput ), 0, -77 ) );
        if ( preg_match( $regexp, $output, $match ) === 0 )
        {
            return null;
        }

        $info = new vcsLogEntry( $match['revision'], $match['author'], $match['message'], $match['date'] );
        vcsCache::cache( $this->path, $this->currentVersion = (string) $info->version, 'info', $info );

        return $info;
    }

    /**
     * Get resource log
     *
     * Get the full log for the current resource up tu the current revision
     *
     * @return vcsXml
     */
    protected function getResourceLog()
    {
        if ( ( $log = vcsCache::get( $this->path, $this->currentVersion, 'log' ) ) !== false )
        {
            return $log;
        }

        $version = $this->currentVersion;
        if ( $version === null )
        {
            $version = 'HEAD';
        }

        $process = new vcsCvsCliProcess();
        $process->workingDirectory( $this->root )
                ->redirect( vcsCvsCliProcess::STDERR, vcsCvsCliProcess::STDOUT )
                ->argument( 'log' )
                ->argument( '-r:' . $version )
                ->argument( '.' . $this->path )
                ->execute();


        $log = array();

        $regexp = '(\-{27}(\r\n|\r|\n)
                   (?# Get revision number )
                   revision\s+(?P<revision>[\d\.]+)(\r\n|\r|\n)
                   (?# Get commit date )
                   date:\s+(?P<date>[^;]+);\s+
                   (?# Get commit author )
                   author:\s+(?P<author>[^;]+);\s+
                   (?# Skip everything else )
                   [^\n\r]+;(\r\n|\r|\n)
                   (?# Get commit message )
                   (?P<message>[\r\n\t]*|.*)(\r\n|\r|\n)$)xs';

        // Remove closing equal characters
        $output = rtrim( substr( rtrim( $process->stdoutOutput ), 0, -77 ) );
        // Split all log entries
        $rawLogs = explode( '---------------------------', $output );
        foreach ( $rawLogs as $rawLog )
        {
            if ( preg_match( $regexp, $rawLog, $match ) === 0 )
            {
                continue;
            }
            $revision = $match['revision'];
            $logEntry = new vcsLogEntry( $revision, $match['author'], $match['message'], $match['date'] );

            $log[$revision] = $logEntry;
        }

        var_dump($logs);

    }

    /**
     * Get resource property
     *
     * Get the value of an CVS property
     *
     * @return string
     */
    protected function getResourceProperty( $property )
    {

    }

    /**
     * @inheritdoc
     */
    public function getVersionString()
    {
        if ( $this->currentVersion === null )
        {
            $this->getResourceInfo();
        }
        return $this->currentVersion;
    }

    /**
     * @inheritdoc
     */
    public function getVersions()
    {
        $this->getResourceLog();
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

