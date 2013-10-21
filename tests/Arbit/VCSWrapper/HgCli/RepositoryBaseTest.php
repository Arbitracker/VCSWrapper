<?php

namespace Arbit\VCSWrapper\HgCli;

use Arbit\VCSWrapper\RepositoryPreparingBaseTest;

class RepositoryBaseTest extends RepositoryPreparingBaseTest
{
    protected static function getVcsIdentifier()
    {
        return 'hg';
    }
}
