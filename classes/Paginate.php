<?php

class Paginate
{
    private static $_page,
                   $_perPage;

    
    public static function setPage()
    {
        self::$_page = Input::get('page') ? (int)Input::get('page') : 1;
    }
    
    public static function getPage()
    {
        return self::$_page;
    }
    
    public static function setPerPage()
    {
        self::$_perPage = Input::get('per-page') ? (int)Input::get('per-page') : 5;
    }
    
    public static function getPerPage()
    {
        return self::$_perPage;
    }
    
    public static function start()
    {
        $page = self::$_page;
        $perPage = self::$_perPage;
        return ($page>1) ? ($page*$perPage)-$perPage : 0;
    }
    
    public static function pages($total)
    {
       return ceil($total/self::getPerPage()); 
    }
}

