<?php
/**
 * PHP VCS wrapper Mercurial system process class
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
 * @subpackage MercurialCliWrapper
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Arbit\VCSWrapper\HgCli;

/**
 * Mercurial executable wrapper for system process class
 *
 * @package VCSWrapper
 * @subpackage MercurialCliWrapper
 * @version $Revision$
 */
class Process extends \SystemProcess\SystemProcess
{
    /**
     * Static property containg information, if the version of the hg CLI
     * binary version has already been verified.
     *
     * @var bool
     */
    public static $checked = false;

    /**
     * Class constructor taking the executable
     *
     * @param string $executable
     * @return void
     */
    public function __construct($executable = 'hg')
    {
        parent::__construct($executable);
        self::checkVersion();

        $this->nonZeroExitCodeException = true;
        $this->argument('--noninteractive');
    }


    /**
     * Verify git version
     *
     * Verify hat the version of the installed GIT binary is at least 1.6. Will
     * throw an exception, if the binary is not available or too old.
     *
     * @return void
     */
    protected static function checkVersion()
    {
        if (self::$checked === true) {
            return true;
        }

        $process = new \SystemProcess\SystemProcess('env');
        $process->nonZeroExitCodeException = true;
        $process->argument('hg')->argument('--version')->execute();

        if (!preg_match('/\(version (.*)\)/', $process->stdoutOutput, $match)) {
            throw new \RuntimeException('Could not determine Mercurial version.');
        }
        if (version_compare($match[1], '1.3', '>=')) {
            return self::$checked = true;
        }

        throw new \RuntimeException('Mercurial is required in a minimum version of 1.3.');
    }
}
