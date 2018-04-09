<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Models;

/**
 * Description of Link
 *
 * @author ilya
 */

use Silex\Application;

class Link extends \ADOdb_Active_Record {
    public $_table = 'links';
    
    public static function getAtomCount(Application $app, $articles) {
        $sql = 'select article_id, count(*) as count from links where article_id in ('.implode(',', $articles).') group by article_id';
        $resAr = array();
        $result = $app['connection']->execute($sql);
        while($r = $result->fetchRow()) {
            $resAr[$r['article_id']] = array(
                'article_id' => $r['article_id'], 
                'count' => $r['count']);
        }
        return $resAr;
    }
}
