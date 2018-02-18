<?php

namespace Firesphere\BootstrapMFA\Generators;

use SilverStripe\Core\Config\Configurable;

/**
 * Class CodeGenerator
 *
 * @package Firesphere\BootstrapMFA\Generators
 */
class CodeGenerator
{
    use Configurable;

    const CHARS_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const CHARS_LOWER = 'abcdefghijklmnopqrstuvqxyz';

    const NUMBERS = '0123456789';

    const CASE_UPPER = 'upper';

    const CASE_LOWER = 'lower';

    const CASE_MIXED = 'mixed';

    const TYPE_ALPHA = 'alpha';

    const TYPE_NUMERIC = 'numeric';

    const TYPE_ALNUM = 'alnum';

    protected static $global_inst;

    private $case;

    private $type;

    private $length;

    private $validChars;

    public static function inst()
    {
        return singleton(static::class);
    }

    public function uppercase()
    {
        $this->case = self::CASE_UPPER;

        return $this;
    }

    public function lowercase()
    {
        $this->case = self::CASE_LOWER;

        return $this;
    }

    public function numbersonly()
    {
        $this->type = self::TYPE_NUMERIC;

        return $this;
    }

    public function charactersonly()
    {
        $this->type = self::TYPE_ALPHA;

        return $this;
    }

    public function setChars($chars)
    {
        $this->validChars = $chars;

        return $this;
    }

    public function __toString()
    {
        return $this->generate();
    }

    public function generate()
    {
        $chars = $this->validChars();
        $numChars = strlen($chars) - 1;
        $length = $this->getLength();
        $code = array();
        for ($i = 0; $i < $length; ++$i) {
            $code[] = $chars[mt_rand(0, $numChars)];
        }

        return implode('', $code);
    }

    private function validChars()
    {
        if ($this->validChars) {
            return $this->validChars;
        }
        $chars = array();
        $type = $this->getType();
        $case = $this->getCase();
        if ($type == self::TYPE_ALNUM || $type == self::TYPE_NUMERIC) {
            $chars[] = self::NUMBERS;
        }
        if ($type == self::TYPE_ALNUM || $type == self::TYPE_ALPHA) {
            if ($case == self::CASE_MIXED || $case == self::CASE_LOWER) {
                $chars[] = self::CHARS_LOWER;
            }
            if ($case == self::CASE_MIXED || $case == self::CASE_UPPER) {
                $chars[] = self::CHARS_UPPER;
            }
        }

        return implode('', $chars);
    }

    public function getType()
    {
        return $this->type ?: static::global_inst()->getType();
    }

    public static function global_inst()
    {
        if (!static::$global_inst) {
            static::$global_inst = (new static())
                ->alphanumeric()
                ->mixedcase()
                ->setLength(6);
        }

        return static::$global_inst;
    }

    public function mixedcase()
    {
        $this->case = self::CASE_MIXED;

        return $this;
    }

    public function alphanumeric()
    {
        $this->type = self::TYPE_ALNUM;

        return $this;
    }

    public function getCase()
    {
        return $this->case ?: static::global_inst()->getCase();
    }

    public function getLength()
    {
        return $this->length ?: static::global_inst()->getLength();
    }

    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }
}
