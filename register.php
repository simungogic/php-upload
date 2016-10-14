<?php
require_once 'core/init.php';


if(Input::exists())
{  
    $validate = new Validation();
    $validation = $validate->check($_POST, array(
        'email' => array(
            'name' => 'Email',
            'required' => true,
            'mail_filter' => true,
            'unique' => 'korisnici'
        ),
        'lozinka' => array(
            'name' => 'Lozinka',
            'required' => true,
            'slova_brojke' => true,
            'min' => 7,
            'max' => 10
        ),
        'lozinka_pon' => array(
            'name' => 'Ponovi lozinku',
            'required' => true, 
            'matches' => 'lozinka'
        ),
        'ime' => array(
            'name' => 'Ime',
            'required' => true,
            'min' => 2,
            'max' => 50,
        ),
        'prezime' => array(
            'name' => 'Prezime',
            'required' => true,
            'min' => 2,
            'max' => 50,
        )
    ));
    
    if($validation->passed())
    {
        $salt = Hash::salt(32);
        $email_kod = Hash::make(Input::get('email'), Hash::salt(32));
        $user = new User();
        try
        {
            $user->create(array(
                'email' => Input::get('email'),
                'lozinka' => Hash::make(Input::get('lozinka'),$salt),
                'salt' => $salt,
                'ime_prez' => Input::get('ime'). ' '.Input::get('prezime') ,
                'vri_reg' => date('Y-m-d H:i:s'),
                'kor_prava' => 1,
                'email_kod' => $email_kod,
                'aktiviran' => 0
            ));
            
            try
            {
                $mail = new Email();
                $mail->send(Input::get('email'), Input::get('ime').' '.Input::get('prezime'), $email_kod);
            } catch (phpmailerException $ex) 
            {
                echo $ex->getMessage(); 
            }
            
            Session::flash('home', 'Morate aktivirati račun klikom na link poslan na vaš e-mail!');
        } catch (Exception $ex) {
            echo $ex->getMessage();
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
?>
<form action="" method="post">
    <div class="field">
        <label for="email">Email:</label>
        <input type="text" name="email" id="email" value="<?php echo escape(Input::get('email')); ?>" autocomplete="off">
    </div>
    
    <div class="field">
        <label for="lozinka">Lozinka:</label>
        <input type="password" name="lozinka" id="lozinka" value="" autocomplete="off">
    </div>
    
    <div class="field">
        <label for="lozinka_pon">Ponovi lozinku:</label>
        <input type="password" name="lozinka_pon" id="lozinka_pon" value="" autocomplete="off">
    </div>
    
    <div class="field">
        <label for="ime">Ime:</label>
        <input type="text" name="ime" id="ime" value="<?php echo escape(Input::get('ime')); ?>" autocomplete="off">
    </div>
    
    <div class="field">
        <label for="prezime">Prezime:</label>
        <input type="text" name="prezime" id="prezime" value="<?php echo escape(Input::get('prezime')); ?>" autocomplete="off">
    </div>
    
    <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
    <input type="submit" value="Registriraj">
</form>
   
