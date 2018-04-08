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

class SiteController implements ControllerProviderInterface {
    
    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];
        $controllers->get('/', function (Application $app) { 
            return $this->indexAction($app);
        });
        return $controllers;
    }
    
    public function indexAction(Application $app) {
        $articles = $app['article']()->find('');
        //var_dump($articles->links);
        $art_ar = array();
        foreach ($articles as $item) {
            $art_ar[] = array(
                'title' => $item->title,
                'link' => $item->link,
                'size' => $item->size,
                'count' => count($item->links)
            );
            //var_dump($item->links);
            //$art_ar
        }
        return $app['twig']->render('index.twig', array('title' => 'Wiki Test!', 'articles' => $art_ar));
    }
}
