<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\BackgroundProcess\Enum;

use Exception;
use ReflectionClass;
use Hopeter1018\BackgroundProcess\BackgroundProcess;

/**
 * Description of BackgroundProcessEnum
 *
 * @version $id$
 * @author peter.ho
 */
abstract class BackgroundProcessEnum implements BackgroundProcessEnumInterface
{

    /**
     *
     * @var int 
     */
    protected $value = null;
    
    protected static $init = false;
    protected static $nameValue = array();
    protected static $valueName = array();

    abstract public function toArray();

// <editor-fold defaultstate="collapsed" desc="Init">

    /**
     * 
     */
    public function __construct($value)
    {
        if (! static::$init) {
            $refl = new ReflectionClass($this);
            static::$nameValue = $refl->getConstants();
            static::$valueName = array_flip(static::$nameValue);
            static::$init = true;
        }
        if (! in_array($value, static::$nameValue)) {
            throw new Exception('No such value in this enum (' . __CLASS__ . ')');
        }
        $this->value = $value;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Getters">

    public function getValue()
    {
        return $this->value;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Compare Operators">

    /**
     * 
     * @param static|self $enum
     * @return boolean
     */
    public function is($enum)
    {
        return $this->value === $enum->value;
    }

    /**
     * 
     * @param int $value
     * @return boolean
     */
    public function isValue($value)
    {
        return $this->value === $value;
    }

// </editor-fold>

}
