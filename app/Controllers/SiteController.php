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
        return $app['twig']->render('index.twig', array( 'title' => 'Wiki Test!'));
    }
}
