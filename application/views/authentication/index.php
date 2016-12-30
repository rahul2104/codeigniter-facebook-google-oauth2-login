<!DOCTYPE HTML>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <base href="<?php echo base_url(); ?>public/">
<title>Login with Google Account by CodexWorld</title>
<style type="text/css">
h1
{
font-family:Arial, Helvetica, sans-serif;
color:#999999;
}
.wrapper{width:600px; margin-left:auto;margin-right:auto;}
.welcome_txt{
	margin: 20px;
	background-color: #EBEBEB;
	padding: 10px;
	border: #D6D6D6 solid 1px;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	border-radius:5px;
}
.google_box{
	margin: 20px;
	background-color: #FFF0DD;
	padding: 10px;
	border: #F7CFCF solid 1px;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	border-radius:5px;
}
.google_box .image{ text-align:center;}
</style>
</head>
<body>
<?php
if(!empty($authUrl)&&!empty($login_url)) {
	echo '<a href="'.$authUrl.'"><center><img style="height:150px; width:400px"  src="image/sign-in-with-google.png" alt=""/></center></a>';
                echo '<a href="'.$login_url.'"><center><img style="height:150px; width:400px"  src="image/51911822.cms" alt=""/></center></a>';
}else{

?>
<div class="wrapper">
    <h1><?php echo $userData['oauth_provider'] ?> Profile Details </h1>
    <?php
    echo '<div class="welcome_txt">Welcome <b>'.$userData['first_name'].'</b></div>';
    echo '<div class="google_box">';
    echo '<p class="image"><img src="'.$userData['picture_url'].'" alt="" width="300" height="220"/></p>';
    echo '<p><b>'.$userData['oauth_provider'].' ID : </b>' . $userData['oauth_uid'].'</p>';
    echo '<p><b>Name : </b>' . $userData['first_name'].' '.$userData['last_name'].'</p>';
    echo '<p><b>Email : </b>' . $userData['email'].'</p>';
    echo '<p><b>Gender : </b>' . $userData['gender'].'</p>';  
    echo '<p><b>You are login with : </b>'.$userData['oauth_provider'].'</p>';
    echo '<p><b>Logout from <a href="'.base_url().'index.php/authentication/logout">'.$userData['oauth_provider'].'</a></b></p>';
    echo '</div>';
    ?>
</div>
<?php } ?>
</body>
</html>