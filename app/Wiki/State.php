<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of State
 * 
 * будем в этом объекта хранить состояние обработки запроса к википедии
 *
 * @author ilya
 */

namespace Application\Wiki;

class State {
    const INIT = 0;
    const WIKI_OK = 1;
    const WIKI_NOT_FOUND = 2;
    const WIKI_PAGE_OK = 10;
    const ART_CLEAR = 3;
    const ART_SAVE = 4;
    const ART_SAVE_FAIL = 5;
    const ATOMS_SORT = 6;
    const ATOMS_SAVE = 7;
    const OK = 8;
    const ERROR = 9;
    
    public $state;
    public $query;
    public $data;
    public $message;
    public $time;

    public function __construct($query) {
        $this->state = State::INIT;
        $this->query = $query;
        $this->time = 0;
    }
    
    public function getAtomsSaveProcess() {
        if(isset($this->data['atoms_count']) && 
                isset($this->data['atoms'])) {
            return 100 - ((count($this->data['atoms']) / $this->data['atoms_count']) * 100);
        }
        
        return 0;
    }
}
