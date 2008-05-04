<?php
    /**
     *	base include file for SimpleTest
     *	@package	SimpleTest
     *	@subpackage	MockFunctions
     *	@version	$Id: mock_objects.php,v 1.86 2005/09/10 23:01:56 lastcraft Exp $
     */
    
    /**
     *    Generates a mock version of a function.
     *    Note that all public methods in this class should be called
     *    statically
     *    Note that you must call the restore method yourself, to remove
     *    a mock function implementation after associated tests are
     *    complete
     *    @package SimpleTest
     *    @subpackage MockFunctions
     */
    class MockFunction {
        
        /**
         *    Raises an error if you construct MockFunction
         *    @access private
         */
        function MockFunction() {
            trigger_error('MockFunction only provides static methods',
                E_USER_ERROR);
        }
        
        /**
         *    Generates a mock function
         *    @param string $function        Function name to mock
         *    @access public
         *    @return SimpleMockFunction
         *    @static
         */
        function & generate($function) {
            $mock = & MockFunction::_instance($function, TRUE);
            $mock->deploy();
            return $mock;
        }
        
        /**
         *    Removes the mock function implementation and restores
         *    the real implementation (if one existed)
         *    @TODO Would be good to have this called automatically
         *    @param string $function        Function name
         *    @access public
         *    @static
         */
        function restore($function) {
            $mock = & MockFunction::_instance($function);
            $mock->restore();
        }
        
        /**
         *    Fetch a singleton instance of SimpleMockFunction
         *    @param string $function    Function name
         *    @param boolean $fresh      Force a fresh instance
         *    @access private
         *    @static
         */
        function &_instance($function, $fresh = FALSE) {
            static $singleton = array();
            
            $function = strtolower($function);
            
            if ( $fresh ) {
                if ( isset($singleton[$function]) ) {
                    unset($singleton[$function]);
                }
            }
            
            if ( !isset($singleton[$function]) ) {
                // TODO: case sensitivity issues
                $class = $function."MockFunction";
                MockFunction::_generateSubClass($class, $function);
                $singleton[$function] = new $class($function);
            }
            
            return $singleton[$function];
        }
        
        /**
         *    Required for strict mode and SimpleMock
         *    @TODO Should perhaps be placed in SimpleFunctionGenerator
         *    @param string $class        subclass name
         *    @param string $method       method name
         *    @access private
         *    @static
         */
        function _generateSubClass($class, $method) {
            if ( class_exists($class) ) {
                return;
            }
            $code = "class $class extends SimpleMockFunction {\n";
            $code .= "    function $method () {}\n";
            $code .= "}\n";
            eval($code);
        }
        
        /**
         *    Changes the default wildcard object.
         *    @param string $function        Function name wildcard applies to
         *    @param mixed $wildcard         Parameter matching wildcard.
         *    @access public
         *    @static
         */
        function setWildcard($function, $wildcard) {
            $mock = & MockFunction::_instance($function);
            $mock->setWildcard($wildcard);
        }
        
        /**
         *    Fetches the call count of a function so far.
         *    @param string $function        Function name called.
         *    @return                        Number of calls so far.
         *    @access public
         *    @static
         */
        function getCallCount($function) {
            $mock = & MockFunction::_instance($function);
            return $mock->getCallCount($function);
        }
        
        /**
         *    Sets a return for a parameter list that will
         *    be passed by value for all calls to this function.
         *    @param string $function     Function name.
         *    @param mixed $value         Result of call passed by value.
         *    @param array $args          List of parameters to match
         *                                including wildcards.
         *    @access public
         *    @static
         */
        function setReturnValue($function, $value, $args = false) {
            $mock = & MockFunction::_instance($function);
            $mock->setReturnValue($function, $value, $args);
        }
                
        /**
         *    Sets a return for a parameter list that will
         *    be passed by value only when the required call count
         *    is reached.
         *    @param integer $timing   Number of calls in the future
         *                             to which the result applies. If
         *                             not set then all calls will return
         *                             the value.
         *    @param string $function  Function name.
         *    @param mixed $value      Result of call passed by value.
         *    @param array $args       List of parameters to match
         *                             including wildcards.
         *    @access public
         *    @static
         */
        function setReturnValueAt($timing, $function, $value, $args = false) {
            $mock = & MockFunction::_instance($function);
            $mock->setReturnValueAt($timing, $function, $value, $args);
        }
         
        /**
         *    Sets a return for a parameter list that will
         *    be passed by reference for all calls.
         *    @param string $function     Function name.
         *    @param mixed $reference     Result of the call will be this object.
         *    @param array $args          List of parameters to match
         *                                including wildcards.
         *    @access public
         *    @static
         */
        function setReturnReference($function, &$reference, $args = false) {
            $mock = & MockFunction::_instance($function);
            $mock->setReturnReference($function, $reference, $args);
        }
        
        /**
         *    Sets a return for a parameter list that will
         *    be passed by value only when the required call count
         *    is reached.
         *    @param integer $timing    Number of calls in the future
         *                              to which the result applies. If
         *                              not set then all calls will return
         *                              the value.
         *    @param string $function   Function name.
         *    @param mixed $reference   Result of the call will be this object.
         *    @param array $args        List of parameters to match
         *                              including wildcards.
         *    @access public
         *    @static
         */
        function setReturnReferenceAt($timing, $function, &$reference, $args = false) {
            $mock = & MockFunction::_instance($function);
            $mock->setReturnReferenceAt($timing, $function, $reference, $args);
        }
        
        /**
         *    Sets up an expected call with a set of
         *    expected parameters in that call. All
         *    calls will be compared to these expectations
         *    regardless of when the call is made.
         *    @param string $function      Function call to test.
         *    @param array $args           Expected parameters for the call
         *                                 including wildcards.
         *    @param string $message       Overridden message.
         *    @access public
         *    @static
         */
        function expectArguments($function, $args, $message = '%s') {
            $mock = & MockFunction::_instance($function);
            $mock->expectArguments($function, $args, $message);
        }
        
        /**
         *    Sets up an expected call with a set of
         *    expected parameters in that call. The
         *    expected call count will be adjusted if it
         *    is set too low to reach this call.
         *    @param integer $timing    Number of calls in the future at
         *                              which to test. Next call is 0.
         *    @param string $function   Function call to test.
         *    @param array $args        Expected parameters for the call
         *                              including wildcards.
         *    @param string $message    Overridden message.
         *    @access public
         *    @static
         */
        function expectArgumentsAt($timing, $function, $args, $message = '%s') {
            $mock = & MockFunction::_instance($function);
            $mock->expectArgumentsAt($timing, $function, $args, $message);
        }
        
        /**
         *    Sets an expectation for the number of times
         *    a function will be called.
         *    @param string $function      Function call to test.
         *    @param integer $count        Number of times it should
         *                                 have been called at tally.
         *    @param string $message       Overridden message.
         *    @access public
         *    @static
         */
        function expectCallCount($function, $count, $message = '%s') {
            $mock = & MockFunction::_instance($function);
            $mock->expectCallCount($function, $count, $message);
        }
        
        /**
         *    Sets the number of times a function may be called
         *    before a test failure is triggered.
         *    @param string $function      Function call to test.
         *    @param integer $count        Most number of times it should
         *                                 have been called.
         *    @param string $message       Overridden message.
         *    @access public
         *    @static
         */
        function expectMaximumCallCount($function, $count, $message = '%s') {
            $mock = & MockFunction::_instance($function);
            $mock->expectMaximumCallCount($function, $count, $message);
        }
        
        /**
         *    Sets the minimum number of times the function must be called
         *    otherwise a test failure is triggered
         *    @param string $function    Function call to test.
         *    @param integer $count      Least number of times it should
         *                               have been called.
         *    @param string $message     Overridden message.
         *    @access public
         *    @static
         */
        function expectMinimumCallCount($function, $count, $message = '%s') {
            $mock = & MockFunction::_instance($function);
            $mock->expectMinimumCallCount($function, $count, $message);
        }
        
        /**
         *    Convenience method for barring a function
         *    call.
         *    @param string $function      Function call to ban.
         *    @param string $message       Overridden message.
         *    @access public
         *    @static
         */
        function expectNever($function, $message = '%s') {
            $mock = & MockFunction::_instance($function);
            $mock->expectNever($function, $message);
        }
        
        /**
         *    Convenience method for a single function
         *    call.
         *    @param string $function   Function call to track.
         *    @param array $args        Expected argument list or
         *                              false for any arguments.
         *    @param string $message    Overridden message.
         *    @access public
         *    @static
         */
        function expectOnce($function, $args = false, $message = '%s') {
            $mock = & MockFunction::_instance($function);
            $mock->expectOnce($function, $args, $message);
        }
        
        /**
         *    Convenience method for requiring a function
         *    call.
         *    @param string $function     Function call to track.
         *    @param array $args          Expected argument list or
         *                                false for any arguments.
         *    @param string $message      Overridden message.
         *    @access public
         *    @static
         */
        function expectAtLeastOnce($function, $args = false, $message = '%s') {
            $mock = & MockFunction::_instance($function);
            $mock->expectAtLeastOnce($function, $args, $message);
        }
        
        function atTestEnd($function) {
            $mock = & MockFunction::_instance($function);
            $mock->atTestEnd($function);
        }
        
    }
    
    /**
     *    Represents a single, mocked function, tracking calls made to it
     *    @package SimpleTest
     *    @subpackage MockFunctions
     */
    class SimpleMockFunction extends SimpleMock {
    
        var $_is_mocked = FALSE;
        var $_generator;
        
        /**
         *    Sets up the mock, creating a generator depending on whether
         *    the function is already declared
         *    @param string $function    Name of function being mocked
         */
        function SimpleMockFunction($function) {
            
            SimpleMock :: SimpleMock();
            
            if ( function_exists($function) ) {
                $this->_generator = new SimpleDeclaredFunctionGenerator($function);
            } else {
                $this->_generator = new SimpleUndeclaredFunctionGenerator($function);
            }
            
        }
        
        /**
         *    Deploys the mock function implementation into PHP's function
         *    table, replacing any existing implementation
         *    @access public
         */
        function deploy() {
            
            if ( !$this->_is_mocked ) {
                
                $this->_is_mocked = TRUE;
                $this->_generator->deploy();
                
            }
            
        }
        
        /**
         *    Restores the state of PHP's function table to that before
         *    the mock function was deployed. Removes the mock function
         *    implementation and restores any existing implementation of
         *    that function
         *    @access public
         */
        function restore() {
            
            if ( $this->_is_mocked ) {
                
                $this->_is_mocked = FALSE;
                $this->_generator->restore();
                
            }
            
        }
        
    }
    
    /**
     *    Base class for deploying and restoring from mock functions
     *    @package SimpleTest
     *    @subpackage MockFunctions
     *    @abstract
     */
    class SimpleFunctionGenerator {
        
        var $_function;
        
        /**
         *    @TODO Validate the function name (as it's being used in eval)
         *    @TODO Add list of illegal functions (ones which must not be mocked
         *    as they will break SimpleTest, which uses them)
         *    @param string $function    Name of function being mocked
         */
        function SimpleFunctionGenerator($function) {
            $this->_function = $function;
        }
        
        /**
         *    Generates the mock function implementation, using eval
         *    @access private
         */
        function _generateMockFunction() {
            $code = "function " . $this->_function . "() {\n";
            $code .= "    \$args = func_get_args();\n";
            $code .= "    \$mock = & MockFunction::_instance('".$this->_function."');\n";
            $code .= "    \$result = &\$mock->_invoke(\"".$this->_function."\", \$args);\n";
            $code .= "    return \$result;\n";
            $code .= "}\n";
            eval($code);
        }
    }
    
    /**
     *    Mock function generator for functions which have already been declared
     *    @package SimpleTest
     *    @subpackage MockFunctions
     */
    class SimpleDeclaredFunctionGenerator extends SimpleFunctionGenerator {
        
        var $_tmp_function = NULL;
        
        /**
         *    Invokes the _generateTmpFnFname
         *    @param string $function    Name of function being mocked
         */
        function SimpleDeclaredFunctionGenerator($function) {
            
            SimpleFunctionGenerator::SimpleFunctionGenerator($function);
            $this->_generateTmpFnFname();
            
        }
        
        /**
         *    Generates a temporary name for the declared function implementation
         *    which is will be renamed to while the mock function is in use
         *    @access private
         */
        function _generateTmpFnFname() {
            static $count = 1;
            $this->_tmp_function = 'tmp_'.md5(time().$this->_function.$count);
            $count++;
        }
        
        /**
         *    Deploys the mock function implementation
         *    @access public
         */
        function deploy() {
            
            runkit_function_rename(
                $this->_function,
                $this->_tmp_function
                ) or
                    trigger_error('Error archiving real function implementation',
                    E_USER_ERROR);
            
            $this->_generateMockFunction();
        }
        
        /**
         *    Removes the mock function implementation and restores
         *    the previously declared implementation
         *    @access public
         */
        function restore() {
            
            runkit_function_remove($this->_function) or
                trigger_error('Error removing mock function',
                    E_USER_ERROR);
            
            runkit_function_rename(
                $this->_tmp_function,
                $this->_function
                ) or
                trigger_error('Error restoring real function',
                    E_USER_ERROR);
        }
    }
    
    /**
     *    Mock function generator for functions which have not
     *    already been declared
     *    @package SimpleTest
     *    @subpackage MockFunctions
     */
    class SimpleUndeclaredFunctionGenerator extends SimpleFunctionGenerator {
        
        /**
         *    Deploys the mock function implementation
         *    @access public
         */
        function deploy() {
            $this->_generateMockFunction();
        }
        
        /**
         *    Removes the mock function implementation
         *    @access public
         */
        function restore() {
            runkit_function_remove($this->_function) or
                trigger_error('Error removing mock function',
                    E_USER_ERROR);
        }
        
    }

