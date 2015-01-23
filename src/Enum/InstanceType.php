<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\BackgroundProcess\Enum;

use Exception;
use Hopeter1018\BackgroundProcess\BackgroundProcess;

/**
 * Description of InstanceType
 * <ul>
 * <li>SINGLE</li>
 * <li>MULTI</li>
 * </ul>
 * @version $id$
 * @author peter.ho
 */
final class InstanceType extends BackgroundProcessEnum
{

    /**
     * Only 1 instance can be run in the same time
     */
    CONST SINGLE = 1;

    /**
     * Multiple instance(s) can be run in the same time
     */
    CONST MULTI = 2;

    /**
     *
     * @var int 
     */
    protected $value = self::SINGLE;

// <editor-fold defaultstate="collapsed" desc="Enum Shortcut">

    /**
     * Return single
     * @link InstanceType::SINGLE
     * @return \self
     */
    public static function single()
    {
        return new self(self::SINGLE);
    }

    /**
     * Return multi
     * @link InstanceType::SINGLE
     * @return \self
     */
    public static function multi()
    {
        return new self(self::MULTI);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Events">

    public function toArray()
    {
        return array($this->value);
    }

    public static function fromArray($array)
    {
        list($value) = $array;
        $new = new static($value);
        return $new;
    }

    public static function isAllowed(BackgroundProcess $bp)
    {
        $isAllowed = true;
        if ($bp->getInstanceType()->is(self::single())) {
            if ($bp->isRunning()) {
                $isAllowed = $bp->isLastTickExpired();
            }
        }
        return $isAllowed;
    }

// </editor-fold>

}
