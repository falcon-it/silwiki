<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bootstrap
 *
 * основные настройки прилоложения silex
 * 
 * @author ilya
 */

namespace Application;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Application\Console\ConsoleWiki;
use Application\Controllers\SiteController;
use Application\Base\Connection;
use Application\Models\Models;
use Silex\Provider\SessionServiceProvider;
use Twig\TwigFilter;

class Bootstrap {
    protected $app;
    
    public function __construct() {
        $this->app = new Application();
    }
    
    /**
     * основные сервисы для приложения
     * соединение с бд
     * и модели таблиц
     */
    private function baseConfigure() {
        $this->app->register(
                new Connection(), 
                array(
                    'connection.host' => 'localhost',
                    'connection.user' => 'root',
                    'connection.pwd' => '123456',
                    'connection.db' => 'wiki'
                )
            );
        $this->app->register(
                new Models(), 
                array(
                    'article.connection' => $this->app['connection']
                )
            );
    }
    
    /**
     * запуск импорта из википедии с консоли
     * второй параметр после имени скрипата команда
     * третий и тд передаются в качестве массива команде
     * @param type $argv
     */
    public function console($argv) {
        $this->baseConfigure();
        //регистрируем консольную команду
        $this->app->register(new ConsoleWiki());
        
        $command = false;
        $args = array();
        $recordSet = $this->app['connection']->Execute('select count(*) from articles');
        while (!$recordSet->EOF) {
            echo 'ok: '.$recordSet->fields[0];
            $recordSet->MoveNext();
        }
        
        if(count($argv) > 1) { $command = $argv[1]; }
        if(count($argv) > 2) { $args = array_slice($argv, 2); }
        
        echo "console command: $command\n";
        try {
            if(!empty($command)) { $this->app["console.$command"]($args)->run(); }
        }
        catch(Exception $e) {
            echo "Exception: {$e->getMessage()}\n";
        }
    }
    
    /**
     * запуск приложения через http
     * регистрируем твиг, фильтры для него, сессии и контроллер для обработки запросов
     */
    public function run() {
        $this->baseConfigure();
        $this->app->register(
                new TwigServiceProvider(), 
                array('twig.path' => __DIR__.'/views', 'twig.options' => array('cache' => __DIR__.'/cache','auto_reload' => true)));
        
        $this->app->extend('twig', function($twig, $app) {
            $twig->addFilter(new TwigFilter('bytes_format', function($val) { 
                $ival = intval($val);
                $s = 0;
                
                for($i = 0; $i < 3; ++$i) {
                    if($ival >= 1024) {
                       $ival /= 1204;
                       ++$s;
                    }
                    else {
                        break;
                    }
                }
                
                $ival = number_format($ival, 2);
                
                switch($s) {
                    case 0: $ival .= ' б'; break;
                    case 1: $ival .= ' Кб'; break;
                    case 2: $ival .= ' Мб'; break;
                }
                
                return $ival; 
            }));
            $twig->addFilter(new TwigFilter('time_format', function($val) { 
                $ival = intval($val);
                
                if($ival > 60) {
                   $ival = intdiv($ival, 60) . ' мин ' . ($ival % 60) . ' секунд';
                }
                else {
                    $ival = "$ival секунд";
                }
                
                return $ival; 
            }));
            return $twig;
        });
        
        $this->app->register(new SessionServiceProvider());
        $this->app->mount('/', new SiteController());
        $this->app->run();
    }
}
