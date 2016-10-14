<?php

class Upload
{
    private $_errors = array(),
            $_db = null,
            $_folder = '',
            $_id,
            $_file_names = array(),
            $_file_errors = array(),
            $_file_sizes = array(),
            $_file_tmps = array(),
            $_sessionName = null,
            $_file_destinations = array();
    
    public function __construct()
    {
        $this->_sessionName = Config::get('session/session_name');
        $this->_cookieName = Config::get('remember/cookie_name');
        
        $this->_db = DB::getInstance();
        
        if(Session::exists($this->_sessionName))
        {
            $user = Session::get($this->_sessionName);
            $field = (is_numeric($user)) ? 'id' : 'email';
            $this->_id = $this->_db->get('korisnici', array(array($field, '=', $user)))->first()->id;
        }
    }


    public function exists($name, $limiter = array())
    {
        $min = 1;
        $max = 100;
        $min_exists = (array_key_exists('min',$limiter)) ? true : false;
        $max_exists = (array_key_exists('max',$limiter)) ? true : false;
        
        $this->_file_names = $this->getProperty('files', 'name');
        if($min_exists)
        {
            $min = $limiter['min'];
        }
        
        if($max_exists)
        {
            $max = $limiter['max'];  
        }
        
        if($min === $max)
        {
            $num = $min;
        }
 
        $min_bool = (!empty($this->_file_names[$min - 1])) ? true : false;
        $max_bool = (!empty($this->_file_names[$max - 1])) ? true : false;
        
        if($min_bool && (count($this->_file_names)) <= $max)
        {
            return true;
        }
        else if(empty($this->_file_names[0]))
        {
           $this->addError("Niste označili dokument/e koje želite uploadati!"); 
        }
        else 
        {
            if(isset($num))
            {
                $this->addError("Mora se uploadati {$num} dokumenata!");
            }
            else
            {
               $this->addError("Mora se uploadati od {$min} do {$max} dokumenata!"); 
            }
            
            
        }

        return false;
    }
    
    public function items($limiter = array())
    {  
        if(count($limiter) > 2)
        {
            $this->addError('items funkcija prima najviše 2 parametra');
        }
            
        foreach($limiter as $element)
        {
            if(!is_int($element))
           {
               $this->addError('items funkcija prima samo integer vrijednosti!');
           }
        }  
        
        if(empty($this->errors()))
        {
            return $limiter;
        }   
        
        return null;
    }
    
    public function getProperty($name, $property)
    {
        return $_FILES[$name][$property];
    }
    
    public function get($name)
    {
        return $_FILES[$name];
    }
    
    public function allowed($ext = array())
    {
        return $ext;
    }
    
    public function getExt($file)
    {
        $file_ext = explode('.', $file);
        $file_ext = strtolower(end($file_ext));
        return $file_ext;
    }

    public function check($allowed = array())
    {
        $this->_file_errors = $this->getProperty('files', 'error');
        $this->_file_sizes = $this->getProperty('files', 'size');
        $this->_file_tmps = $this->getProperty('files', 'tmp_name');
        
        foreach ($this->_file_names as $position => $file_name)
        {
            $file_tmp = $this->_file_tmps[$position];
            $file_error = $this->_file_errors[$position];
            $file_size = $this->_file_errors[$position];
            
            $ext = $this->getExt($file_name);
            
            if(!in_array($ext, $allowed))
            { 
                $allowed_string = implode(", ",$allowed);
                $this->addError("Dokument '{$file_name}' nije podržanog formata({$allowed_string})!");
            } 
            
            if($file_error != 0)
            {
                $this->addError("Došlo je do greške prilikom uploada!");
            }
            
            if($file_size > 2097152)
            {
                $this->addError("Svi dokumenti moraju biti manji od 2MB");
            }
        }
        
        if($this->_db->recordExists('uploads', array(array('id_kor', '=', $this->_id))))
        {
            $this->addError("Korisnik je već uploadao potrebne dokumente!");
        }

        if(empty($this->_errors))
        {
            return true;
        }
            
        return false;
    }
    
    private function addError($desc)
    {
        $this->_errors[] = $desc;
    }
    
    public function errors()
    {
        return $this->_errors;
    }
    
    public function destinationFolder($folder = 'uploads')
    {
       $this->_folder = $folder.'/'; 
    }
    
    private function getDestinations()
    {
        return $this->_file_destinations;
    }
    
    private function finalDestinations()
    {
        $folder = $this->_folder;
        foreach ($this->_file_names as $file_name)
        {
            $ext = $this->getExt($file_name);
            $file_name_new = uniqid('', true) . '.' . $ext;
            $this->_file_destinations[] = $folder . $file_name_new;          
        }
    }

    public function upload()
    {
        $x = 0;
        $this->finalDestinations();
        $paths = $this->getDestinations();
        foreach ($this->_file_names as $position => $file_name)
        {
           $file_tmp = $this->_file_tmps[$position];
           if(move_uploaded_file($file_tmp, $paths[$x]))
            {
                echo "Uploadali ste {$file_name}.<br>";
            }
            $x++;
        }
        
        $this->store();       
    }
    
    private function store()
    {  
        $x = 0;
        $paths = $this->getDestinations();
        $files = count($paths);
        for($x=0; $x < $files; $x+=$files)
        {
           if(!$this->_db->recordExists('uploads', array(array('id_kor', '=', $this->_id))))
           {
               $this->_db->insert('uploads', array(
                    'id_kor' => $this->_id,
                    'path1' => $paths[$x],
                    'path2' => $paths[$x+1]
                )); 
           }
        }
        
    }
}

