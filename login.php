<?php
	session_start();
	session_unset();
       session_destroy();
       session_write_close();
?>
<html>
<body>
<div style="display: block;margin-left: auto;margin-right: auto;width: 100%;">
  <?php  if($_GET['error'] != ""){ print "<font color='red'>".$_GET['error']."</font><br/>";}?>
  <br/>
  <br/>
  <form name='frmLogin' action='login_process.php' method="POST">
  <table width='300px' align='center'>
    <tr>
      <td colspan='3'>
  		<img src='images/BadmingerLogo.png' width='250' height='52'><br/><br/>
      </td>
    </tr>
    <tr>
      <td width='30%'>Username: </td><td width='10%'></td><td width='60%'><input type='text' name='txtUsername'></td>
    </tr>
    <tr>
      <td>Password:</td><td></td><td><input type='password' name='txtPassword'></td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td><br/>
   	 	<input type='submit' value='   Login   '>
    	<input type='reset' value='Reset'>
        </td>
    </tr>
    </table>
  </form>
 </div>
  </body>
</html>
<?php //phpinfo(); ?>