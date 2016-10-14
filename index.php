<?php

require_once 'core/init.php';

if(Session::exists('home'))
{
  echo '<p>' .Session::flash('home'). '</p>';  
}

if(Session::exists('potvrda'))
{
  echo '<p>' .Session::flash('potvrda'). '</p>'; 
}

$user = new User();

/*if($user->hasPermission('moderator'))
{
    echo "You are moderator!!";
}
else
{
    echo '<p>You need to <a href="login.php">login</a> or <a href="register.php">register</a>';
}*/

if($user->isLoggedIn())
{
  require_once 'templates/index_temp.phtml';
}
 




