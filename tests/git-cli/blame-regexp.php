<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

/**
 * Tests for the regular expression used to extract data from git's blame
 * output.
 */
class vcsGitCliBlameRegexpTests extends vcsTestCase
{
    /**
     * Return test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

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
        self::assertGreaterThan( 0, preg_match( vcsGitCliFile::BLAME_REGEXP, $blameLine, $match ) );
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
