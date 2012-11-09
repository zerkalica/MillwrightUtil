<?php
namespace Millwright\Util;

use Assert\AssertionFailedException;

use Assert\Assertion as AssertionBase;

/**
 * PHP Assertions
 */
class Assertion extends AssertionBase
{
    const INVALID_CONSTANT = 202;
    const INVALID_OBJECT   = 203;

    /**
     * Assert that constant exists in class.
     *
     * @param string $constant constant in this class
     * @param string $class    class name with constants
     * @param string $message
     * @param string $propertyPath
     *
     * @throws AssertionFailedException
     */
    static public function constantExists($constant, $class, $message = null, $propertyPath = null)
    {
        $refClass = new \ReflectionClass($class);

        if (!in_array($constant, $refClass->getConstants())) {
            if (!$message) {
                $message = strtr('Error constant: %const%, available constants: %keys%', array(
                        '%const%' => $constant,
                        '%class%' => $class,
                        '%keys%'  => implode(', ', array_keys($refClass->getConstants())),
                    )
                );
            }

            throw new AssertionFailedException($message, self::INVALID_CONSTANT, $propertyPath);
        }
    }

    /**
     * Assert that value is object.
     *
     * @param mixed $value
     * @param string $message;
     * @return void
     * @throws AssertionFailedException
     */
    static public function isObject($value, $message = null, $propertyPath = null)
    {
        if ( ! is_object($value)) {
            throw new AssertionFailedException($message, self::INVALID_OBJECT, $propertyPath);
        }
    }
}
