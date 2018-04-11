<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SiteController
 *
 * @author ilya
 */

namespace Application\Controllers;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Application\Models\Link;
use Symfony\Component\HttpFoundation\Request;
use Application\Wiki\State;
use Application\Wiki\Handler;

class SiteController implements ControllerProviderInterface {
    
    //регистрируем обработчики запросов
    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];
        $controllers->get('/', function (Application $app) {  return $this->indexAction($app); });
        $controllers->post('/copy/', function (Application $app) {  return $this->copyAction($app); });
        $controllers->post('/search/', function (Application $app) {  return $this->searchAction($app); });
        $controllers->get('/article/{id}/', function (Application $app, $id) {  return $this->articleAction($app, $id); });
        return $controllers;
    }
    
    //индексная страница
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
    
    /**
     * копируем данные из википедии
     * разбиваем процесс на кучу шагов
     * и с помощью js будем постоянно слать запросы
     * пока не получим 100% или ошибку
     * @param Application $app
     * @return type
     */
    public function copyAction(Application $app) {
        $req = Request::createFromGlobals();
        $ansver = array('process' => 0, 'result' => 'ok', 'exit' => false, 'message' => false);
        $wikiState = false;        
        
        if($app['session']->has('wiki')) {
            $wikiState = unserialize($app['session']->get('wiki'));
        }
        else {
            $wikiState = new State($req->get('query'));
        }
        
        Handler::Execute($wikiState, $app);
        
        switch ($wikiState->state) {
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
                $ansver['process'] = number_format(25 + ($wikiState->getAtomsSaveProcess() * 75 / 100), 2);
                break;
            case State::OK:
                $ansver['process'] = 100;
                $ansver['exit'] = true;
                $ansver['message'] = $app['twig']->render('result.twig', 
                        array(
                            'link' => $wikiState->data['link'], 
                            'time' => $wikiState->time, 
                            'size' => $wikiState->data['article_size'], 
                            'count' => $wikiState->data['atoms_count']
                        ));
                $ansver['table'] = $app['twig']->render('table.twig', 
                        array(
                            'article' => array(
                                'title'=> $wikiState->data['title'], 
                                'link' => $wikiState->data['link'], 
                                'size' => $wikiState->data['article_size'], 
                                'count' => $wikiState->data['atoms_count'])
                            ));
                break;
            case State::WIKI_NOT_FOUND:
                $ansver['process'] = 100;
                $ansver['result'] = 'not_found';
                $ansver['message'] = $app['twig']->render('not_found.twig', 
                        array('query' => $wikiState->query));
                break;
            case State::ART_SAVE_FAIL:
                $ansver['process'] = 100;
                $ansver['result'] = 'article_exsit';
                $ansver['message'] = $app['twig']->render('article_exsit.twig', 
                        array('query' => $wikiState->query));
                break;
            case State::ERROR:
                $ansver['process'] = 100;
                $ansver['result'] = 'fail';
                break;
        }
        
        if(($wikiState->state == State::OK) || 
                ($wikiState->state == State::ERROR) ||
                ($wikiState->state == State::ART_SAVE_FAIL) ||
                ($wikiState->state == State::WIKI_NOT_FOUND)) {
            if($app['session']->has('wiki')) {
                $app['session']->remove('wiki');
            }
        }
        else {
            $app['session']->set('wiki', serialize($wikiState));
        }
        
        return $app->json($ansver);
    }
    
    //запрос поиска атомов
    public function searchAction(Application $app) {
        $req = Request::createFromGlobals();
        $search = $req->get('search');
        $match = 0;
        $articles = array();
        
        $atoms = $app['atom']()->find('atom=?', array(mb_strtolower($search)));
        if(count($atoms) > 0) {
            $links = $atoms[0]->links;
            foreach ($links as $link) {
                $match += $link->counter;
                $articles[$link->article_id] = array(
                    'id' => $link->article_id,
                    'match' => $link->counter);
            }
            
            $articles_t = $app['article']()->find(implode(' OR ', array_fill(0, count($articles), 'id=?')), array_keys($articles));
            foreach ($articles_t as $articles_t_i) {
                $articles[$articles_t_i->id]['title'] = $articles_t_i->title;
            }
        }
        
        return $app->json(array('result' => $app['twig']->render('find_result.twig', array('match' => $match, 'articles' => $articles))));
    }
    
    //запрос текста статьи
    public function articleAction(Application $app, $id) {
        $articles = $app['article']()->find('id=?', array($id));
        $result = 'Ничего не найдено';
        if(count($articles) > 0) {
            $result = nl2br($articles[0]->article);
        }
        return $app->json(array('article' => $result));
    }
}
