<?php 
namespace Src\Core;

class Url{
    static private $instance;
    public $base = null;
    public $pag = null;
    
    public function __construct(){
        
        if(isset($_SERVER['HTTPS']))
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        else
            $protocol = 'http';

        $this->base = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/" . PATH;
        
    }
	
	static function getInstance() {
		if(!(Url::$instance instanceof Url)) {
			Url::$instance = new Url();
		}		
		return Url::$instance;
	}
    
    public function getURL( $id = null ){
        if(empty($this->pag))
            $this->getURLList();
        
        if( isset( $this->pag[ $id ] ) )
            return $this->pag[ $id ];
        
        return null;
    }
	
    public function getBase(){
        return $this->base;
    }

    private function getFullUrl(){	
        if(isset($_SERVER['HTTPS']))
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        else
            $protocol = 'http';

        return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public function getURLList(){
       $request = str_replace("/index.php","",$this->getFullUrl());

       $request = str_replace($this->base,"",$request);

       $retorno = explode("/", $request);

       $this->pag = $retorno;
    }

}