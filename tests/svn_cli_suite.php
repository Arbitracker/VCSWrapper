<?php
/**
 * vcs main test suite
 *
 * @version $Revision: 14 $
 * @license LGPLv3
 */

/*
 * Set file whitelist for phpunit
 */
if ( !defined( 'VCS_TEST' ) )
{
    $files = include ( $base = dirname(  __FILE__ ) . '/../src/' ) . 'classes/autoload.php';
    foreach ( $files as $class => $file )
    {
        require_once $base . $file;
        PHPUnit_Util_Filter::addFileToWhitelist( $base . $file );
    }

    require 'base_test.php';
}

/**
 * Couchdb backend tests
 */
require 'svn-cli/repository.php';

/**
* Test suite for vcs
*/
class vcsSvnCliTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Basic constructor for test suite
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'SVN cli wrapper suite' );

        $this->addTest( vcsSvnCliRepositoryTests::suite() );
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

