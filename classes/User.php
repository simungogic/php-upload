<?php

class User
{
    private $_db,
            $_data,
            $_sessionName,
            $_cookieName,
            $_isLoggedIn;
    
    public function __construct($user = null)
    {
        $this->_db = DB::getInstance();
        $this->_sessionName = Config::get('session/session_name');
        $this->_cookieName = Config::get('remember/cookie_name');
        
        if(!$user)
        {
            if(Session::exists($this->_sessionName))
            {
                $user = Session::get($this->_sessionName);
                if($this->find($user))
                {
                    $this->_isLoggedIn = true;
                }
            }
        }
        else
        {
            $this->find($user);
        }
    }
    
    public function create($fields = array())
    {
        if(!$this->_db->insert('korisnici', $fields))
        {
            throw new Exception('There was a problem creating an account!');
        }
    }
    
    public function find($user = null, $lozinka = null)
    {
        if($user)
        {
            $field = (is_numeric($user)) ? 'id' : 'email';
            $data = $this->_db->get('korisnici', array(array($field, '=', $user)));
            
            if($data->count())
            {
               $this->_data = $data->first();
               return true;
            }
        }
        return false;
    }
    
    public function data()
    {
        return $this->_data;
    }
    
    public function login($email = null, $lozinka = null, $zapamti = false)
    {
        if(!$email && !$lozinka && $this->exists())
        {
            Session::put($this->_sessionName, $this->data()->id);
        }
        else
        {
            $user = $this->find($email);  

            if($user)
            {
                if($this->data()->lozinka == Hash::make($lozinka, $this->data()->salt) && $this->data()->aktiviran == 1)
                {
                    Session::put($this->_sessionName, $this->_data->id);

                    if($zapamti)
                    {
                       $hash = Hash::unique();
                       $hashCheck = $this->_db->get('korisnici_session', array(array('id_kor', '=', $this->data()->id)));

                       if(!$hashCheck->count())
                       {
                           
                           $this->_db->insert('korisnici_session', array(
                               'id_kor' => $this->data()->id,
                               'hash' => $hash
                           ));
                       }
                       else
                       {
                           $hash = $hashCheck->first()->hash;
                       }
                       
                       Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
                       
                    }

                    return true;
                }
            }
        }
                    return false;
    }
    
    public function exists()
    {
        return (!empty($this->_data)) ? true : false;
    }

    public function isLoggedIn()
    {
        return $this->_isLoggedIn;
    }
    
    public function logout()
    {
        $this->_db->delete('korisnici_session', array(array('id_kor', '=', $this->data()->id)));
        Session::delete($this->_sessionName); 
        Cookie::delete($this->_cookieName);
    }
    
    public function update($fields = array(), $id = null)
    {
        if(!$id && $this->_isLoggedIn)
        {
            $id = $this->data()->id;
        }
        
        if(!$this->_db->update('korisnici', $id, $fields))
        {
            throw new Exception('There was a problem updating');
        }
    }
    
    public function hasPermission($key)
    {
        $permissions = $this->_db->get('permissions', array(array('id', '=', $this->data()->kor_prava)));
        
        if($permissions->count())
        {
            $permission = json_decode($permissions->first()->permissions, true);
            if($permission[$key] == true)
            {
                return true;
            }
            return false;
        }
    }
}

