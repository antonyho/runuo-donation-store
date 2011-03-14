<?php
session_start();
if ($_GET['logout'] == 1)
	session_destroy();
require_once('admin_util.php');

if (isset($_SESSION['login_succeed']) && $_SESSION['login_succeed'] === true)
{
	//todo: options
	//add donation items
	//view transactions in database
	//view logs
	$output = "<a href=\"#info\" onclick=\"load('admin_ops.php?t=1')\">Add donation items</a><br/>";
	$output .= "<a href=\"#info\" onclick=\"load('admin_ops.php?t=4')\">Remove donation items</a><br/>";
	$output .= "<a href=\"#info\" onclick=\"load('admin_ops.php?t=2')\">View all transactions in database</a><br/>";
	$output .= "<a href=\"#info\" onclick=\"load('admin_ops.php?t=3')\">View all logs</a><br/>";
	$output .= "<a href=\"#info\" onclick=\"load('admin_ops.php?t=5')\">Get donation buttons HTML code</a><br/>";
	$output .= "<a href=\"#info\" onclick=\"load('admin_ops.php?t=6')\">Manual add gift to account</a><br/>";
	$output .= "<a href=\"adminpage.php?logout=1\">Logout</a><br/>";
}
else
{
	if (isset($_POST['username']) && isset($_POST['password']))
	{
		admin_login($_POST['username'], $_POST['password']);
		header("Location: adminpage.php");
		return;
	}
	else
	{
		$output = "<form method=\"POST\" action=\"adminpage.php\">";
		$output .= "Username: <input type=\"text\" name=\"username\" /><br/>";
		$output .= "Password: <input type=\"password\" name=\"password\" /><br/>";
		$output .= "<input type=\"submit\" value=\"Login\" />";
		$output .= "</form>";
	}
}

?>

<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>

<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Donation Store Back-office</title>
		<script type="text/javascript">
			function GetXmlHttpObject()
			{
				if (window.XMLHttpRequest)
				{
					// code for IE7+, Firefox, Chrome, Opera, Safari
					return new XMLHttpRequest();
				}
				if (window.ActiveXObject)
				{
					// code for IE6, IE5
					return new ActiveXObject("Microsoft.XMLHTTP");
				}
				return null;
			}
			
			function load(uri)
			{
				xmlhttp = GetXmlHttpObject();
				if (xmlhttp == null)
					alert("Your browser does not support XMLHTTP!");
				xmlhttp.onreadystatechange = loading;
				xmlhttp.open("GET", uri, true);
				xmlhttp.send();
			}
			
			function submitForm(formId,uri)
			{
				xmlhttp = GetXmlHttpObject();
				if (xmlhttp == null)
				{
					alert("Your browser does not support XMLHTTP!");
				}
				var form = document.getElementById(formId);
				var inputs = form.getElementsByTagName("input");
				var post_value = "";
				var i;
				for (i in inputs) {
					post_value += inputs[i].name + "=" + inputs[i].value + "&";
				}
				var selects = form.getElementsByTagName("select");
				for (j in selects)
				{
					post_value += selects[j].name + "=" + selects[j].value + "&";
				}
				xmlhttp.onreadystatechange = loading;
				xmlhttp.open("POST", uri, true);
				xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xmlhttp.setRequestHeader("Content-length", post_value.length);
				xmlhttp.setRequestHeader("Connection", "close");
				xmlhttp.send( post_value );
			}
			
			function loading()
			{
				var targ_div = document.getElementById("lower");
				if ( xmlhttp.readyState < 4 )
					targ_div.innerHTML = "Loading...";
				else if ( (xmlhttp.readyState == 4) && (xmlhttp.status == 200) )
					targ_div.innerHTML = xmlhttp.responseText;
			}
		</script>
	</head>
	<body>
		<div style="width: 100%; height: 100%;" id="outter">
			<div style="border: solid 2px; padding: 10px;" id="upper">
				<?php print($output); ?>
			</div>
			<a name="info">
			<div style="border: solid 2px; padding: 10px;" id="lower">
			</div>
			</a>
		</div>
	</body>
</html>
