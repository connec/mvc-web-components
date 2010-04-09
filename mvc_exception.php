<?php

/**
 * Globally useful exception classes.
 * 
 * @package mvc-web-components
 * @subpackage exceptions
 * @author Chris Connelly
 */
namespace MVCWebComponents;

/**
 * Overrides some behaviour of Exception.
 * 
 * @version 1.2
 */
class MVCException extends \Exception {
	
	/**
	 * Returns an HTML formatted message and backtrace.
	 * 
	 * @return string The formatted message.
	 * @since 1.1
	 */
	public function getFormattedMsg() {
		
		$type = @end(explode('\\', get_class($this)));
		$output = '<pre class="exception">';
		$output .= "<h1>Unhandled Exception <span class=\"emphasis\">$type</span></h1>";
		$output .= '<p class="exception-message">' . $this->getMessage() . ' - Code: ' . $this->getCode() . '<br/>';
		$output .= '<span class="exception-context">Triggered on line ' . $this->getLine() . ' in ' . $this->getFile() . '.</span></p>';
		$output .= Debug::formatTrace($this->getTrace());
		$output .= '</pre>';
		return $output;
		
	}
	
}

/**
 * Bad argument exception.
 *
 * An exception thrown when a function encounters an invalid argument.
 *
 * @version 1.1
 */
class BadArgumentException extends MVCException {
	
	/**
	 * Sets the message.
	 * 
	 * @param string $details Details of the bad argument.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($details) {
		
		$this->message = "Invalid argument: $details";
		
	}
	
}

?>