<?php

class Email
{
    private $_m = null,
            $_errors = array();
    public  $_db = null;
            
    public function __construct()
    {
        $this->_db = DB::getInstance();
        
        $this->_m = new PHPMailer;
        $this->_m->isSMTP();
        $this->_m->SMTPAuth = true;
        
        $this->_m->Host = 'smtp.gmail.com';
        $this->_m->Username = 'simun.gogic@gmail.com';
        $this->_m->Password = 'osijek93rtf';
        $this->_m->SMTPSecure = 'ssl';
        $this->_m->Port = 465;
        $this->_m->isHTML(true);
        $this->_m->addReplyTo('reply@booky.online', 'Reply address'); 
        $this->_m->Subject = 'Confirmation mail-Booky.online';
        $this->_m->From = 'simun.gogic@gmail.com';
        $this->_m->FromName = 'booky.online';
    }

    public function send($to, $name, $email_kod)
    {
        $this->_m->addAddress($to, $name);
        $this->_m->Body = "<a href=http://localhost/booky.online/activation.php?email={$to}&email_kod={$email_kod}>Aktivacija accounta</a>";
        $this->_m->send();  
    }
    
    public function addError($error)
    {
        $this->_errors[] = $error;
    }
    
    public function errors()
    {
        return $this->_errors;
    }
}

