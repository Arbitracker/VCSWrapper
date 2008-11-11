<?php
/**
 * PHP VCS wrapper autoload file
 *
 * This file is part of vcs-wrapper.
 *
 * vcs-wrapper is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * vcs-wrapper is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with vcs-wrapper; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/*
 * This array is autogenerated and topoligically sorted. Do not change anything
 * in here, but just run the following script in the trunk/ directory.
 *
 * # scripts/gen_autoload_files.php
 */
return array(
    'vcsCache'                        => 'classes/cache.php',
    'vcsCacheMetaData'                => 'classes/cache/metadata.php',
    'vcsCacheFileSystemMetaData'      => 'classes/cache/metadata/filesystem.php',
    'vcsCacheSqliteMetaData'          => 'classes/cache/metadata/sqlite.php',
    'vcsException'                    => 'classes/exceptions.php',
    'vcsCacheNotInitializedException' => 'classes/exceptions.php',
    'vcsNotCacheableException'        => 'classes/exceptions.php',
    'vcsCacheable'                    => 'classes/interfaces/cacheable.php',
    'vcsResource'                     => 'classes/resource.php',
    'vcsResource'                     => 'classes/resource.php',
    'vcsDirectory'                    => 'classes/directory.php',
    'vcsVersioned'                    => 'classes/interfaces/versioned.php',
    'vcsAuthored'                     => 'classes/interfaces/authored.php',
    'vcsLogged'                       => 'classes/interfaces/logged.php',
    'vcsSvnCliDirectory'              => 'classes/wrapper/svn-cli/directory.php',
    'vcsFile'                         => 'classes/file.php',
    'vcsBlameable'                    => 'classes/interfaces/blameable.php',
    'vcsSvnCliFile'                   => 'classes/wrapper/svn-cli/file.php',
    'vcsRepository'                   => 'classes/repository.php',
    'vcsSvnCliRepository'             => 'classes/wrapper/svn-cli/repository.php',
);

