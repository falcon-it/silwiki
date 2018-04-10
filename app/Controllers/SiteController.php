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
use Symfony\Component\HttpFoundation\Request;
use Application\Wiki\State;
use Application\Wiki\Handler;

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
        $req = Request::createFromGlobals();
        $ansver = array('process' => 0, 'result' => 'ok', 'exit' => false, 'message' => false);
        $wikiStare = false;        
        
        if($app['session']->has('wiki')) {
            $wikiStare = unserialize($app['session']->get('wiki'));
        }
        else {
            $wikiStare = new State($req->get('query'));
        }
        
        Handler::Execute($wikiStare, $app);
        
        switch ($wikiStare->state) {
            case State::WIKI_OK:
                $ansver['process'] = 5;
                break;
            case State::WIKI_PAGE_OK:
                $ansver['process'] = 12;
                break;
            case State::ART_CLEAR:
                $ansver['process'] = 18;
                break;
            case State::ART_SAVE:
                $ansver['process'] = 25;
                break;
            case State::ATOMS_SAVE:
                $ansver['process'] = number_format(25 + ($wikiStare->getAtomsSaveProcess() * 75 / 100), 2);
                break;
            case State::OK:
                $ansver['process'] = 100;
                $ansver['exit'] = true;
                $ansver['message'] = $app['twig']->render('result.twig', 
                        array(
                            'link' => $wikiStare->data['link'], 
                            'time' => $wikiStare->time, 
                            'size' => $wikiStare->data['article_size'], 
                            'count' => $wikiStare->data['atoms_count']
                        ));
                $ansver['table'] = $app['twig']->render('table.twig', 
                        array(
                            'article' => array(
                                'title'=> $wikiStare->data['title'], 
                                'link' => $wikiStare->data['link'], 
                                'size' => $wikiStare->data['article_size'], 
                                'count' => $wikiStare->data['atoms_count'])
                            ));
                break;
            case State::WIKI_NOT_FOUND:
                $ansver['process'] = 100;
                $ansver['result'] = 'not_found';
                $ansver['message'] = $app['twig']->render('not_found.twig', 
                        array('query' => $wikiStare->query));
                break;
            case State::ART_SAVE_FAIL:
                $ansver['process'] = 100;
                $ansver['result'] = 'article_exsit';
                $ansver['message'] = $app['twig']->render('article_exsit.twig', 
                        array('query' => $wikiStare->query));
                break;
            case State::ERROR:
                $ansver['process'] = 100;
                $ansver['result'] = 'fail';
                break;
        }
        
        if(($wikiStare->state == State::OK) || 
                ($wikiStare->state == State::ERROR) ||
                ($wikiStare->state == State::ART_SAVE_FAIL) ||
                ($wikiStare->state == State::WIKI_NOT_FOUND)) {
            if($app['session']->has('wiki')) {
                $app['session']->remove('wiki');
            }
        }
        else {
            $app['session']->set('wiki', serialize($wikiStare));
        }
        
        return $app->json($ansver);
    }
}
