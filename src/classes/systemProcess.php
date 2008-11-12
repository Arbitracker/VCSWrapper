<?php
/**
 * PHP VCS wrapper system process handler
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
 * @package VCSWrapper
 * @subpackage Core
 * @version $Revision: 14 $
 * @author Jakob Westhoff <jakob@php.net>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */

/**
 * Management facility for any external system process.
 */
class pbsSystemProcess 
{
    /*
     * Types of command parts
     */
    const EXECUTABLE        = 1;    
    const ARGUMENT          = 2;
    const UNESCAPEDARGUMENT = 3;
    const SYSTEMPROCESS     = 4;
    const STDOUT_REDIRECT   = 5;
    const STDERR_REDIRECT   = 6;

    /*
     * Constants to represent the stdin, out and err handles
     */
    const STDIN  = 0;
    const STDOUT = 1;
    const STDERR = 2;

    /*
     * Constants defining descriptor types
     */
    const PIPE = 'pipe';
    const FILE = 'file';

    /*
     * Constants of signals, which can be send to the running process
     */
    const SIGHUP    = 1;
    const SIGINT    = 2;
    const SIGQUIT   = 3;
    const SIGILL    = 4;
    const SIGTRAP   = 5;
    const SIGABRT   = 6;
    const SIGFPE    = 8;
    const SIGKILL   = 9;
    const SIGUSR1   = 10;
    const SIGSEGV   = 11;
    const SIGUSR2   = 12;
    const SIGPIPE   = 13;
    const SIGALRM   = 14;
    const SIGTERM   = 15;
    const SIGSTKFLT = 16;
    const SIGCHLD   = 17;
    const SIGCONT   = 18;
    const SIGSTOP   = 19;
    const SIGTSTP   = 20;
    const SIGTTIN   = 21;
    const SIGTTOU   = 22;
    const SIGIO     = 23;
    const SIGXCPU   = 24;
    const SIGXFSZ   = 25;
    const SIGVTALRM = 26;
    const SIGPROF   = 27;
    const SIGWINCH  = 28;

    /**
     * Attributes of the class
     * 
     * @var array
     */
    protected $attributes = array( 
        'stdoutOutput'  =>  '',
        'stderrOutput'  =>  ''
    );    

    /**
     * Array containing all parts of the constructed command including their
     * type
     * 
     * @var array( array )
     */
    protected $commandParts = array();

    /**
     * Environment to be set for execution 
     * 
     * @var array
     */
    protected $environment = null;

    /**
     * Working directory to be used for execution
     * 
     * @var string
     */
    protected $workingDirectory = null;

    /**
     * Pipes from and to the running process
     * 
     * @var array
     */
    protected $pipes = null;

    /**
     * Custom file descriptors which can be created and used
     * 
     * @var array
     */
    protected $customDescriptors = array();

    /**
     * Collected output of the running process
     * 
     * @var string
     */
    protected $stdoutOutput = '';

    /**
     * Collected error output of the running process 
     * 
     * @var string
     */
    protected $stderrOutput = '';

    /**
     * The process handle of the currently running process
     * 
     * @var resource
     */
    protected $processHandle = null;

    /**
     * Class constructor taking the executable
     * 
     * @param string $executable Executable to create system process for
     * @return void
     */
    public function __construct( $executable ) 
    {
        $this->commandParts[] = array( self::EXECUTABLE, $executable );
    }

    /**
     * Interceptor method to handle writable attributes
     * 
     * @param mixed $k 
     * @param mixed $v 
     * @return void
     */
    public function __set( $k, $v ) 
    {
        if ( array_key_exists( $k, $this->attributes ) !== true ) 
        {
            throw new pbsAttributeException( pbsAttributeException::NON_EXISTANT, $k );
        }

        // None of the attributes are writeable
        switch( $k ) 
        {
            default:
                throw new pbsAttributeException( pbsAttributeException::WRITE, $k );
        }
    }

    /**
     * Interceptor method to handle readable attributes
     * 
     * @param mixed $k 
     * @return mixed
     */
    public function __get( $k ) 
    {
        if ( array_key_exists( $k, $this->attributes ) !== true ) 
        {
            throw new pbsAttributeException( pbsAttributeException::NON_EXISTANT, $k );
        }

        // All existant attributes are readable
        switch( $k ) 
        {
            default:
                return $this->attributes[$k];
        }
    }

    /**
     * Add an argument to the system process
     * 
     * @param string $argument Argument to add to the commandline
     * @param bool $alreadyEscaped The given argument will not be escaped. If
     * you decide to pass true here, you need to make sure the argument
     * supplied is not harmful and treated as one argument. Therfore you may
     * need to enclose it in single or double quotes.
     * @return pbsSystemProcess The object this method was called on (fluent
     * interface)
     */
    public function argument( $argument, $alreadyEscaped = false ) 
    {       
        $this->commandParts[] = array( 
            ( $alreadyEscaped === true )
          ? ( self::UNESCAPEDARGUMENT )
          : ( self::ARGUMENT ),
            $argument
        );
        return $this;
    }

    /**
     * Pipe the output of the executed command to another system process
     * 
     * @param pbsSystemProcess $process Process to pipe the output to
     * @return pbsSystemProcess The object this method was called on (fluent
     * interface)
     */
    public function pipe( pbsSystemProcess $process ) 
    {
        if ( $process === $this ) 
        {
            throw new pbsSystemProcessRecursivePipeException();
        }
        $this->commandParts[] = array( self::SYSTEMPROCESS, &$process->commandParts );
        return $this;
    }

    /**
     * Redirect one of the streams to a file or another stream
     * 
     * @param int $stream The stream to redirect (one of the class constants
     * STDOUT or STDERR) 
     * @param mixed $target The target to redirect the given stream to. This
     * may be a filename or a another stream
     * @return pbsSystemProcess The object this method was called on (fluent
     * interface)
     */
    public function redirect( $stream, $target )
    {
        $this->commandParts[] = array( 
            ( $stream == self::STDOUT ) 
          ? ( self::STDOUT_REDIRECT )
          : ( self::STDERR_REDIRECT ),
            $target
        );
        return $this;
    }

    /**
     * Set a special environment for the process. 
     *
     * If none is set the environment of the php process is used.
     * 
     * @param array $env The environment to be used defined as associative
     * array. The array key is the variable name and the value is the
     * corresponding value for this variable.
     * @return pbsSystemProcess The object this method was called on (fluent
     * interface)
     */
    public function environment( $env ) 
    {
        if ( $this->environment === null ) 
        {
            $this->environment = array();
        }
        $this->environment = array_merge( $this->environment, $env );
        return $this;
    }

    /**
     * Set the working directory to be used.
     *
     * If this function is not called the working dir of the php process will
     * be used.
     * 
     * @param string $cwd Working directory to be set
     * @return pbsSystemProcess The object this method was called on (fluent
     * interface)
     */
    public function workingDirectory( $cwd ) 
    {
        $this->workingDirectory = $cwd;
        return $this;
    }

    /**
     * Add a custom file descriptor which will be attached to the process.
     *
     * After the process is created you can use the the supplied pipes to
     * interact with your custom descriptor. Custom file descriptor pipes can
     * only be used with asyncronous executions.
     * 
     * @param int $fd File descriptor id
     * @param string $type PIPE or FILE constant
     * @param string $target If the PIPE type is used this is whether "w" to
     * allow the process writing to the pipe or "r" to allow the process
     * reading from the pipe
     * @param string filemode If the type is FILE this is the mode to open the
     * file with, e.g. "a"
     * @return pbsSystemProcess The object this method was called on (fluent
     * interface)
     */
    public function descriptor( $fd, $type, $target, $filemode = null ) 
    {
        if ( $fd < 3 ) 
        {
            throw new pbsSystemProcessInvalidCustomDescriptorException( $fd );
        }
        if ( $filemode === null ) 
        {
            $this->customDescriptors[(int)$fd] = array( $type, $target );
        }
        else 
        {
            $this->customDescriptors[(int)$fd] = array( $type, $target, $filemode );
        }
        return $this;
    }

    /**
     * Execute the system process
     * 
     * @param bool $asyncronous Whether the execution is asynronous or not.
     * @return mixed If asyncronous is true an array with all the pipes will be
     * returned. If asyncronous is false the exitcode of the application will
     * be returned after the process has finished its execution. False is
     * returned on an error. The error message can be obtained using
     * stderrOutput.
     */
    public function execute( $asyncronous = false )
    {
        $this->prepareExecution();
        $command = $this->buildCommand( $this->commandParts );
        $ds      = $this->prepareDescriptorSpecification();

        $this->processHandle = proc_open( 
            $command,
            $ds,
            $this->pipes,
            $this->workingDirectory,
            $this->environment
        );

        if ( $asyncronous === true ) 
        {
            return $this->pipes;
        }
                
        // Handle all the data until the streams are closed
        $readablePipes = array( 1 => true, 2 => true );
        foreach( $this->customDescriptors as $k => $cd ) 
        {
            if ( $cd[0] === self::PIPE && $cd[1] === 'w' ) 
            {
                $readablePipes[$k] = true;
            }
        }
        while ( true ) 
        {
            // Read all the given data
            $r = array( $this->pipes[1], $this->pipes[2] );            
            $w = null;
            $e = null;
            
            if ( ( $num = stream_select( $r, $w, $e, null ) ) === false ) 
            {
                return false;
            }

            // Map the handles to their fd index
            $readableHandles = array();
            foreach( $r as $handle ) 
            {
                foreach( $this->pipes as $k => $pipe ) 
                {
                    if ( $handle === $pipe ) 
                    {
                        $readableHandles[$k] = $handle;
                    }
                }
            }
            
            // Read all the provided data
            foreach( $readableHandles as $k => $handle )             
            {                
                switch( $k ) 
                {
                    case 1:
                        $this->attributes['stdoutOutput'] .= fread( $handle, 4096 );
                    break;
                    case 2:
                        $this->attributes['stderrOutput'] .= fread( $handle, 4096 );
                    break;
                    default:
                        // A custom descriptor has data. We don't handle this here.
                        // Therefore the data is just read and discarded.
                        fread( $handle, 4096 );
                }
                
                if ( feof( $handle ) === true ) 
                {
                    $readablePipes[$k] = false;
                }                
            }
            
            $finished = true;
            foreach( $readablePipes as $pipe ) 
            {
                if ( $pipe === true ) 
                {
                    $finished = false;
                }
            }
            if ( $finished === true ) 
            {
                break;
            }
        }

        // Wait until the process is finished and close it
        return $this->close();
    }

    /**
     * Close the currently running asyncronous process.
     *
     * This function will block until the process is finished.
     * 
     * @return int errorcode
     */
    public function close() 
    {
        if ( $this->processHandle === null ) 
        {
            throw new pbsSystemProcessNotRunningException();
        }

        // Close all pipes
        foreach( $this->pipes as $pipe ) 
        {
            if ( is_resource( $pipe ) ) 
            {
                fclose( $pipe );
            }
        }

        // Close the process
        $retVal = proc_close( $this->processHandle );
        $this->processHandle = null;

        return $retVal;
    }

    /**
     * Send an arbitrary POSIX signal to the running process
     * 
     * @param int $signal Signal to be send to the process.
     * @return void
     */
    public function signal( $signal ) 
    {
        if ( $this->processHandle === null ) 
        {
            throw new pbsSystemProcessNotRunningException();
        }
        proc_terminate( $this->processHandle, $signal );
    }

    /**
     * Prepare the execution of the defined process
     * 
     * @return void
     */
    protected function prepareExecution() 
    {
        // If there is a asyncronously running process still opened close it
        if ( $this->processHandle !== null ) 
        {
            // Send TERM signal
            $this->signal( self::SIGTERM );
            sleep( 0.5 );
            // Check if the process has terminated
            $status = proc_get_status( $this->processHandle );
            if ( $status['running'] === false ) 
            {
                // Send a KILL signal
                $this->signal( self::SIGKILL );
            }

            $this->close();
        }

        // Clean the output buffers
        $this->attributes['stdoutOutput'] = '';
        $this->attributes['stderrOutput'] = '';

        // Remove all remaining pipe references
        $this->pipes = null;        
    }

    /**
     * Prepare the descriptor specification and create it
     * 
     * @return array The descriptor specification array to create a new process
     */
    protected function prepareDescriptorSpecification() 
    {
        $ds = $this->customDescriptors;        

        // Make sure the default output descriptors are set correctly
        $ds[self::STDIN]  = array( 'pipe', 'r' );
        $ds[self::STDOUT] = array( 'pipe', 'w' );
        $ds[self::STDERR] = array( 'pipe', 'w' );

        return $ds;
    }

    /**
     * Build the commandline to execute
     * 
     * @param array $parts Array containing the commandline parts
     * @return string The constructed commandline
     */
    public function buildCommand( $parts ) 
    {
        $cmd = '';
        foreach( $parts as $part ) 
        {
            if ( $cmd !== '' ) 
            {
                $cmd .= ' ';
            }

            switch( $part[0] ) 
            {
                case self::EXECUTABLE:
                    $cmd .= escapeshellcmd( $part[1] );
                break;
                case self::ARGUMENT:
                    $cmd .= escapeshellarg( $part[1] );
                break;
                case self::UNESCAPEDARGUMENT:
                    $cmd .= $part[1];
                break;
                case self::SYSTEMPROCESS:
                    $cmd .= '| ' . $this->buildCommand( $part[1] );
                break;
                case self::STDOUT_REDIRECT:
                    if ( is_int( $part[1] ) === true ) 
                    {
                        $cmd .= '1>&' . $part[1];
                    }
                    else 
                    {
                        $cmd .= '1>' . escapeshellarg( $part[1] );
                    }
                break;
                case self::STDERR_REDIRECT:
                    if ( is_int( $part[1] ) === true ) 
                    {
                        $cmd .= '2>&' . $part[1];
                    }
                    else 
                    {
                        $cmd .= '2>' . escapeshellarg( $part[1] );
                    }
                break;
            }
        }
        return $cmd;
    }
}
