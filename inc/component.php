<?php

// where all the components should reside
define(DOKU_COMPONENTS_ROOT, DOKU_INC . 'inc/components');

/** Doku_Component is the super class of all components
 *  This class does not specify any component interface, 
 *  The interface of each type of components should be specified by
 *  the subclasses of this class, residing in DOKU_COMPONENTS_ROOT folder
 */ 
class Doku_Component {
	////////////////////////////////////////////////////
	// Class static methods to implement components auto loading
	////////////////////////////////////////////////////

	// The array of all components that are initialized, 
	// keyed by their names.
	private static $_components = array();
	// The list of all extensions (an array of subclasses) 
	// of a given component, keyed by the component class names
	// entries with empty extensions are initialized and put in 
	// the $_components array
	private static $_extensions = array();
	
	/** the entrance function to initialize and use the whole 
	 *  component system. */
	public static function init() {
		// make a note of all defined classes, before components are loaded
		$old_classes = get_declared_classes();
		// load all the components
		self::load(DOKU_COMPONENTS_ROOT);
		// take another look at all the defined classes, now with 
		// all the components
		$classes = get_declared_classes();
		// tkae a difference to find all the classes defined in components
		$new_classes = array_diff($classes, $old_classes);
		// check if these classes extend components
		// if they do, register with their parent classes for extensions
		foreach ($new_classes as $class) {
			if (is_subclass_of($class, "Doku_Component")) 
				self::register_extension($class);
		}
		// initialize all the classes that are not extended. 
		// They are components that we should use.
		foreach(self::$_extensions as $component => $extensions)
			if (!$extensions && !self::is_abstract_class($component)) 
				array_push(self::$_components, new $component);
	}

	// this function registers $class as an extension of its parent class.
	private static function register_extension($class) {
		$parent = get_parent_class($class);
		if ($parent) {
			if (!array_key_exists("$parent", self::$_extensions))
				self::register_extension($parent);
			array_push(self::$_extensions[$parent], $class);
		}
		self::$_extensions[$class] = array();
	}

	// This is a utility function to check if a class is abstract
	// abstract component classes will not be initialized.
	private static function is_abstract_class($class) {
		$ref_class = new ReflectionClass($class);
		$abs = $ref_class->isAbstract();
		unset($ref_class);
		return $abs;
	}

	// this function is a utility function to check if a file 
	// is a php script.
	private static function is_php_script($fname) {
		$ext = substr($fname, -4);
		return strtolower($ext) === ".php";
	}

	/** load($dir, $files, $recursive) loads all the components 
	 *  specified in $files in the given directory in $dir,
	 *  optionally recursively.
	 */
	public static function load($dir, $files=array(), $recursive = TRUE) {
		// use DOKU_COMPONENTS_ROOT by default
		if (!$dir) $dir = DOKU_COMPONENTS_ROOT;
		// if no files are given, read all files, recursively
		if (!$files) {
			$d = dir($dir);
			while (false !== ($entry = $d->read())) {
				if ($entry !== "." && $entry !== "..") 
					array_push($files, $entry);
			}
			$d->close();
		}
		// if $files is a string specifying a single file, 
		// put it in an array
		else if (is_string($files)) 
			$files = array($files);
		// collect all the subdirs to load after the php scripts, 
		// if specified recursively
		$subdirs = array();
		// load all the scripts in $dir, one by one
		foreach ($files as $file) {
			$fname = $dir . DIRECTORY_SEPARATOR . $file;
			if (!file_exists($fname)) continue;
			if (is_dir($fname))
				array_push($subdirs, $fname);
			else if (self::is_php_script($fname))
				include_once($fname);
		}
		// if recursive, load scripts and subsubdirs in $subdirs
		if ($recursive)
			foreach ($subdirs as $subdir) {
				self::load($subdir, array(), $recursive);
			}
	}
	
	// this is a debug function.
	public static function print_components() {
		echo "<H1>Dependencies</H1>\n";
		print_r(self::$_extensions);
		echo "\n<H1>Components</H1>\n<ul>\n";
		foreach (self::$_components as $component)
			echo "<li>" . get_class($component) . "</li>";
		echo "</ul><p>";
	}
}

Doku_Component::init();
#Doku_Component::print_components();
