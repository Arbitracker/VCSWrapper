<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\GitCli;

use \Arbit\VCSWrapper\TestCase;

/**
 * Test for the regular expression used to extract data from git's blame
 * output.
 */
class BlameRegexpTest extends TestCase
{
    /**
     * Performs the regular
     *
     * @param string $blameLine
     *
     * @return void
     * @dataProvider dataProviderBlameLines
     */
    public function testRegexpMatchesBlameLine( $blameLine )
    {
        self::assertGreaterThan( 0, preg_match( File::BLAME_REGEXP, $blameLine, $match ) );
    }

    /**
     * Returns blame test data.
     *
     * @return array(array)
     */
    public static function dataProviderBlameLines()
    {
        return array(
            array( '52459828f5d9b853bf147e9b392c66353673f969 PHP/Depend/Code/ASTFormalParameters.php (Manuel Pichler 2009-07-24 08:17:27 +0000 117)' )
        );
    }
}
