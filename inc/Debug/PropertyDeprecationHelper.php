<?php
/**
 * Trait for issuing warnings on deprecated access.
 *
 * Adapted from https://github.com/wikimedia/mediawiki/blob/4aedefdbfd193f323097354bf581de1c93f02715/includes/debug/DeprecationHelper.php
 *
 */


namespace dokuwiki\Debug;

/**
 * Use this trait in classes which have properties for which public access
 * is deprecated. Set the list of properties in $deprecatedPublicProperties
 * and make the properties non-public. The trait will preserve public access
 * but issue deprecation warnings when it is needed.
 *
 * Example usage:
 *     class Foo {
 *         use DeprecationHelper;
 *         protected $bar;
 *         public function __construct() {
 *             $this->deprecatePublicProperty( 'bar', '1.21', __CLASS__ );
 *         }
 *     }
 *
 *     $foo = new Foo;
 *     $foo->bar; // works but logs a warning
 *
 * Cannot be used with classes that have their own __get/__set methods.
 *
 */
trait PropertyDeprecationHelper
{

    /**
     * List of deprecated properties, in <property name> => <class> format
     * where <class> is the the name of the class defining the property
     *
     * E.g. [ '_event' => '\dokuwiki\Cache\Cache' ]
     * @var string[]
     */
    protected $deprecatedPublicProperties = [];

    /**
     * Mark a property as deprecated. Only use this for properties that used to be public and only
     *   call it in the constructor.
     *
     * @param string $property The name of the property.
     * @param null $class name of the class defining the property
     * @see DebugHelper::dbgDeprecatedProperty
     */
    protected function deprecatePublicProperty(
        $property,
        $class = null
    ) {
        $this->deprecatedPublicProperties[$property] = $class ?: get_class();
    }

    public function __get($name)
    {
        if (isset($this->deprecatedPublicProperties[$name])) {
            $class = $this->deprecatedPublicProperties[$name];
            DebugHelper::dbgDeprecatedProperty($class, $name);
            return $this->$name;
        }

        $qualifiedName = get_class() . '::$' . $name;
        if ($this->deprecationHelperGetPropertyOwner($name)) {
            // Someone tried to access a normal non-public property. Try to behave like PHP would.
            trigger_error("Cannot access non-public property $qualifiedName", E_USER_ERROR);
        } else {
            // Non-existing property. Try to behave like PHP would.
            trigger_error("Undefined property: $qualifiedName", E_USER_NOTICE);
        }
        return null;
    }

    public function __set($name, $value)
    {
        if (isset($this->deprecatedPublicProperties[$name])) {
            $class = $this->deprecatedPublicProperties[$name];
            DebugHelper::dbgDeprecatedProperty($class, $name);
            $this->$name = $value;
            return;
        }

        $qualifiedName = get_class() . '::$' . $name;
        if ($this->deprecationHelperGetPropertyOwner($name)) {
            // Someone tried to access a normal non-public property. Try to behave like PHP would.
            trigger_error("Cannot access non-public property $qualifiedName", E_USER_ERROR);
        } else {
            // Non-existing property. Try to behave like PHP would.
            $this->$name = $value;
        }
    }

    /**
     * Like property_exists but also check for non-visible private properties and returns which
     * class in the inheritance chain declared the property.
     * @param string $property
     * @return string|bool Best guess for the class in which the property is defined.
     */
    private function deprecationHelperGetPropertyOwner($property)
    {
        // Easy branch: check for protected property / private property of the current class.
        if (property_exists($this, $property)) {
            // The class name is not necessarily correct here but getting the correct class
            // name would be expensive, this will work most of the time and getting it
            // wrong is not a big deal.
            return __CLASS__;
        }
        // property_exists() returns false when the property does exist but is private (and not
        // defined by the current class, for some value of "current" that differs slightly
        // between engines).
        // Since PHP triggers an error on public access of non-public properties but happily
        // allows public access to undefined properties, we need to detect this case as well.
        // Reflection is slow so use array cast hack to check for that:
        $obfuscatedProps = array_keys((array)$this);
        $obfuscatedPropTail = "\0$property";
        foreach ($obfuscatedProps as $obfuscatedProp) {
            // private props are in the form \0<classname>\0<propname>
            if (strpos($obfuscatedProp, $obfuscatedPropTail, 1) !== false) {
                $classname = substr($obfuscatedProp, 1, -strlen($obfuscatedPropTail));
                if ($classname === '*') {
                    // sanity; this shouldn't be possible as protected properties were handled earlier
                    $classname = __CLASS__;
                }
                return $classname;
            }
        }
        return false;
    }
}
