<?php

require_once 'core/init.php';

$user = new User();

if(!$user->isLoggedIn())
{
    Redirect::to('index.php');
}

/*if(!$user->hasPermission('admin'))
{
    Redirect::to('index.php'); 
}*/

/*for($i=0;$i<60;$i++)
{
    DB::getInstance()->insert('users', array(
        'ime' => uniqid(),
        ));
}*/
Paginate::setPage();
Paginate::setPerPage();
$start = Paginate::start();
$page = Paginate::getPage();
$perPage = Paginate::getPerPage();
$total = DB::getInstance()->total('users')->count();
$pages = Paginate::pages($total);
$results = DB::getInstance()->limit('uploads', array(), array($start, $perPage))->results();
$query1 = DB::getInstance()->merge('uploads', 'korisnici')->results();

?>

<html>
    <head>
        
    </head>
    <body><?php foreach($query1 as $query): ?>
        <div>
            <p><?php echo $query->id ?>|<?php echo $query->ime_prez ?>|<?php echo $query->email ?>|<?php echo $query->path1 ?>|<?php echo $query->path2 ?></p>
        </div>
        <?php endforeach; ?>
        
        <div>
            <?php for($x=1;$x<=$pages;$x++): ?>
            <a href='?page=<?php echo $x; ?>&per-page=<?php echo $perPage; ?>'><?php echo $x; ?></a>
            <?php endfor; ?>
        </div>
    </body>
</html>