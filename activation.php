<?php

require_once 'core/init.php';

$email = trim(Input::get('email'));
$email_kod = trim(Input::get('email_kod'));
$emails = new Email();

if(Session::exists('user'))
{
    $emails->addError('Korisnik je ulogiran!');
}
if(!Input::exists('get') || !$email || !$email_kod)
{
    $emails->addError('Kod ili email adresa nisu uneseni!');
}
if(!$emails->_db->get('korisnici', array(array('email', '=', $email)))->count() && $email)
{
    $emails->addError('Nepostojeća email adresa!');
}
if(!$emails->_db->get('korisnici', array(array('email_kod', '=', $email_kod)))->count())
{
   $emails->addError('Nepostojeći email kod!'); 
}

if($emails->_db->get('korisnici', array(array('email', '=', $email), array('aktiviran', '=', 1)))->count())
{
    $emails->addError('Korisnik je već aktivirao account!');
}

if(empty($emails->errors()))
{
    $id = $emails->_db->get('korisnici', array(array('email', '=', $email), array('email_kod', '=', $email_kod)))->first()->id;
    $emails->_db->update('korisnici', $id, array('aktiviran' => 1));
    Session::flash('potvrda', 'Uspješno ste potvrdili svoj account!');
    Redirect::to('index.php');
}
else
{
    foreach ($emails->errors() as $error)
    {
        echo '<p>'.escape($error).'<br></p>';
    }
}




   
  