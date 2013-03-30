<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Arbit\VCSWrapper\Diff;

use \Arbit\VCSWrapper\TestCase;

/**
 * Test for the Cunified diff parser
 */
class UnifiedTest extends TestCase
{
    /**
     * Array with diffs in dataprovider.
     *
     * @param array
     */
    protected static $diffs = null;

    public static function getUnifiedDiffFiles()
    {
        if ( self::$diffs !== null ) return $diffs;

        $files = glob( __DIR__ . '/_fixtures/s_*.diff' );
        foreach ( $files as $file ) {
            self::$diffs[] = array(
                $file,
                substr( $file, 0, -4 ) . 'php'
            );
        }

        return self::$diffs;
    }

    /**
     * @dataProvider getUnifiedDiffFiles
     */
    public function testParseUnifiedDiff( $from, $to )
    {
        if ( !is_file( $to ) ) {
            $this->markTestIncomplete( "Comparision file $to does not yet exist." );
        }

        $parser = new \Arbit\VCSWrapper\Diff\Unified();
        $diff = $parser->parseFile( $from );

        // Store diff result in temp folder for manual check in case of failure
        file_put_contents( $this->tempDir . '/' . basename( $to ), "<?php\n\n return " . var_export( $diff, true ) . ";\n\n" );

        // Compare parsed diff against expected diff.
        $this->assertEquals(
            include $to,
            $diff,
            "Diff for file $from does not match expectations."
        );
    }
}
