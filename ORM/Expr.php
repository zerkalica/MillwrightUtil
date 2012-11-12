<?php
namespace Millwright\Util\ORM;

use Doctrine\ORM\Query\Expr as BaseExpr;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;

use Millwright\Util\DateUtil;

/**
 * Query builder expression helper
 */
class Expr extends BaseExpr
{
    /**
     * @see Expr::stringLike()
     */
    const STRING_STARTS = 1;
    const STRING_ENDS   = 2;
    const STRING_EQ     = 3;
    const STRING_BOTH   = 4;

    /**
     * Date expression
     *
     * @param \DateTime $date
     * @param string    $pattern
     * @param string    $empty
     *
     * @return \Doctrine\ORM\Query\Expr\Literal|string
     */
    protected function dateExpr(\DateTime $date = null, $pattern = DateUtil::SQL_DATE, $empty = 'NULL')
    {
        return $date ? $this->literal($date->format($pattern)) : $empty;
    }

    /**
     * Sql formatted date string
     *
     * @param \DateTime|null $date
     * @param string         $empty
     *
     * @return \Doctrine\ORM\Query\Expr\Literal
     */
    public function date(\DateTime $date = null)
    {
        return $this->dateExpr($date, DateUtil::SQL_DATE, 'NULL');
    }

    /**
     * Sql formatted date time string
     *
     * @param \DateTime|null $date
     *
     * @return \Doctrine\ORM\Query\Expr\Literal
     */
    public function dateTime(\DateTime $date = null)
    {
        return $this->dateExpr($date, DateUtil::SQL_DATE_TIME, 'NULL');
    }

    /**
     * Date expression for where part
     *
     * @param \DateTime $date
     *
     * @return \Doctrine\ORM\Query\Expr\Literal|string
     */
    public function whereDate(\DateTime $date = null)
    {
        return $this->dateExpr($date, DateUtil::SQL_DATE, 'IS NULL');
    }

    /**
     * Date time expression for where part
     *
     * @param \DateTime $date
     *
     * @return \Doctrine\ORM\Query\Expr\Literal|string
     */
    public function whereDateTime(\DateTime $date = null)
    {
        return $this->dateExpr($date, DateUtil::SQL_DATE_TIME, 'IS NULL');
    }

    /**
     * Returns between expression if min and max not null
     * Returns lte expression if max is null
     * Returns gte expression if min is null
     *
     * @param  string  $field field name
     * @param  integer $min   minimum value
     * @param  integer $max   maximum value
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison|string
     */
    public function inRange($field, $min, $max)
    {
        if (!$min && !$max) {
            return null;
        }

        if ($min === null) {
            // $max exists
            $findExpression = $this->lte($field, (float) $max);
        } else {
            if ($max === null) {
                // $min exists
                $findExpression = $this->gte($field, (float) $min);
            } else {
                //both $min and $max exists
                $findExpression = $this->between(
                    $field,
                    (integer) $min,
                    (integer) $max
                );
            }
        }

        return $findExpression;
    }

    /**
     * Prepare value for like operation
     *
     * @param string  $value
     * @param integer $type one of Expr::STRING_*
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function convertTypeToMask($value, $type)
    {
        switch ($type) {
            case self::STRING_STARTS:
                $value = '%' . $value;
                break;

            case self::STRING_ENDS:
                $value .= '%';
                break;

            case self::STRING_BOTH:
                $value = '%' . $value . '%';
                break;

            case self::STRING_EQ:
                //return $e->eq($field, $e->literal($value));
                break;

            default:
                throw new \InvalidArgumentException('Wrong type constant in string like expression mapper');
        }

        return $value;
    }

    /**
     * Get string like expression
     *
     * @param string  $field field name
     * @param string  $value string value
     * @param integer $type  one of Expr::STRING_* constant
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function stringLike($field, $value, $type = self::STRING_BOTH)
    {
        $value = $this->convertTypeToMask($value, $type);

        return $this->like($field, $this->literal($value));
    }

    /**
     * Get like expression with string start matching rule
     *
     * @param string $field
     * @param string $value
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     *
     * @see    Expr::stringLike()
     */
    public function stringStarts($field, $value)
    {
        return $this->stringLike($field, $value, self::STRING_STARTS);
    }

    /**
     * Get like expression with string end matching rule
     *
     * @param string $field
     * @param string $value
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     *
     * @see    Expr::stringLike()
     */
    public function stringEnds($field, $value)
    {
        return $this->stringLike($field, $value, self::STRING_ENDS);
    }

    /**
     * Get like expression with both string and end string matching rule
     *
     * @param string $field
     * @param string $value
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     *
     * @see    Expr::stringLike()
     */
    public function stringBoth($field, $value)
    {
        return $this->stringLike($field, $value, self::STRING_BOTH);
    }

    /**
     * Get like expression with equal string matching rule
     *
     * @param string $field
     * @param string $value
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     *
     * @see Expr::stringLike()
     */
    public function stringEq($field, $value)
    {
        return $this->stringLike($field, $value, self::STRING_EQ);
    }

    /**
     * Get like expressions for any matching word, separated by space or array elements
     *
     * @param string       $field
     * @param array|string $values
     * @param integer      $type one of self::STRING_*
     *
     * @return Orx
     */
    public function stringLikeAnyWord($field, $values, $type = self::STRING_BOTH)
    {
        if (!is_array($values)) {
            $values = explode(' ', $values);
        }

        $exprs = array();

        foreach ($values as $value) {
            $exprs[] = $this->stringLike($field, $value, $type);
        }

        return new Orx($exprs);
    }

    /**
     * Get like expression for any matching word with string end matching rule
     *
     * @param string $field
     * @param string $value
     *
     * @return Orx
     *
     * @see Expr::stringLikeAnyWord()
     */
    public function stringEndsAnyWord($field, $value)
    {
        return $this->stringLikeAnyWord($field, $value, self::STRING_ENDS);
    }

    /**
     * Get like expression  for any matching word with both string and end string matching rule
     *
     * @param string $field
     * @param string $value
     *
     * @return Orx
     *
     * @see Expr::stringLikeAnyWord()
     */
    public function stringBothAnyWord($field, $value)
    {
        return $this->stringLikeAnyWord($field, $value, self::STRING_BOTH);
    }

    /**
     * Get like expression  for any matching word with equal string matching rule
     *
     * @see Expr::stringLikeAnyWord()
     *
     * @param string $field
     * @param string $value
     *
     * @return Orx
     */
    public function stringEqAnyWord($field, $value)
    {
        return $this->stringLikeAnyWord($field, $value, self::STRING_EQ);
    }

    /**
     * Creates an instance of Expr\Comparison, with the given arguments
     *
     * Processing if right expression is NULL
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> <= <right expr>. Example:
     *
     *
     *     // u.id <= ?1
     *     $q->where($q->expr()->lte('u.id', '?1'));
     *
     * @param mixed $x left expression
     * @param mixed $y right expression
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function lteNull($x, $y)
    {
        return null === $y ? null : new \Doctrine\ORM\Query\Expr\Comparison($x, \Doctrine\ORM\Query\Expr\Comparison::LTE, $y);
    }

    /**
     * Creates an instance of Expr\Comparison, with the given arguments.
     * Processing if right expression is NULL
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> >= <right expr>. Example:
     *
     *     [php]
     *     // u.id >= ?1
     *     $q->where($q->expr()->gte('u.id', '?1'));
     *
     * @param    mixed $x Left expression
     * @param    mixed $y Right expression
     *
     * @return   \Doctrine\ORM\Query\Expr\Comparison
     */
    public function gteNull($x, $y)
    {
        if (is_null($y)) {
            return null;
        } else {
            return new \Doctrine\ORM\Query\Expr\Comparison($x, \Doctrine\ORM\Query\Expr\Comparison::GTE, $y);
        }
    }

    /**
     * Is value is null or in array or equal to given value
     *
     * @param  string                $field
     * @param  string|int|null|array $value
     * @param  boolean               $literal if $value is array - add slashes to each element
     *
     * @return string DQL expression
     */
    public function inEq($field, $value, $literal = false)
    {
        if ($value === null) {
            $result = $this->isNull($field);
        } else if (is_array($value)) {
            $value  = $this->inValue($value, $literal);
            $result = $this->in($field, $value);
        } else {
            if (is_string($value)) {
                $value = $this->literal($value);
            }
            $result = $this->eq($field, $value);
        }

        return $result;
    }

    /**
     * Is value is not null or not in array or not equal to given value
     *
     * @param  string                $field
     * @param  string|int|null|array $value
     * @param  boolean               $literal if $value is array - add slashes to each element
     *
     * @return string DQL expression
     */
    public function inNotEq($field, $value, $literal = false)
    {
        if ($value === null) {
            $result = $this->isNotNull($field);
        } else if (is_array($value)) {
            $value  = $this->inValue($value, $literal);
            $result = $this->notIn($field, $value);
        } else {
            if (is_string($value)) {
                $value = $this->literal($value);
            }
            $result = $this->neq($field, $value);
        }

        return $result;
    }

    /**
     * Add slashes to each array elemets and extract ids from object
     *
     * @param array $value
     * @param boolean $literal
     *
     * @return array
     */
    private function inValue(array $value, $literal = false)
    {
        $result = array();
        foreach ($value as $v) {
            if (is_object($v) && method_exists($v, 'getId')) {
                $v = $v->getId();
            }
            if ($literal) {
                $v = $this->literal($v);
            }
            if (null !== $v) {
                $result[] = $v;
            }
        }

        return $result;
    }

    /**
     * Aggregate all ids from domains, literalize it and implode to string
     *
     * @param object[] $domains
     *
     * @return string
     */
    public function extractIds(array $domains)
    {
        $result = array();
        foreach ($domains as $v) {
            if (is_object($v) && method_exists($v, 'getId')) {
                $result[] = $v->getId();
            }
        }

        return $result;
    }

    /**
     * Aggregate all values from array, literalize it and implode to string,
     *
     * @param stirng[] $array
     *
     * @return string
     */
    public function inArray(array $array)
    {
        return implode(', ', $this->inValue($array, true));
    }
}
