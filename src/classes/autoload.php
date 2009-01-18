<?php
/**
 * arbit autoload file
 *
 * This file is part of arbit.
 *
 * arbit is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License.
 *
 * arbit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with arbit; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL
 */

/*
 * This array is autogenerated and topoligically sorted. Do not change anything
 * in here, but just run the following script in the trunk/ directory.
 *
 * # scripts/gen_autoload_files.php
 */
return array(
    'vcsCacheable'                                     => 'interfaces/cacheable.php',
    'vcsBaseStruct'                                    => 'struct.php',
    'vcsBlameStruct'                                   => 'blame.php',
    'vcsCache'                                         => 'cache.php',
    'vcsCacheMetaData'                                 => 'cache/metadata.php',
    'vcsCacheFileSystemMetaData'                       => 'cache/metadata/filesystem.php',
    'vcsCacheSqliteMetaData'                           => 'cache/metadata/sqlite.php',
    'vcsDiffChunk'                                     => 'diff/chunk.php',
    'vcsDiff'                                          => 'diff/diff.php',
    'vcsDiffLine'                                      => 'diff/line.php',
    'vcsDiffParser'                                    => 'diff/parser.php',
    'vcsUnifiedDiffParser'                             => 'diff/parser/unified.php',
    'vcsException'                                     => 'exceptions.php',
    'vcsNoSuchFileException'                           => 'exceptions.php',
    'vcsRuntimeException'                              => 'exceptions.php',
    'vcsXmlParserException'                            => 'exceptions.php',
    'vcsCacheNotInitializedException'                  => 'exceptions.php',
    'vcsNotCacheableException'                         => 'exceptions.php',
    'vcsCheckoutFailedException'                       => 'exceptions.php',
    'vcsNoSuchVersionException'                        => 'exceptions.php',
    'vcsInvalidZipArchiveException'                    => 'exceptions.php',
    'vcsInvalidRepositoryUrlException'                 => 'exceptions.php',
    'vcsFileNotFoundException'                         => 'exceptions.php',
    'pbsSystemProcessInvalidCustomDescriptorException' => 'external/exceptions/system_process/invalidCustomFileDescriptor.php',
    'pbsSystemProcessNonZeroExitCodeException'         => 'external/exceptions/system_process/nonZeroExitCode.php',
    'pbsSystemProcessNotRunningException'              => 'external/exceptions/system_process/notRunning.php',
    'pbsSystemProcessRecursivePipeException'           => 'external/exceptions/system_process/recursivePipe.php',
    'vcsXmlNode'                                       => 'external/xml/node.php',
    'vcsXml'                                           => 'external/xml/document.php',
    'vcsXmlNodeList'                                   => 'external/xml/node_list.php',
    'vcsLogEntry'                                      => 'log_entry.php',
    'vcsResource'                                      => 'resource.php',
    'vcsArchiveResource'                               => 'wrapper/archive/resource.php',
    'vcsDirectory'                                     => 'interfaces/directory.php',
    'vcsArchiveDirectory'                              => 'wrapper/archive/directory.php',
    'vcsCheckout'                                      => 'interfaces/checkout.php',
    'vcsArchiveCheckout'                               => 'wrapper/archive/checkout.php',
    'vcsZipArchiveCheckout'                            => 'wrapper/archive/checkout/zip.php',
    'vcsFile'                                          => 'interfaces/file.php',
    'vcsArchiveFile'                                   => 'wrapper/archive/file.php',
    'vcsCvsCliDirectory'                               => 'wrapper/cvs-cli/directory.php',
    'vcsCvsCliCheckout'                                => 'wrapper/cvs-cli/checkout.php',
    'vcsVersioned'                                     => 'interfaces/versioned.php',
    'vcsAuthored'                                      => 'interfaces/authored.php',
    'vcsLogged'                                        => 'interfaces/logged.php',
    'vcsCvsCliResource'                                => 'wrapper/cvs-cli/resource.php',
    'vcsBlameable'                                     => 'interfaces/blameable.php',
    'vcsFetchable'                                     => 'interfaces/fetchable.php',
    'vcsDiffable'                                      => 'interfaces/diffable.php',
    'vcsCvsCliFile'                                    => 'wrapper/cvs-cli/file.php',
    'pbsSystemProcess'                                 => 'external/system_process/systemProcess.php',
    'vcsCvsCliProcess'                                 => 'wrapper/cvs-cli/process.php',
    'vcsGitCliResource'                                => 'wrapper/git-cli/resource.php',
    'vcsGitCliDirectory'                               => 'wrapper/git-cli/directory.php',
    'vcsGitCliCheckout'                                => 'wrapper/git-cli/checkout.php',
    'vcsGitCliFile'                                    => 'wrapper/git-cli/file.php',
    'vcsGitCliProcess'                                 => 'wrapper/git-cli/process.php',
    'vcsSvnCliResource'                                => 'wrapper/svn-cli/resource.php',
    'vcsSvnCliDirectory'                               => 'wrapper/svn-cli/directory.php',
    'vcsSvnCliCheckout'                                => 'wrapper/svn-cli/checkout.php',
    'vcsSvnCliFile'                                    => 'wrapper/svn-cli/file.php',
    'vcsSvnCliProcess'                                 => 'wrapper/svn-cli/process.php',
    'vcsSvnExtResource'                                => 'wrapper/svn-ext/resource.php',
    'vcsSvnExtDirectory'                               => 'wrapper/svn-ext/directory.php',
    'vcsSvnExtCheckout'                                => 'wrapper/svn-ext/checkout.php',
    'vcsSvnExtFile'                                    => 'wrapper/svn-ext/file.php',
);

