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
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Arbit\VCSWrapper\SvnCli;

/**
 * Resource implementation vor SVN Cli wrapper
 *
 * @package VCSWrapper
 * @subpackage SvnCliWrapper
 * @version $Revision$
 */
abstract class Resource extends \Arbit\VCSWrapper\Resource implements \Arbit\VCSWrapper\Versioned, \Arbit\VCSWrapper\Authored, \Arbit\VCSWrapper\Logged, \Arbit\VCSWrapper\Diffable
{
    /**
     * Current version of the given resource
     *
     * @var string
     */
    protected $currentVersion = null;

    /**
     * Username to access the repository
     *
     * @var sting
     */
    protected $username;

    /**
     * Password to access the repository
     *
     * @var sting
     */
    protected $password;

    /**
     * Construct file from local repository path and repository root
     *
     * Construct the resource from the repository root, which is used to store
     * the actual repository contents, and the local paht inside the
     * repository.
     *
     * @param string $root
     * @param string $path
     * @param string $user
     * @param string $password
     * @return void
     */
    public function __construct($root, $path, $user = null, $password = null)
    {
        parent::__construct($root, $path);

        $this->username = $user;
        $this->password = $password;
    }

    /**
     * Get resource base information
     *
     * Get the base information, like version, author, etc for the current
     * resource in the current version.
     *
     * @return \Arbit\Xml\Document
     */
    protected function getResourceInfo()
    {
        if (($this->currentVersion === null) ||
             (($info = \Arbit\VCSWrapper\Cache\Manager::get($this->path, $this->currentVersion, 'info')) === false))
        {
            // Refetch the basic information, and cache it.
            $process = new \Arbit\VCSWrapper\SvnCli\Process('svn', $this->username, $this->password);
            $process->argument('--xml');

            // Fetch for specified version, if set
            if ($this->currentVersion !== null) {
                $process->argument('-r' . $this->currentVersion);
            }

            // Execute info command
            $return = $process->argument('info')->argument(new \SystemProcess\Argument\PathArgument($this->root . $this->path))->execute();

            $info = \Arbit\Xml\Document::loadString($process->stdoutOutput);
            \Arbit\VCSWrapper\Cache\Manager::cache($this->path, $this->currentVersion = (string) $info->entry[0]->commit[0]['revision'], 'info', $info);
        }

        return $info;
    }

    /**
     * Get resource log
     *
     * Get the full log for the current resource up tu the current revision
     *
     * @return \Arbit\Xml\Document
     */
    protected function getResourceLog()
    {
        if (($log = \Arbit\VCSWrapper\Cache\Manager::get($this->path, $this->currentVersion, 'log')) === false) {
            // Refetch the basic logrmation, and cache it.
            $process = new \Arbit\VCSWrapper\SvnCli\Process('svn', $this->username, $this->password);
            $process->argument('--xml');

            // Fecth for specified version, if set
            if ($this->currentVersion !== null) {
                $process->argument('-r1:' . $this->currentVersion);
            }

            // Execute logr command
            $return = $process->argument('log')->argument(new \SystemProcess\Argument\PathArgument($this->root . $this->path))->execute();

            // Transform XML into object array
            $xmlLog = \Arbit\Xml\Document::loadString($process->stdoutOutput);
            $log    = array();
            foreach ($xmlLog->logentry as $entry) {
                $log[(string) $entry['revision']] = new \Arbit\VCSWrapper\LogEntry(
                    $entry['revision'],
                    $entry->author,
                    $entry->msg,
                    strtotime($entry->date)
                );
            }
            uksort($log, array($this, 'compareVersions'));
            $last = end($log);

            // Cache extracted data
            \Arbit\VCSWrapper\Cache\Manager::cache($this->path, $this->currentVersion = (string) $last->version, 'log', $log);
        }

        return $log;
    }

    /**
     * Get resource property
     *
     * Get the value of an SVN property
     *
     * @param string $property
     * @return string
     */
    protected function getResourceProperty($property)
    {
        if (($value = \Arbit\VCSWrapper\Cache\Manager::get($this->path, $this->currentVersion, $property)) === false) {
            // Refetch the basic mimeTypermation, and cache it.
            $process = new \Arbit\VCSWrapper\SvnCli\Process('svn', $this->username, $this->password);

            // Fecth for specified version, if set
            if ($this->currentVersion !== null) {
                $process->argument('-r' . $this->currentVersion);
            }

            // Execute mimeTyper command
            $return = $process->argument('propget')->argument('svn:' . $property)->argument(new \SystemProcess\Argument\PathArgument($this->root . $this->path))->execute();

            $value = trim($process->stdoutOutput);
            \Arbit\VCSWrapper\Cache\Manager::cache($this->path, $this->currentVersion, $property, $value);
        }

        return $value;
    }

    /**
     * Get version string
     *
     * Return a string representing the current version of the file or
     * directory.
     *
     * @return string
     */
    public function getVersionString()
    {
        if ($this->currentVersion === null) {
            $this->getResourceInfo();
        }

        return $this->currentVersion;
    }

    /**
     * Get available versions
     *
     * Get all available versions for the current resource. This method
     * returns an array with all version strings.
     *
     * @return array
     */
    public function getVersions()
    {
        $versions = array();
        $log = $this->getResourceLog();
        foreach ($log as $entry) {
            $versions[] = (string) $entry->version;
        }

        return $versions;
    }

    /**
     * Compare two version strings
     *
     * If $version1 is lower then $version2, an integer < 0, will be returned.
     * In case $version1 is bigger / later then $version2 an integer > 0 will
     * be returned. In case both versions are equal 0 will be returned.
     *
     * @param string $version1
     * @param string $version2
     * @return int
     */
    public function compareVersions($version1, $version2)
    {
        return $version1 - $version2;
    }

    /**
     * Get author
     *
     * Return author information for the resource. Optionally the $version
     * parameter may be passed to the method to specify a version the author
     * information should be returned for.
     *
     * @param mixed $version
     * @return string
     */
    public function getAuthor($version = null)
    {
        if ($version === null) {
            $info = $this->getResourceInfo();
            return (string) $info->entry[0]->commit[0]->author;
        }

        $version = $version === null ? $this->getVersionString() : $version;
        $log = $this->getResourceLog();

        if (!isset($log[$version])) {
            throw new \UnexpectedValueException("Invalid log entry $version for {$this->path}.");
        }

        return $log[$version]->author;
    }

    /**
     * Get full revision log
     *
     * Return the full revision log for the given resource. The revision log
     * should be returned as an array of \Arbit\VCSWrapper\LogEntry objects.
     *
     * @return array
     */
    public function getLog()
    {
        return $this->getResourceLog();
    }

    /**
     * Get revision log entry
     *
     * Get the revision log entry for the spcified version.
     *
     * @param string $version
     * @return \Arbit\VCSWrapper\LogEntry
     */
    public function getLogEntry($version)
    {
        $log = $this->getResourceLog();

        if (!isset($log[$version])) {
            throw new \UnexpectedValueException("Invalid log entry $version for {$this->path}.");
        }

        return $log[$version];
    }

    /**
     * Get diff
     *
     * Get the diff between the current version and the given version.
     * Optionally you may specify another version then the current one as the
     * diff base as the second parameter.
     *
     * @param string $version
     * @param string $current
     * @return \Arbit\VCSWrapper\Resource
     */
    public function getDiff($version, $current = null)
    {
        $current = ($current === null) ? $this->getVersionString() : $current;

        if (($diff = \Arbit\VCSWrapper\Cache\Manager::get($this->path, $version, 'diff')) === false) {
            // Refetch the basic content information, and cache it.
            $process = new \Arbit\VCSWrapper\SvnCli\Process('svn', $this->username, $this->password);
            $process->argument('-r' . $version . ':' . $current);

            // Execute command
            $return = $process->argument('diff')->argument(new \SystemProcess\Argument\PathArgument($this->root . $this->path))->execute();
            $parser = new \Arbit\VCSWrapper\Diff\Unified();
            $diff   = $parser->parseString($process->stdoutOutput);
            \Arbit\VCSWrapper\Cache\Manager::cache($this->path, $version, 'diff', $diff);
        }

        foreach ($diff as $fileDiff) {
            $fileDiff->from = substr($fileDiff->from, strlen($this->root));
            $fileDiff->to   = substr($fileDiff->to, strlen($this->root));
        }

        return $diff;
    }
}
