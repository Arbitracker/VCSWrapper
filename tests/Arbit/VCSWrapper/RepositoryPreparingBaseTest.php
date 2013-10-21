<?php

namespace Arbit\VCSWrapper;

class RepositoryPreparingBaseTest extends TestCase
{
    /**
     * Temporary directory for the Git repository.
     * @var string
     */
    private static $repositoryTempDir;

    protected static function getVcsIdentifier()
    {
        // Emulate abstract static
        throw new \RuntimeException(
            'Missing VCS identifier. Need to override getVcsIdentifier() in test case.'
        );
    }

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        if ( version_compare( PHP_VERSION, '5.2.1' ) < 0 )
        {
            self::markTestSkipped(
                'Test requires PHP 5.2.1 or greater for sys_get_temp_dir().'
            );
        }

        $uniqueTempDir = sys_get_temp_dir() . '/' . uniqid('vcswrapper_test_repository_');
        if ( ! mkdir( $uniqueTempDir ) )
        {
            throw new \RuntimeException(
                "Could not create unique temp dir '$uniqueTempDir' for repository."
            );
        }

        $vcsIdentifier = static::getVcsIdentifier();

        $zipFile = __DIR__ . '/../../data/' . $vcsIdentifier . '.zip';

        if ( !file_exists( $zipFile ) )
        {
            throw new \RuntimeException( "Invalid VCS repository ZIP file: {$zipFile}." );
        }

        $zip = new \ZIPArchive();
        $zip->open( $zipFile );
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
        return 'file://' . self::$repositoryTempDir . '/' . static::getVcsIdentifier();
    }
}
