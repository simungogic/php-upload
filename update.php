<?php

require_once 'core/init.php';

$user = new User();

if(!$user->isLoggedIn())
{
    Redirect::to('index.php');
}

if(Input::exists())
{
   
    if(Token::check(Input::get('token')))
    {
        $validate = new Validation();
        $validation = $validate->check($_POST, array(
            'name' => array(
                'required' => true,
                'min' => 2,
                'max' => 50
            )
        ));
    }
    
    if($validation->passed())
    {
        try
        {
           $user->update(array(
               'name' => Input::get('name')
           )); 

           Session::flash('home','Your profile has been updated');
           Redirect::to('index.php');
        } catch (Exception $ex) {
            die($ex->getMessage());
        }
    }
    else
    {
        foreach($validate->errors() as $error)
        {
            echo $error.'<br>';
        }
    }
}

?>

<form method="post" action="">
    <div class="field">
        <label for="name">Name</label>
        <input name="name" type="text" value="<?php echo escape($user->data()->username); ?>">
        <input type="submit" value="Update">
        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
    </div>
</form>

