<?php

namespace Arbit\VCSWrapper\SvnCli;

use Arbit\VCSWrapper\RepositoryPreparingBaseTest;

class RepositoryBaseTest extends RepositoryPreparingBaseTest
{
    protected static function getVcsIdentifier()
    {
        return 'svn';
    }
}
