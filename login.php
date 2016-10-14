<?php
require_once 'core/init.php';

if(Input::exists())
{
    if(Token::check(Input::get('token')))
    {
        $validate = new Validation();
        $validation = $validate->check($_POST,array(
            'email' => array(
                'name' => 'Email',
                'required' => true
            ),
            'lozinka' => array(
                'name' => 'Lozinka',
                'required' => true
            )
        ));
        
        if($validation->passed())
        {
            $user = new User();
            $zapamti = (Input::get('zapamti') === 'on') ? true : false;
            $login = $user->login(Input::get('email'), Input::get('lozinka'), $zapamti);
            
            if($login)
            {
                Session::flash('loggedIn', 'Uspješno ste prijavljeni!');
                Redirect::to('index.php');
            }
            else
            {
                echo '<p>Neuspješna prijava!</p>';
            }
        }
        else
        {
            foreach($validation->errors() as $error)
            {
                echo $error.'<br>';
            }
              
        }
    }
}
?>

<form action="" method="post">
    <div class="field">
        <label for="email">Email:</label>
        <input type="text" name="email" id="email" autocomplete="off">
    </div>
    
    <div class="field">
        <label for="lozinka">Lozinka</label>
        <input type="password" name="lozinka" id="lozinka" autocomplete="off">
    </div>
    
    <div class="field">
        <label for="remember">
            <input type="checkbox" name="zapamti" id="remember"> Zapamti me
        </label>
    </div>
    
    <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
    <input type="submit" value="Ulogiraj">
</form>

