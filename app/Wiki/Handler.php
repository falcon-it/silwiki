<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Handler
 *
 * @author ilya
 */


namespace Application\Wiki;

use Application\Wiki\State;
use Silex\Application;

class Handler {
    const ATOM_SAVE_STEP = 20;
    public static function Execute(State $s, Application $app) {
        $time = time();
        
        switch ($s->state) {
            case State::INIT:
                Handler::queryWiki($s);
                break;
            case State::WIKI_OK:
                Handler::queryWikiPage($s);
                break;
            case State::WIKI_PAGE_OK:
                Handler::pageClear($s);
                break;
            case State::ART_CLEAR:
                Handler::saveArticle($s, $app);
                break;
            case State::ART_SAVE:
                Handler::atomsSort($s);
                break;
            case State::ATOMS_SAVE:
                Handler::atomsSave($s, $app);
                break;
            default:
                $s->state = State::ERROR;
                $s->message = 'Error fail state';
        }
        
        $s->time += time() - $time;
    }
    
    private static function queryWiki(State $s) {
        $query_str = 'https://ru.wikipedia.org/w/api.php?action=query&titles='.urlencode($s->query).'&prop=info&format=json&rvprop=content&redirects=true';
        
        $curl = curl_init();
        
        if($curl === false) {
            $s->state = State::ERROR;
            $s->message = 'Error curl init';
            return;
        }
        
        curl_setopt($curl, CURLOPT_URL, $query_str);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT , 5); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, '1'); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($curl);
        curl_close($curl);
        
        if(empty($output)) {
            $s->state = State::ERROR;
            $s->message = "Wiki query {$s->query} fail";
            return;
        }
        
        $json_out = json_decode($output, true);
        if($json_out === null) {
            $s->state = State::ERROR;
            $s->message = "Wiki query {$s->query} json decode fail";
        }
        
        if(isset($json_out['query']['pages'][-1])) {
            $s->state = State::WIKI_NOT_FOUND;
            $s->message = "Wiki query {$s->query} page not found";
            return;
        }
        
        $s->data = array_shift($json_out['query']['pages']);
        $s->state = State::WIKI_OK;
    }
    
    private static function queryWikiPage(State $s) {
        $article_link = 'https://ru.wikipedia.org/wiki/'.urlencode($s->data['title']);
        
        $curl = curl_init();
        
        if($curl === false) {
            $s->state = State::ERROR;
            $s->message = 'Error curl init';
            return;
        }
        
        curl_setopt($curl, CURLOPT_URL, $article_link);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT , 5); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, '1'); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($curl);
        curl_close($curl);
        
        if(empty($output)) {
            $s->state = State::ERROR;
            $s->message = "Wiki query {$s->query} fail";
            return;
        }
        
        $s->state = State::WIKI_PAGE_OK;
        $s->data = array(
            'text' => $output, 
            'link' => $article_link, 
            'title' => $s->data['title']);
    }
    
    private static function pageClear(State $s) {
        $output2 = $s->data['text'];
        //ищем на странице тэг с телом статьи
        $startstr = '<div id="bodyContent" class="mw-body-content">';
        $startstrlen = mb_strlen($startstr);
        $spos0 = mb_strpos($output2, $startstr);
        
        $spos = $spos0 + $startstrlen;
        $epos = $spos;
        $_cycle = false;
        do {
            $spos = mb_strpos($output2, '<div', $spos);
            $epos = mb_strpos($output2, '</div>', $epos);
            $_cycle = (($spos !== false) && ($epos !== false) && ($epos > $spos));
            if($_cycle) {
                $spos += 3;
                $epos += 3;
            }
        } while($_cycle);
        
        $text = mb_substr($output2, $spos0, $epos - $spos0 + mb_strlen('</div>'));
        
        //удалим css и script
        foreach (['css', 'script'] as $ti) {
            $startTag = '<'.$ti;
            $endTag = '</'.$ti.'>';
            while(true) {
                $spos = mb_strpos($text, $startTag);
                if($spos === false) { break; }
                $epos = mb_strpos($text, $endTag, $spos);
                if($epos === false) { break; }
                $text = mb_substr($text, 0, $spos).mb_substr($text, $epos + mb_strlen($endTag));
            }
        }
        
        
        //удаляем лишнии пробелы и переводы строк
        $text = preg_replace('/[\n]+/', "\n", 
                    preg_replace('/[\r\n]+/', "\r\n", 
                    preg_replace('/[\r]+/', "\r", 
                        strip_tags( $text))));
        
        $s->state = State::ART_CLEAR;
        $s->data['text'] = $text;
        //var_dump($s);
    }
    
    private static function saveArticle(State $s, Application $app) {
        $article = $app['article']();
        if(count($article->find('title=?', array($s->data['title']))) > 0) {
            $s->state = State::ART_SAVE_FAIL;
            $s->message = "Article {$s->query} exists";
            return;
        }
        
        $article = $app['article']();
        
        $article->title = $s->data['title'];
        $article->date = date('Y-m-d H:i:s');
        $article->link = $s->data['link'];
        $article->article = $s->data['text'];
        $s->data['article_size'] = strlen($s->data['text']);
        $article->size = $s->data['article_size'];
        $article->save();
        //var_dump($article->save());
        
        $s->data['article_id'] = $article->id;
        //var_dump($article->id);

        $s->state = State::ART_SAVE;
        //$s->state = State::ART_SAVE_FAIL;
    }
    
    private static function atomsSort(State $s) {
        $text = mb_strtolower($s->data['text']);
        $text = preg_replace('/[^(a-z)|(A-Z)|(а-я)|(А-Я)|(0-9)|(\r)|(\n)|(\s)|(\t)]+/', '', $text);
        $atosm = preg_split ('/[(\r)|(\n)|(\s)|(\t)]+/', $text);
        $count_ar = array_count_values ($atosm);
        
        $s->data['atoms'] = $count_ar;
        $s->data['atoms_count'] = count($count_ar);
        
        $s->state = State::ATOMS_SAVE;
    }
    
    private static function atomsSave(State $s, Application $app) {
        $step = Handler::ATOM_SAVE_STEP;
        $atom_count_all = count($s->data['atoms']);
        $atom_copy = ($atom_count_all > $step) ? $step : $atom_count_all;
        
        $am = array_slice($s->data['atoms'], 0, $atom_copy);
        $s->data['atoms'] = array_slice($s->data['atoms'], $atom_copy);
        
        foreach($am as $at => $count) {
            $atom = $app['atom']();
            $atom_id = false;
            $save_atoms = $atom->find('atom=?', array($at));
            if(count($save_atoms) > 0) {
                $atom_id = $save_atoms[0]->id;
            }
            else {
                $new_atom = $app['atom']();
                $new_atom->atom = $at;
                $new_atom->save();
                $atom_id = $new_atom->id;
            }
            
            if($atom_id !== false) {
                $link = $app['link']();
                $link->atom_id = $atom_id;
                $link->article_id = $s->data['article_id'];
                $link->counter = $count;
                $link->save();
            }
        }
        
        $s->state = (count($s->data['atoms']) == 0) ? State::OK : State::ATOMS_SAVE;
    }
}
