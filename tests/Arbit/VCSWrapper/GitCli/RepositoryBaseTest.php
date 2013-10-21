<?php

namespace Arbit\VCSWrapper\GitCli;

use Arbit\VCSWrapper\RepositoryPreparingBaseTest;

class RepositoryBaseTest extends RepositoryPreparingBaseTest
{
    protected static function getVcsIdentifier()
    {
        return 'git';
    }
}
