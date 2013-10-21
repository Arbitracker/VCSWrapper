<?php

namespace Arbit\VCSWrapper\CvsCli;

use Arbit\VCSWrapper\RepositoryPreparingBaseTest;

class RepositoryBaseTest extends RepositoryPreparingBaseTest
{
    protected static function getVcsIdentifier()
    {
        return 'cvs';
    }

    protected function getRepository()
    {
        return realpath( $this->getRepositoryPath() ) . '#cvs';
    }
}
