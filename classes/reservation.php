<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BookedAPI\Classes;
/**
 * Description of reservation
 *
 * @author ryan
 */
class reservation {
    private $referenceNumber = '';
    private $isPendingApproval = false;
    private $links = array();
    private $message = null;
    
    function __construct($_referenceNumber, $_isPendingApproval, $_links, $_message ){
        
        $this->referenceNumber = (string) $_referenceNumber;
        $this->isPendingApproval = (bool) $_isPendingApproval;
        foreach($_links as $_link){
            $link = new link($_link->getHref(),$_link->getTitle());
            $this->links[] = $link;
        }
        $this->message = $_message;
        
    }

}

class link {
    
    public $id;
    private $href;
    public $title;
    
    function _construct($_href, $_title){
        $this->href = $_href;
        $this->title = $_title;
    }
    
    function getHref(){
        return $this->href;
    }
    
    function getTitle(){
        return $this->title;
    }

}
