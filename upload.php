<?php

require_once 'core/init.php';


$user = new User();

if($user->hasPermission('admin'))
{
    echo '<p>You need to <a href="login.php">login</a> or <a href="register.php">register</a>';
}
else
{
    require_once 'templates/upload_temp.phtml';
}

if(!$user->isLoggedIn())
{
    Redirect::to('index.php');
}

$upload = new Upload();

$limiter = $upload->items(array(
        'min' => 2,
        'max' => 2 
        ));

$allowed = $upload->allowed(array('jpg', 'pdf'));
$where = $upload->destinationFolder('uploads');

if(empty($upload->errors()))
{
    if(Input::get('upload') == true)
    {
        if($upload->exists('files', $limiter))
        {    
            if($upload->check($allowed))
            {
                $upload->upload();
            }
        } 
    }
}

if(!empty($upload->errors()))
{
    print_r($upload->errors());
}

