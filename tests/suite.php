<?php
/**
 * vcs main test suite
 *
 * @version $Revision$
 * @license LGPLv3
 */

/*
 * Set file whitelist for phpunit
 */
define( 'VCS_TEST', __FILE__ );
$files = include ( $base = dirname(  __FILE__ ) . '/../src/' ) . 'classes/autoload.php';
foreach ( $files as $class => $file )
{
    require_once $base . $file;
    PHPUnit_Util_Filter::addFileToWhitelist( $base . $file );
}

/**
 * Couchdb backend tests
 */
require 'cache_suite.php';

/**
* Test suite for vcs
*/
class vcsTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Basic constructor for test suite
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'vcsWrapper - A PHP VCS wrapper' );

        $this->addTestSuite( vcsCacheTestSuite::suite() );
    }

    /**
     * Return test suite
     * 
     * @return prpTestSuite
     */
    public static function suite()
    {
        return new static( __CLASS__ );
    }
}

