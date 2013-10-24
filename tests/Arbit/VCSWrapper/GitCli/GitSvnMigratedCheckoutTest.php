<?php

namespace Arbit\VCSWrapper\GitCli;

use Arbit\VCSWrapper\RepositoryPreparingBaseTest;

class GitSvnMigratedCheckoutTest extends RepositoryPreparingBaseTest
{
    protected static function getVcsIdentifier()
    {
        return 'git-svn-migrated';
    }

    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \Arbit\VCSWrapper\Cache\Manager::initialize($this->createTempDir());
    }

    public function testLogInitialVersion()
    {
        $repository = new \Arbit\VCSWrapper\GitCli\Checkout($this->tempDir);
        $repository->initialize($this->getRepository());
        $repository->update('9868639fd94941f0498e74b11431f36f7618fa0a');

        $this->assertEquals(
            array(
                "9868639fd94941f0498e74b11431f36f7618fa0a" => new \Arbit\VCSWrapper\LogEntry(
                    "9868639fd94941f0498e74b11431f36f7618fa0a", "Tobias Schlitt", "Initial SVN standard layout.
git-svn-id: file:///home/dotxp/Desktop/svnmigrate/svn_repo/trunk@1 65c7331f-d0f7-4853-aef3-63bf191519e6
", 1382598448
                )
            ),
            $repository->getLog()
        );
    }
}
