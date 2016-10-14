<?php

class DB
{
    private static $_instance = null;
    private $_pdo, 
            $_query, 
            $_results, 
            $_count = 0, 
            $_error = false;
    
    public function __construct()
    {
        try
        {
            $this->_pdo = new PDO('mysql:host='.Config::get('mysql/host').';dbname='.Config::get('mysql/db'),Config::get('mysql/username'),Config::get('mysql/password'));
        } catch (PDOException $ex) {
            die($ex->getMessage());
        }
    }
    
    public static function getInstance()
    {
        if(!isset(self::$_instance))
        {
            self::$_instance = new DB();
        }
        return self::$_instance;
    }
    
    public function total($table)
    {
        $sql = "SELECT FOUND_ROWS() FROM {$table} AS total";
        if (!$this->query($sql)->error()) 
        {
            return $this;
        } 
      
      return false;
    }
    
    public function query($sql, $params = array())
    {
        $this->_error = false;
        if($this->_query = $this->_pdo->prepare($sql))
        {
            $x = 1;
            if(count($params))
            {
                foreach ($params as $param)
                {
                    $this->_query->bindValue($x,$param);
                    $x++;
                }
            }
            
            if($this->_query->execute())
            {
                $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
                $this->_count = $this->_query->rowCount();
            }
            else
            {
                $this->_error = true;
            }
        }
        return $this;
    }
    
    public function recordExists($table, $where = array())
    {
        $exists = $this->get($table, $where)->count();
        
        if($exists)
        {
            return true;  
        }

        return false;
    }
    
    public function error()
    {
        return $this->_error;
    }
    
    
    public function action($action,$table,$where = array(), $limit=array())
    {
        $sql = "{$action} FROM {$table}";
        $err = array();
        $field = array();
        $operator = array();
        $value = array();
        $operators = array('>', '<', '=', '<=', '>=');

        if(count($where))
        {
            try
            {
                foreach ($where as $element) 
                {
                    $field[] = $element[0];
                    $operator[] = $element[1];
                    $value[] = $element[2];
                    foreach($element as $bit)
                    {
                        if (count($element) != 3) 
                        {
                            throw new Exception('query ne valja');
                        }
                    }

                }
            }

            catch(Exception $e)     
            {          
                $e->getMessage();
            }

            $x = 1;
            $sql .= " WHERE ";
            for ($i = 0; $i < count($field); $i++) {
                if (in_array($operator[$i], $operators)) {
                    $sql .= "{$field[$i]} {$operator[$i]} ?";
                    if ($x < count($field)) {
                        $sql .= " AND ";
                        $x++;
                    }
                }
            }
        }

        if(count($limit) == 2)
        {
            foreach($limit as $num)
            {
                if(!is_integer($num))
                {
                    $err[] = 0;
                }
            }
            
            if(empty($err))
            {       
                $start = $limit[0];
                $perPage = $limit[1]; 
            }
            
            $sql .= " LIMIT {$start}, {$perPage}";
        }

        if (!$this->query($sql, $value)->error()) 
        {
           return $this;
        }  
          
        return false;
    }
    
    public function get($table,$where,$limit=null)
    {
        return $this->action('SELECT *',$table,$where,$limit);
    }
    
    public function limit($table,$where,$limit)
    {
        return $this->get($table, $where, $limit);
    }
    
    public function merge($table1,$table2)
    {
        $sql = "SELECT * from {$table1} INNER JOIN {$table2} ON {$table1}.id_kor={$table2}.id";
        return $this->query($sql);
    }
    
    public function delete($table,$where)
    {
        return $this->action('DELETE',$table,$where);
    }
    
    public function count()
    {
        return $this->_count;
    }
    
    public function results()
    {
        return $this->_results;
    }
    
    public function first()
    {
        return $this->results()[0];
    }
    
    public function insert($table,$fields = array())
    {
        $keys = array_keys($fields);
        $values = '';
        $x = 1;

        foreach($fields as $field)
        {
            $values .= '?';
            if($x < count($fields))
            {
                $values .= ', ';
                $x++;
            }
        }

        $sql = "INSERT INTO {$table} (".implode(", ", $keys).") VALUES ({$values})";
        if(!$this->query($sql,$fields)->error())
        {
            return true;
        }

        return false;
    }
    
    public function update($table, $id, $fields)
    {
        $set = '';
        $x = 1;
        
        foreach($fields as $name => $value)
        {
            $set .= "{$name} = ?";
            if($x < count($fields))
            {
                $set .= ", ";
                $x++;
            }
        }

        $sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";
        
        if(!$this->query($sql, $fields)->error())
        {
            return true;
        }
       return false; 
        
    }
}


