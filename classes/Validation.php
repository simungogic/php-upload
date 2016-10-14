<?php

class Validation
{
    private $_passed = false,
            $_errors = array(),
            $_db = null;
    
    public function __construct()
    {
        $this->_db = DB::getInstance();
    }
    
    public function check($source, $items = array())
    {
        foreach($items as $item => $rules)
        {
            $item = escape($item);
            foreach($rules as $rule => $rule_value)
            {
               $value = trim($source[$item]);
               if($rule === 'name')
               {
                   $name = $rule_value;
               }
               if($rule === 'required' && empty($value))
               {
                   $name = lcfirst($name);
                   $this->addError("Polje '{$name}' je obavezno");
               }
               else if(!empty($value))
               {
                   switch ($rule)
                   {
                       case 'mail_filter':
                           if(!filter_var($value, FILTER_VALIDATE_EMAIL))
                           {
                               $this->addError("{$name} nije u valjanom formatu");
                           }
                           break;
                       case 'min':
                           if(strlen($value) < $rule_value)
                           {
                               $this->addError("{$name} mora imati najmanje {$rule_value} znakova");
                           }
                           break;
                       case 'max':
                           if(strlen($value) > $rule_value)
                           {
                               $this->addError("{$name} smije imati najviše {$rule_value} znakova"); 
                           }
                           break;
                       case 'slova_brojke':
                           if (!preg_match('/[A-Z]/', $value) || !preg_match('/[0-9]/', $value) || !preg_match('/[a-z]/', $value))
                            {
                                $this->addError("{$name} mora sadrzavati barem jedan broj i barem jedno veliko i malo slovo");
                            }
                            break;
                       case 'matches':
                           if($value != $source[$rule_value])
                           {
                               $this->addError("'{$name}' mora biti ista kao {$rule_value}"); 
                           }
                           break;
                       case 'unique':
                           try{
                                $check = $this->_db->get($rule_value,array(array($item, '=', $value)));
                           } catch (Exception $ex) {
                                echo $ex->getMessage();
                           }
                           
                           if($check->count())
                           {
                               $this->addError("{$name} postoji u bazi podataka");
                           }
                           break;
                   }
               }
               
            }
        }
        
        if(empty($this->_errors))
        {
            $this->_passed = true;
        }
        
        return $this;
    }
    
    private function addError($error)
    {
        $this->_errors[] = $error;
    }
           
    public function errors()
    {
        return $this->_errors;
    }
    
    public function passed()
    {
        return $this->_passed;
    }
}

