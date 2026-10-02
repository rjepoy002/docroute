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
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
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
	// 	$users[] = $row['lname'].", ".$row['fname']." ".$row['mname'];
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
				}

			}
	}

?>

<table style="background-color: ghostwhite; border-style: none; width: 100%; padding: 5px; border-radius: 5px; margin-top: 20px;">
	<tr>
		<td>
			<label style="font-size: 18px; color: black;">Welcome <?php echo $name." (".$designation."), "; ?></label>
			<a style="color: red; font-weight: 400; font-size: 18px;" href="http://192.168.18.14/docroute/index.php">Logout</a><br><br>
			<a style="color: gray; font-size: 18px;" href="http://192.168.18.14/docroute/new.php/?id=<?php echo $id; ?>">+CREATE</a>
			<a style="color: gray; margin-left: 10px; font-size: 18px;" href="http://192.168.18.14/docroute/search.php/?id=<?php echo $id; ?>">SEARCH</a>
			<?php

				if($type == 'admin'){
					?>
						<a style="color: gray; margin-left: 10px; font-size: 18px;" href="http://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>">DASHBOARD</a>
					<?php
				}

			?>
		</td>
	</tr>
</table>
<br>

<form action="" method="POST">
	<div class="row">
		<div class="column">
		  	<!-- ACCOUNTS -->
			<table width="100%">
				<tr>
					<td colspan="7" style="font-size: 24px; font-weight: 600; font-style: italic; background-color: blue; color: white; padding: 10px;">
						EXISTING ACCOUNTS
					</td>
				</tr>
				<tr>
					<th width="10%">NO</th>
					<th width="20%">DATECREATED</th>
					<th width="25%">NAME</th>
					<th width="15%">DESIGNATION</th>
					<th width="10%">DEPARTMENT</th>
					<th width="5">STATUS</th>
					<th width="15%">USERNAME</th>
				</tr>
					<?php 

					// $query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, a.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track 
					// 					WHERE date IN (SELECT MAX(date) AS date
					// 					FROM dr_logs
					// 					GROUP BY id_track) AND a.receiver = '$id' AND a.status = 'Pending'";
					$query = "SELECT * FROM dr_users";
					$query_run = mysqli_query($conn, $query);
					$counter = 0;
					$dtacount = 0;
					$dtaType = "password";

						if(mysqli_num_rows($query_run) > 0){
							
							foreach ($query_run as $row) {
									$dtacount++;
									if($counter == 0){
										
										?>
										<tr>
											<td><center><?php echo $dtacount; ?></center></td>
											<td><center><?php echo $row['datecreated']; ?></center></td>
											<td><?php echo $row['lname'].", ".$row['fname']." ".$row['mname']; ?></td>
											<td><?php echo $row['designation']; ?></td>
											<td><center><?php echo $row['department']; ?></center></td>
											<td>
												<center>
													<button name="btnStatus[]" id="btnStatus[]" value="<?php if($row['status'] == 'Inactive'){ echo "Inactive"."-".$row['id'];}else{echo "Active"."-".$row['id'];} ?>" onclick="if(!confirm('Change Status?')) { return false };" ><?php if($row['status'] == 'Inactive'){ echo "Inactive";}else{echo "Active";}?></button>
												</center>
											</td>
											<td>
												<center>
													<button name="btnView_i[]" id="btnView_i[]" value="<?php echo $row['username']; ?>">View</button>
												</center>
											</td>
										</tr>
										<?php

										$counter = 1;
									}else{

										?>
										<tr>
											<td style="background-color: lightyellow;"><center><?php echo $dtacount; ?></center></td>
											<td style="background-color: lightyellow;"><center><?php echo $row['datecreated']; ?></center></td>
											<td style="background-color: lightyellow;"><?php echo $row['lname'].", ".$row['fname']." ".$row['mname']; ?></td>
											<td style="background-color: lightyellow;"><?php echo $row['designation']; ?></td>
											<td style="background-color: lightyellow;"><center><?php echo $row['department']; ?></center></td>
											<td style="background-color: lightyellow;">
												<center>
													<button name="btnStatus[]" id="btnStatus[]" value="<?php if($row['status'] == 'Inactive'){ echo "Inactive"."-".$row['id'];}else{echo "Active"."-".$row['id'];} ?>" onclick="if(!confirm('Change Status?')) { return false };" ><?php if($row['status'] == 'Inactive'){ echo "Inactive";}else{echo "Active";} ?></button>
												</center>
											</td>
											<td style="background-color: lightyellow;">
												<center>
													<button name="btnView_i[]" id="btnView_i[]" value="<?php echo $row['username']; ?>">View</button>
												</center>
											</td>
										</tr>
										<?php

										$counter = 0;
									}
								
							}
						}else{
							?>
							<tr>
								<td colspan="6">
									<label style="font-size: 21px; padding: 20px;"><center>No registered users.</center></label>
								</td>
							</tr>
							<?php
						}
				?>	
			</table>
		</div>
	</div>
</form>

<?php
	if(isset($_POST['btnView_i'])){
		foreach($_POST['btnView_i'] as $btnView_i){
			?>
				<script>
					alert("Username: <?php echo $btnView_i; ?>")
				</script>
			<?php
		}
	}

	if(isset($_POST['btnStatus'])){
		foreach($_POST['btnStatus'] as $btnStatus){
			
			$status = substr($btnStatus,0,strpos($btnStatus,"-"));
			$id = substr($btnStatus, strpos($btnStatus, "-")+1, (strlen($btnStatus) - strpos($btnStatus, "-")));

			// echo $status." - ".$id;
			if($status == 'Active'){
				//set inactive
				$query = "UPDATE dr_users SET status='Inactive' WHERE id = '$id'";
       	$query_run = mysqli_query($conn, $query);
			}else{
				//set active
				$query = "UPDATE dr_users SET status='Active' WHERE id = '$id'";
				$query_run = mysqli_query($conn, $query);
			}

			echo "<meta http-equiv='refresh' content='0'>";

			

		}
	}

?>