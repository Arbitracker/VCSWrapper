<?php

namespace Arbit\VCSWrapper\GitCli;

use \Arbit\VCSWrapper\TestCase;

class RepositoryPreparingBaseTest extends TestCase
{
    /**
     * Temporary directory for the Git repository.
     * @var string
     */
    private static $repositoryTempDir;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        if ( version_compare( PHP_VERSION, '5.2.1' ) < 0 )
        {
            self::markTestSkipped(
                'Test requires PHP 5.2.1 or greater for sys_get_temp_dir().'
            );
        }

        $uniqueTempDir = sys_get_temp_dir() . '/' . uniqid('vcswrapper_git_repository_');
        if ( ! mkdir( $uniqueTempDir ) )
        {
            throw new \RuntimeException(
                "Could not create unique temp dir '$uniqueTempDir' for repository."
            );
        }

        $zip = new \ZIPArchive();
        $zip->open( __DIR__ . '/../../../data/git.zip' );
        $zip->extractTo( $uniqueTempDir );
        $zip->close();

        self::$repositoryTempDir = $uniqueTempDir;
    }

    public static function tearDownAfterClass()
    {
        self::removeRecursively( self::$repositoryTempDir );
    }

    protected function getRepository()
    {
        return 'file://' . self::$repositoryTempDir . '/git';
    }
}
