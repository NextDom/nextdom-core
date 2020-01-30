<?php


use NextDom\Helpers\DBHelper;
use NextDom\Managers\HistoryManager;
use NextDom\Model\Entity\History;

require ('core/php/core.inc.php');

DBHelper::exec('TRUNCATE history');
DBHelper::exec('TRUNCATE historyArch');

$start = date('Y-m-d H:i:s');
for ($i = 0; $i < 50; ++$i) {
    $historyDate = date('Y-m-d H:i:s', strtotime('-' . ($i * 30) . ' minutes', strtotime($start)));
    $h = new History();
    $h->setDatetime($historyDate);
    $h->setCmd_id(1);
    $h->setValue(random_int(0, 1));
    $h->save();
}
for ($i = 0; $i < 50; ++$i) {
    $historyDate = date('Y-m-d H:i:s', strtotime('-' . ($i * 30) . ' minutes', strtotime($start)));
    $h = new History();
    $h->setDatetime($historyDate);
    $h->setCmd_id(4);
    $h->setValue(mt_rand(20, 30));
    $h->save();
}
$historyData = DBHelper::getAll('SELECT * FROM history');
HistoryManager::archive();
$historyData = DBHelper::getAll('SELECT * FROM history');
$historyArchData = DBHelper::getAll('SELECT * FROM historyArch');