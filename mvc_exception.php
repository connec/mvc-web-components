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
Class MVCException extends \Exception {
	
	/**
	 * A hash of css element names to use in markup.
	 * 
	 * @var array
	 * @since 1.2
	 */
	public static $elementClasses = array(
		'wrapper' => 'exception',
		'heading' => 'exception-heading',
		'message' => 'exception-message',
		'context' => 'exception-context',
		'trace-wrapper' => 'exception-trace',
		'trace-row-even' => 'exception-trace-row-even',
		'trace-row-odd' => 'exception-trace-row-odd'
	);
	
	/**
	 * Returns an HTML formatted message and backtrace.
	 * 
	 * @return string The formatted message.
	 * @since 1.1
	 */
	public function getFormattedMsg() {
		
		$type = get_class($this);
		$output = '<pre class="' . self::$elementClasses['wrapper'] . '">';
		$output .= '<h1 class="' . self::$elementClasses['heading'] . "\">Unhandled Exception <i>$type</i></h1>";
		$output .= '<p class="' . self::$elementClasses['message'] . '">' . $this->getMessage() . ' - Code: ' . $this->getCode() . '<br/>';
		$output .= '<span class="' . self::$elementClasses['context'] . '">Triggered on line ' . $this->getLine() . ' in ' . $this->getFile() . '.</span></p>';
		$output .= '<p class="' . self::$elementClasses['trace-wrapper'] . '">';
		$i = count($this->getTrace());
		foreach($this->getTrace() as $trace) {
			@$output .= '<span class="' . self::$elementClasses['exception-trace-row-' . ($i % 2 == 0 ? 'even' : 'odd')] . "\">$i: {$trace['class']}{$trace['type']}{$trace['function']}() <i>{$trace['file']} line {$trace['line']}</i></span><br/>";
			$i -= 1;
		}
		$output .= '</p></pre>';
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
Class BadArgumentException extends MVCException {
	
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