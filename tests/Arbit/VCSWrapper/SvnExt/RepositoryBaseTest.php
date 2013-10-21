<?php

namespace Arbit\VCSWrapper\SvnExt;

use Arbit\VCSWrapper\RepositoryPreparingBaseTest;

class RepositoryBaseTest extends RepositoryPreparingBaseTest
{
    protected static function getVcsIdentifier()
    {
        return 'svn';
    }
}
