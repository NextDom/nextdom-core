<?php

namespace NextDom\Model\Entity\Parents;

use NextDom\Helpers\DBHelper;

trait RefreshEntity
{
    /**
     * Rafraichit les informations à partir de la base de données
     *
     * @throws \Exception
     */
    public function refresh()
    {
        DBHelper::refresh($this);
    }
}