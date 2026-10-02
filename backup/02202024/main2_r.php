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

	$query = "SELECT * FROM dr_users";
	$query_run = mysqli_query($conn, $query);

	foreach($query_run as $row){
		$users[] = $row['lname'].", ".$row['fname'];
	}

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

	if(isset($_GET['sender'])){
		$sender = $_GET['sender'];
	}

?>

<table style="background-color: ghostwhite; border-style: none; width: 100%; padding: 5px; border-radius: 5px; margin-top: 20px;">
	<tr>
		<td>
			<label style="font-size: 18px; color: black;">Welcome <?php echo $name." (".$designation."), "; ?></label>
			<a style="color: red; font-weight: 400; font-size: 18px;" href="http://192.168.18.14/docroute/index.php">Logout</a><br><br>
			<a style="color: gray; font-size: 18px;" href="http://192.168.18.14/docroute/main2.php/?id=<?php echo $id; ?>">+BACK</a>
		</td>
	</tr>
</table>
<br>

<form action="" method="POST">
<div class="row">
  <div class="column">
  <!-- RECEIVABLES -->
	<table width="100%">
		<tr>
			<td colspan="7" style="font-size: 24px; font-weight: 600; font-style: italic; background-color: limegreen; color: white; padding: 10px;">
				RECEIVABLES
			</td>
		</tr>
		<tr>
			<th width="5%">NO</th>
			<th width="20%">DATE</th>
			<th width="15%">TRACKING#</th>
			<th width="20%">TITLE</th>
			<th width="10%">STATUS</th>
			<th width="20%">SENDER</th>
			<th width="10%">ACTION</th>
		</tr>

		<?php //RECEIVABLES

			// $query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, a.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track 
			// 					WHERE date IN (SELECT MAX(date) AS date
			// 					FROM dr_logs
			// 					GROUP BY id_track) AND a.sender = '$sender' AND a.status = 'Pending' ORDER BY a.date DESC";


	// 		$query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, CONCAT(c.lname,', ',c.fname,' ',left(c.mname,1),'.') as name, a.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track
	// LEFT JOIN dr_users as c ON a.sender = c.id
	// 							WHERE date IN (SELECT MAX(date) AS date
	// 							FROM dr_logs
	// 							GROUP BY id_track) AND a.receiver = '$id' AND a.status = 'Pending' AND a.sender = '$sender' ORDER BY date DESC";


			$query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender,CONCAT(c.lname,', ',c.fname,' ',left(c.mname,1),'.') as name, a.status as stats, b.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track LEFT JOIN dr_users as c ON a.sender = c.id
								WHERE date IN (SELECT MAX(date) AS date
								FROM dr_logs
								GROUP BY id_track) AND a.receiver = '$id' AND a.status <> 'Pending' AND b.status = 'Open' AND a.sender ='$sender'  ORDER BY a.date DESC";
			$query_run = mysqli_query($conn, $query);
			$counter = 0;
			$count = 0;

				if(mysqli_num_rows($query_run) > 0){
					
					foreach ($query_run as $row) {
							$count++;
							if($counter == 0){

								?>
								<tr>
									<td><center><?php echo $count; ?></center></td>
									<td><center><?php echo $row['date']; ?></center></td>
									<td><center><?php echo $row['id_track']; ?></center></td>
									<td><?php echo $row['title']; ?></td>
									<td><center><?php echo $row['stats']; ?></center></td>
									<!-- <td><?php echo $row['description']; ?></td> -->
									<td>
										<?php
											echo $users[$row['sender']-1];
											// echo $row['sender'];
										?>
									</td>
									<td>
										<center>
											<button name="btnView_r[]" id="btnView_r[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>">View</button>
										</center>
									</td>
								</tr>
								<?php

								$counter = 1;
							}else{

								?>
								<tr>
									<td style="background-color: lightyellow;"><center><?php echo $count; ?></center></td>
									<td style="background-color: lightyellow;"><center><?php echo $row['date']; ?></center></td>
									<td style="background-color: lightyellow;"><center><?php echo $row['id_track']; ?></center></td>
									<td style="background-color: lightyellow;"><?php echo $row['title']; ?></td>
									<td style="background-color: lightyellow;"><center><?php echo $row['stats']; ?></center></td>
									<!-- <td style="background-color: lightyellow;"><?php echo $row['description']; ?></td> -->
									<td style="background-color: lightyellow;">
										<?php
											echo $users[$row['sender']-1];
										?>
									</td>
									<td style="background-color: lightyellow;">
										<center>
											<button name="btnView_r[]" id="btnView_r[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>">View</button>
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
							<label style="font-size: 21px; padding: 20px;"><center>No documents to received</center></label>
						</td>
					</tr>
					<?php
				}
		?>	

	</table>
  </div>

</div>

<?php

	$date = date("Y-m-d H:i:s");

	if(isset($_POST['btnView_r'])){
		foreach($_POST['btnView_r'] as $btnView){
			?>
	 		<script>
	 			window.location.href='https://192.168.18.14/docroute/view.php/?id=<?php echo $id; ?>&tracking=<?php echo $btnView; ?>&type=<?php echo 'receivables'; ?>';
	 		</script>
	 		<?php
		}
	}

?>

<!--FORWARDED DOCUMENTS-->
<br>
<br>


</form>