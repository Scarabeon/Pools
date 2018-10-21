<?PHP
require_once 'api.php';
?>
<!DOCTYPE html>
<HTML lang="en">
<HEAD>
<TITLE>Kaluže</TITLE>
<META charset="utf-8"/>
<META http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
<META name="description" content=""/>
<META name="keywords" content=""/>
<META name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>
<META name="robots" content="index, follow"/>
<LINK rel="shortcut icon" href="favicon.ico"/>
<LINK rel="stylesheet" href="css/style.css"/>
<LINK rel="stylesheet" href="css/normalize.css"/>
</HEAD>
<BODY>
<HEADER>
<DIV class="wrapper">
<DIV id="banner">
<DIV id="logo"><H1>Pools</H1></DIV>
</DIV>
</DIV>
</HEADER>
<SECTION id="home">   
<DIV class="wrapper">
<DIV class="row">
<P>This application generates on board 20&nbsp;x&nbsp;20 random fields where water is spilt. Then counts the number of pools&nbsp;-&nbsp;fields with spilt water that touch each other, prints their number and determines which pool is the largest one (shown highlighted).</P>
<P>In case of more pools with the same maximal size that one with the lowest number is highlighted. Isolated fields are removed from board.</P>
<?PHP
echo (new Code(20, 20))->draw();
?>
</DIV>
</DIV>
</SECTION>
</BODY>
</HTML>