<?php
/**
 * Created by PhpStorm.
 * User: luc
 * Date: 07/06/2018
 * Time: 10:25
 */

namespace NextDom\Interfaces;


interface DAOInterface
{
    public function buildListDomaineObject(array $array) :array;
}