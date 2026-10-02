<style type="text/css">
	
	*{
		font-family: Tahoma;
		font-size: 14;
	}
	th{
		align-content: right;
		background-color: lightblue;
		padding: 10px;
	}

	td{
		align-content: right;
		padding: 10px;
	}

	button{
		padding: 10px;
	}

	table{
		border-style: solid;
		border-width: 1px;
		border-color: lightgray;
	}

.column {
  float: left;
  width: 49%;
}


</style>

<?php
session_start();
date_default_timezone_set('Asia/Taipei');

	$conn = mysqli_connect("localhost","root","","local_pal_db");

	if ($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
	}

	if(!isset($_SESSION['DOCROUTE'])){
    ?>
    	<script> window.location.href='http://192.168.18.14/docroute/index.php/'</script>
    <?php
  	}


	//get list of users into array

	// $query = "SELECT * FROM dr_users";
	// $query_run = mysqli_query($conn, $query);

	// foreach($query_run as $row){
	// 	$users[] = $row['lname'].", ".$row['fname'];
	// }

	if(isset($_GET['id'])){
		$id = $_GET['id'];

		$query = "SELECT * FROM dr_users WHERE id = '$id'";
		$query_run = mysqli_query($conn, $query);

			if(mysqli_num_rows($query_run) > 0){

				foreach ($query_run as $row) {
					$name = $row['lname'].", ".$row['fname']." ".$row['mname'];
					$designation = $row['designation'];
					$type = $row['type'];
					$group = $row['isgroup'];
				}

			}
	}

?>
<table style="background-color: ghostwhite; border-style: none; width: 100%; padding: 5px; border-radius: 5px; margin-top: 20px;">
	<tr>
		<td>
			<label style="font-size: 18px; color: black;">Welcome <?php echo $name." (".$designation."), "; ?></label>
			<a style="color: red; font-weight: 400; font-size: 18px;" href="http://192.168.18.14/docroute/index.php">Logout</a><br><br>
			<!-- <a style="color: gray; font-size: 18px;" href="javascript:history.back()">BACK</a> -->
			<a style="color: gray; font-size: 18px;" href="http://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>">BACK</a>
		</td>
	</tr>
</table>
<br>

<form action="" method="POST">
	<table>
		<tr>
			<th colspan="2">SETTINGS</th>
		</tr>
		<tr>
			<td>Dashboard Groupings:</td>
			<td>
				<button name="btnGroup" id="btnGroup" value="<?php echo $group; ?>"><?php if($group == 0){echo "OFF";}else{ echo "ON";} ?></button>
			</td>
		</tr>
	</table>
</form>
<br>

<table>
	<tr>
		<th>DATE</th>
		<th>CHANGES</th>
		<th>STATUS</th>
	</tr>
	<tr>
		<td>02/08/2024</td>
		<td>Tracking ID: +authorid</td>
		<td>Updated</td>
	</tr>
	<tr>
		<td>02/08/2024</td>
		<td>Search-View-Back Button: Retains previous search details. </td>
		<td>Updated</td>
	</tr>
	<tr>
		<td>02/08/2024</td>
		<td>Fix: Using special characters generate error. (addslashes)</td>
		<td>Updated</td>
	</tr>
	<tr>
		<td>02/16/2024</td>
		<td>Grouping of documents</td>
		<td>Updated</td>
	</tr>
	<tr>
		<td>-</td>
		<td>Outgoing documents Edit button</td>
		<td>To be Updated</td>
	</tr>
	<tr>
		<td>-</td>
		<td>Change create and forward receipient (focal person to department)<br>Any registered personnels in the department can now view and receive incoming documents</td>
		<td>To be Updated</td>
	</tr>
	<tr>
		<td>-</td>
		<td>Follow-up notification</td>
		<td>To be Updated</td>
	</tr>
	<tr>
		<td>-</td>
		<td>Forward - Title Editing</td>
		<td>To be Updated</td>
	</tr>


</table>

<?php 

if(isset($_POST['btnGroup'])){
	$btnGroup = $_POST['btnGroup'];
	$isgroup = $btnGroup;

	if($btnGroup == 0){
		//set to 1 active
		$isgroup = 1;
    
	}else{
		//set to 0 inactive
		$isgroup = 0;
	}

	$query = "UPDATE dr_users SET isgroup='$isgroup' WHERE id = '$id'";
	$query_run = mysqli_query($conn, $query);
	echo "<meta http-equiv='refresh' content='0'>";
}

?>