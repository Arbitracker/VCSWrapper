<?php
/**
 * PHP VCS wrapper diff chunk struct
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
 * @subpackage Diff
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Arbit\VCSWrapper\Diff;

/**
 * Basic struct containing a diff chunk
 *
 * @package VCSWrapper
 * @subpackage Diff
 * @version $Revision$
 */
class Chunk extends \Arbit\VCSWrapper\Struct
{
    /**
     * Array containing the structs properties.
     *
     * @var array
     */
    protected $properties = array(
        'start'      => null,
        'startRange' => 1,
        'end'        => null,
        'endRange'   => 1,
        'lines'      => null,
    );

    /**
     * Construct diff from properties
     *
     * @param int $start
     * @param int $startRange
     * @param int $end
     * @param int $endRange
     * @param array $lines
     * @return void
     */
    public function __construct($start = null, $startRange = 1, $end = null, $endRange = 1, array $lines = array())
    {
        $this->start      = (int) $start;
        $this->startRange = (int) $startRange;
        $this->end        = (int) $end;
        $this->endRange   = (int) $endRange;
        $this->lines      = $lines;
    }

    /**
     * Recreate struct exported by var_export()
     *
     * Recreate struct exported by var_export()
     *
     * @ignore
     * @param array $properties
     * @param string $class
     * @return vcsDiffChunk
     */
    public static function __set_state(array $properties, $class = __CLASS__)
    {
        return \Arbit\VCSWrapper\Struct::__set_state($properties, $class);
    }
}
