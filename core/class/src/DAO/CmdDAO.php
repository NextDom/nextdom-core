<?php
/**
 * Created by PhpStorm.
 * User: luc
 * Date: 07/06/2018
 * Time: 11:24
 */

namespace NextDom\src\DAO;


use NextDom\Interfaces\DAOInterface;
use NextDom\src\Domaine\Cmd;

class CmdDAO extends DAO implements DAOInterface

{


    /**
     * @param array $row
     * Builds a domain object from a DB row.
     * Must be overridden by child classes.
     */
    protected function buildDomainObject(array $row): Cmd
    {
        $cmd = (new Cmd())
            ->setId($row['id'])
            ->setEqLogicId($row['eqLogic_Id'])
            ->setType($row['eqType'])
            ->setLogicalId($row['logicalId'])
            ->setGenericType($row['generic_type'])
            ->setOrder($row['order'])
            ->setName($row['name'])
            ->setConfiguration($row['configuration'])
            ->setTemplate($row['template'])
            ->setIsHistorized($row['isHistorized'])
            ->setType($row['type'])
            ->setSubType($row['subType'])
            ->setUnite($row['unite'])
            ->setDisplay($row['display'])
            ->setIsVisible($row['isVisible'])
            ->setValue($row['value'])
            ->setHtml($row['html'])
            ->setAlert($row['alert']);
        return $cmd;
    }

    /**
     * @param array $array
     * @return array
     */
    public function buildListDomainObject(array $array): array
    {
        $list = [];
        foreach ($array as $row) {
            $list[] = $this->buildDomainObject($row);
        }
        return $list;
    }

}