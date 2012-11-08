<?php
namespace Millwright\Util;

/**
 * PHP hacks and fixes utils
 */
final class PhpUtil
{
    const SHIFT_COUNT = 2;

    /**
     * Smart merge
     *
     * Array_merge rewrite all elements in second level
     * array_merge_recursive adds elements and makes arrays from strings
     *
     * @param array $to
     * @param array $from
     *
     * @return array
     *
     *
     * @example
     * <code>
     *     $this->merge(array(
     *         'level1' => array('param1' => '1', 'params2' => array('a' => 'b'))
     *     ), array(
     *         'level1' => array('param1' => '2', 'params2' => array('a' => 'c', 'd' => 'e'))
     *     ));
     *
     *     //result:
     *     array(
     *         'level1' => array('param1' => '2', 'params2' => array('a' => 'c'), 'd' => 'e')
     *     );
     * </code>
     */
    public static function merge(array $to, array $from)
    {
        foreach ($from as $key => $value) {
            if (!is_array($value)) {
                if (is_int($key)) {
                    $to[] = $value;
                } else {
                    $to[$key] = $value;
                }
            } else {
                if (!isset($to[$key])) {
                    $to[$key] = array();
                }

                $to[$key] = self::merge($to[$key], $value);
            }
        }

        return $to;
    }

    public static function flattenArray(array $config, $prefix = '')
    {
        $result = array();
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, self::flattenArray($value, $key . '.'));
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    /**
     * Clone object, null-compatible
     *
     * @param object|null &$object
     *
     * @return object|null cloned object or null
     */
    static public function cloneObject(&$object = null)
    {
        return null === $object ? null : clone $object;
    }

    /**
     * Convert model constants to select element options
     *
     * @param string $class       class name with constants
     * @param string $prefix      array values prefix
     * @param string $localPrefix this prefix will be removed from result, if each constants has an TYPE_* like prefix
     * @param string $separator   replace underscores to this separator in constants
     *
     * @return array
     *
     * @example:
     * <code>
     * ex1:
     *
     * class UserRolesEnum {
     *     const ROLE_USER  = 'user';
     *     const ROLE_ADMIN = 'admin';
     * }
     *
     * $array = convertConstantsToOptions('UserRolesEnum');
     *
     * returns:
     * array(
     *     'user' => 'role.user',
     *     'admin'=> 'role.admin',
     * );
     *
     *
     * ex2:
     *
     * interface UserInterface {
     *     const ROLE_USER  = 'user';
     *     const ROLE_ADMIN = 'admin';
     *
     *     const STATUS_ENABLED  = 1;
     *     const STATUS_DISABLED = 0;
     * }
     *
     * $array = convertConstantsToOptions('UserInterface', 'ROLE');
     *
     * returns:
     * array(
     *     'user'  => 'role.user',
     *     'admin' => 'role.admin',
     * );
     *
     * $array = convertConstantsToOptions('UserInterface', 'STATUS');
     *
     * returns:
     * array(
     *     '1' => 'status.enabled',
     *     '0' => 'status.disabled',
     * );
     * </code>
     */
    static public function convertConstantsToOptions($class, $prefix = '', $localPrefix = '', $separator = '.')
    {
        $choices = array();
        if ($localPrefix && !$localPrefix[(strlen($localPrefix) - 1)] != '_') {
            $localPrefix .= '_';
        }

        if (!$prefix) {
            //autodetect prefix from namespace and class name
            $prefixes = explode('\\', trim($class, '\\'));

            for ($i = 0; $i < self::SHIFT_COUNT; $i++) {
                array_shift($prefixes);
            }

            $prefix = implode($separator, $prefixes);
        }

        $prefix .= $separator;

        $reflection = new \ReflectionClass($class);
        foreach ($reflection->getConstants() as $name => $value) {
            if (!$localPrefix || $localPrefix === substr($name, 0, strlen($localPrefix))) {

                $choices[$value] = $prefix . strtolower(
                    str_replace(
                        array($localPrefix, '_'), array('', $separator),
                        $name
                    )
                );
            }
        }

        return $choices;
    }
}
