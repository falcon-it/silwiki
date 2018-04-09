<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SiteController
 *
 * @author user2
 */

namespace Application\Controllers;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Application\Models\Link;

class SiteController implements ControllerProviderInterface {
    
    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];
        $controllers->get('/', function (Application $app) { 
            return $this->indexAction($app);
        });
        $controllers->post('/copy/', function (Application $app) { 
            return $this->copyAction($app);
        });
        return $controllers;
    }
    
    public function indexAction(Application $app) {
        $articles = $app['article']()->find('');
        $art_ar = array();
        foreach ($articles as $item) {
            $art_ar[$item->id] = array(
                'title' => $item->title,
                'link' => $item->link,
                'size' => $item->size,
                'count' => 0
            );
        }
        $countAr = Link::getAtomCount($app, array_keys($art_ar));
        foreach($countAr as $countItem) {
            if(isset($art_ar[$countItem['article_id']])) {
                $art_ar[$countItem['article_id']]['count'] = $countItem['count'];
            }
        }
        return $app['twig']->render('index.twig', array('title' => 'Wiki Test!', 'articles' => $art_ar));
    }
    
    public function copyAction(Application $app) {
        return 'hello';
    }
}
