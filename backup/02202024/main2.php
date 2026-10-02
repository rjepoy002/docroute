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
					$isgroup = $row['isgroup'];
				}

			}
	}

	if($isgroup == 0){
		?>
			<script>
				window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'
			</script>
		<?php
	}

?>

<table style="background-color: ghostwhite; border-style: none; width: 100%; padding: 5px; border-radius: 5px; margin-top: 20px;">
	<tr>
		<td>
			<label style="font-size: 18px; color: black;">Welcome <?php echo $name." (".$designation."), "; ?></label>
			<a style="color: red; font-weight: 400; font-size: 18px;" href="http://192.168.18.14/docroute/index.php">Logout</a><br><br>
			<a style="color: gray; font-size: 18px;" href="http://192.168.18.14/docroute/new.php/?id=<?php echo $id; ?>">+CREATE</a>
			<a style="color: gray; margin-left: 10px; font-size: 18px;" href="http://192.168.18.14/docroute/search.php/?id=<?php echo $id; ?>">SEARCH</a>
			<a style="color: gray; margin-left: 10px; font-size: 18px;" href="http://192.168.18.14/docroute/help.php/?id=<?php echo $id; ?>">HELP</a>
			<?php

				if($type == 'admin'){
					?>
						<a style="color: gray; margin-left: 10px; font-size: 18px;" href="http://192.168.18.14/docroute/admin.php/?id=<?php echo $id; ?>">ACCOUNTS</a>
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
  <!-- INCOMING -->
	<table width="100%">
		<tr>
			<td colspan="6" style="font-size: 24px; font-weight: 600; font-style: italic; background-color: limegreen; color: white; padding: 10px;">
				INCOMING DOCUMENTS
			</td>
		</tr>
		<tr>
			<th width="10%">NO</th>
			<th width="40%">SENDER</th>
			<th width="25%">DATE</th>
			<th width="15%">DOCUMENT</th>
			<!-- <th width="25%">REMARKS</th> -->
			<th width="10">ACTION</th>
		</tr>

		<?php //INCOMING DOCUMENTS

			// $query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, a.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track 
			// 					WHERE date IN (SELECT MAX(date) AS date
			// 					FROM dr_logs
			// 					GROUP BY id_track) AND a.receiver = '$id' AND a.status = 'Pending' ORDER BY a.date DESC";


			$query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, CONCAT(c.lname,', ',c.fname,' ',left(c.mname,1),'.') as name , count(*) as documents, a.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track
	LEFT JOIN dr_users as c ON a.sender = c.id
								WHERE date IN (SELECT MAX(date) AS date
								FROM dr_logs
								GROUP BY id_track) AND a.receiver = '$id' AND a.status = 'Pending' GROUP BY a.sender ORDER BY date DESC";
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
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $row['date']; ?></td>
									<td><center><?php echo $row['documents']; ?></center></td>
									<td>
										<center>
											<button name="btnView_i[]" id="btnView_i[]" value="<?php echo $row['sender']; ?>">View</button>
										</center>
									</td>
								</tr>
								<?php

								$counter = 1;
							}else{

								?>
								<tr>
									<td style="background-color: lightyellow;"><center><?php echo $count; ?></center></td>
									<td style="background-color: lightyellow;"><?php echo $row['name']; ?></td>
									<td style="background-color: lightyellow;"><?php echo $row['date']; ?></td>
									<td style="background-color: lightyellow;"><center><?php echo $row['documents']; ?></center></td>
									<td style="background-color: lightyellow;">
										<center>
											<button name="btnView_i[]" id="btnView_i[]" value="<?php echo $row['sender']; ?>">View</button>
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

<!-- RECEIVABLES -->
	<br>
	<table width="100%">
		<tr>
			<td colspan="6" style="font-size: 24px; font-weight: 600; font-style: italic; background-color: green; color: white; padding: 10px;">RECEIVABLES</td>
		</tr>

		<tr>
			<th width="10%">NO</th>
			<th width="40%">SENDER</th>
			<th width="25%">DATE</th>
			<th width="15%">DOCUMENT</th>
			<!-- <th width="25%">REMARKS</th> -->
			<th width="10">ACTION</th>
		</tr>
		<tr>
		<?php 

			// $query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, a.status as stats, b.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track 
			// 					WHERE date IN (SELECT MAX(date) AS date
			// 					FROM dr_logs
			// 					GROUP BY id_track) AND a.receiver = '$id' AND a.status <> 'Pending' AND b.status = 'Open'  ORDER BY a.date DESC";

			$query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, CONCAT(c.lname,', ',c.fname,' ',left(c.mname,1),'.') as name, count(*) as documents, b.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track LEFT JOIN dr_users as c ON a.sender = c.id
								WHERE date IN (SELECT MAX(date) AS date
								FROM dr_logs
								GROUP BY id_track) AND a.receiver = '$id' AND a.status <> 'Pending' AND b.status = 'Open' GROUP BY a.sender ORDER BY a.date DESC";

	// 		$query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, CONCAT(c.lname,', ',c.fname,' ',left(c.mname,1),'.') as name , count(*) as documents, a.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track
	// LEFT JOIN dr_users as c ON a.sender = c.id
	// 							WHERE date IN (SELECT MAX(date) AS date
	// 							FROM dr_logs
	// 							GROUP BY id_track) AND a.receiver = '$id' AND a.status = 'Pending' GROUP BY a.sender ORDER BY date DESC";

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
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $row['date']; ?></td>
									<td><center><?php echo $row['documents']; ?></center></td>
									<td>
										<center>
											<button name="btnView_r[]" id="btnView_r[]" value="<?php echo $row['sender']; ?>">View</button>
										</center>
									</td>
								</tr>
								<?php

								$counter = 1;
							}else{

								?>
								<tr>
									<td style="background-color: lightyellow;"><center><?php echo $count; ?></center></td>
									<td style="background-color: lightyellow;"><?php echo $row['name']; ?></td>
									<td style="background-color: lightyellow;"><?php echo $row['date']; ?></td>
									<td style="background-color: lightyellow;"><center><?php echo $row['documents']; ?></center></td>
									<td style="background-color: lightyellow;">
										<center>
											<button name="btnView_r[]" id="btnView_r[]" value="<?php echo $row['sender']; ?>">View</button>
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
							<label style="font-size: 21px; padding: 20px;"><center>No documents received</center></label>
						</td>
					</tr>
					<?php
				}
		?>
		</tr>

	</table>

	<br>
  	<!-- OUTGOING -->
	<table width="100%">
		<tr>
			<td colspan="7" style="font-size: 24px; font-weight: 600; font-style: italic; background-color: royalblue; color: white; padding: 10px;">OUTGOING</td>
		</tr>

		<tr>
			<th width="10%">NO</th>
			<th width="40%">RECEIVER</th>
			<th width="25%">DATE</th>
			<th width="15%">DOCUMENT</th>
			<!-- <th width="25%">REMARKS</th> -->
			<th width="10">ACTION</th>
		</tr>
		<tr>
		<?php

			$query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, CONCAT(c.lname,', ',c.fname,' ',left(c.mname,1),'.') as name , count(*) as documents, a.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track LEFT JOIN dr_users as c ON a.receiver = c.id
								WHERE date IN (SELECT MAX(date) AS date
								FROM dr_logs
								GROUP BY id_track) AND a.sender = '$id' AND a.status = 'Pending' GROUP BY a.receiver ORDER BY a.date DESC";


	// 		$query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, CONCAT(c.lname,', ',c.fname,' ',left(c.mname,1),'.') as name , count(*) as documents, a.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track
	// LEFT JOIN dr_users as c ON a.sender = c.id
	// 							WHERE date IN (SELECT MAX(date) AS date
	// 							FROM dr_logs
	// 							GROUP BY id_track) AND a.receiver = '$id' AND a.status = 'Pending' GROUP BY a.sender ORDER BY date DESC";


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
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $row['date']; ?></td>
									<td><center><?php echo $row['documents']; ?></center></td>
									<td>
										<center>
											<button name="btnView_o[]" id="btnView_o[]" value="<?php echo $row['receiver']; ?>">View</button>
										</center>
									</td>
								</tr>
							<?php

							$counter = 1;
						}else{

							?>
							<tr>
									<td style="background-color: lightyellow;"><center><?php echo $count; ?></center></td>
									<td style="background-color: lightyellow;"><?php echo $row['name']; ?></td>
									<td style="background-color: lightyellow;"><?php echo $row['date']; ?></td>
									<td style="background-color: lightyellow;"><center><?php echo $row['documents']; ?></center></td>
									<td style="background-color: lightyellow;">
										<center>
											<button name="btnView_o[]" id="btnView_o[]" value="<?php echo $row['receiver']; ?>">View</button>
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
							<label style="font-size: 21px; padding: 20px;"><center>No outgoing documents</center></label>
						</td>
					</tr>
					<?php
				}
		?>
		</tr>

	</table>


  </div>

  <div class="column" style="padding-left: 1%;"> 
  	<!-- RELEASED -->
	<table width="100%">
		<tr>
			<td colspan="7" style="font-size: 24px; font-weight: 600; font-style: italic; background-color: skyblue; color: white; padding: 10px;">RELEASED</td>
		</tr>

		<tr>
			<th width="18%">DATE</th>
			<th width="10%">TRACKING#</th>
			<th>TITLE</th>
			<!-- <th>DESCRIPTION</th> -->
			<th width="18%">RECEIVER</th>
			<th width="10%">STATUS</th>
			<th width="18%">ACTION</th>
		</tr>
		<tr>
		<?php


			// $query = "SELECT a.id_track, a.max_id, b.status as log_status, b.sender, b.receiver, b.date, c.title, c.description, c.author FROM
			// 			(SELECT id_track, MAX(id) as max_id FROM dr_logs GROUP BY id_track) a
			// 			LEFT JOIN
			// 			(SELECT id, status, sender, receiver, date FROM dr_logs) b ON a.max_id = id 
			// 			LEFT JOIN
			// 		    dr_documents as c ON a.id_track = c.id_track WHERE c.author = '$id' LIMIT 10";

			// $query = "SELECT a.id, a.date, a.id_track, b.title, a.status, a.sender, a.receiver FROM dr_logs as a LEFT JOIN dr_documents as b ON a.id_track = b.id_track
			// 	WHERE a.id IN (SELECT MAX(id) AS id
			// 	FROM dr_logs WHERE sender = '$id'
			// 	GROUP BY id_track) ORDER BY a.date DESC";

			$query = "SELECT a.id_track, a.date, b.title, a.receiver, a.sender, a.status FROM dr_logs AS a LEFT JOIN dr_documents as b ON a.id_track = b.id_track 
								WHERE date IN (SELECT MAX(date) AS date
								FROM dr_logs
								GROUP BY id_track) AND a.sender = '$id' AND a.status <> 'Pending' AND a.status <> 'Change Title' ORDER BY a.date DESC LIMIT 10";
			$query_run = mysqli_query($conn, $query);
			$counter = 0;

				if(mysqli_num_rows($query_run) > 0){
					
					foreach ($query_run as $row) {
						if($counter == 0){
							if($row['status'] == 'Declined'){
								?>
								<tr>
									<td style="color: red;"><?php echo $row['date']; ?></td>
									<td style="color: red;"><?php echo $row['id_track']; ?></td>
									<td style="color: red;"><?php echo $row['title']; ?></td>
									<td style="color: red;">
										<?php
											if($id == $row['receiver']){
												echo $users[$row['sender']-1];
											}else{
												echo $users[$row['receiver']-1];
											}
										?>
									</td>
									<td style="color: red;"><?php echo $row['status']; ?></td>
									<td>
										<center>
											<button name="btnView_rr[]" id="btnView_rr[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>">View</button>
										<center>
									</td>
								</tr>
							<?php
							}else{
								?>
								<tr>
									<td><?php echo $row['date']; ?></td>
									<td><?php echo $row['id_track']; ?></td>
									<td><?php echo $row['title']; ?></td>
									<!-- <td><?php echo $row['description']; ?></td> -->
									<td>
										<?php
											if($id == $row['receiver']){
												echo $users[$row['sender']-1];
											}else{
												echo $users[$row['receiver']-1];
											}
										?>
									</td>
									<td><?php echo $row['status']; ?></td>
									<td>
										<center>
											<button name="btnView_rr[]" id="btnView_rr[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>">View</button>
										<center>
									</td>
								</tr>
								<?php
							}
							

							$counter = 1;
						}else{
							if($row['status'] == 'Declined'){
								?>
								<tr>
									<td style="background-color: lightyellow; color: red;"><?php echo $row['date']; ?></td>
									<td style="background-color: lightyellow; color: red;"><?php echo $row['id_track']; ?></td>
									<td style="background-color: lightyellow; color: red;"><?php echo $row['title']; ?></td>
									<td style="background-color: lightyellow; color: red;">
										<?php
											if($id == $row['receiver']){
												echo $users[$row['sender']-1];
											}else{
												echo $users[$row['receiver']-1];
											}
										?>
									</td>
									<td style="background-color: lightyellow; color: red;"><?php echo $row['status']; ?></td>
									<td style="background-color: lightyellow;">
										<center>
											<button name="btnView_rr[]" id="btnView_rr[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>">View</button>
										<center>
									</td>
								</tr>
								<?php
							}else{
								?>
								<tr>
									<td style="background-color: lightyellow;"><?php echo $row['date']; ?></td>
									<td style="background-color: lightyellow;"><?php echo $row['id_track']; ?></td>
									<td style="background-color: lightyellow;"><?php echo $row['title']; ?></td>
									<td style="background-color: lightyellow;">
										<?php
											if($id == $row['receiver']){
												echo $users[$row['sender']-1];
											}else{
												echo $users[$row['receiver']-1];
											}
										?>
									</td>
									<td style="background-color: lightyellow;"><?php echo $row['status']; ?></td>
									<td style="background-color: lightyellow;">
										<center>
											<button name="btnView_rr[]" id="btnView_rr[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>">View</button>
										<center>
									</td>
								</tr>
								<?php
							}


							$counter = 0;
						}
					}
				}else{
					?>
					<tr>
						<td colspan="6">
							<label style="font-size: 21px; padding: 20px;"><center>No released documents</center></label>
						</td>
					</tr>
					<?php
				}
		?>
		</tr>

	</table>
  </div>

</div>

<?php

	$date = date("Y-m-d H:i:s");

	if(isset($_POST['btnView_i'])){
		foreach($_POST['btnView_i'] as $btnView){
			?>
	 		<script>
	 			window.location.href='https://192.168.18.14/docroute/main2_i.php/?id=<?php echo $id; ?>&sender=<?php echo $btnView; ?>';
	 		</script>
	 		<?php
		}
	}

	if(isset($_POST['btnView_r'])){
		foreach($_POST['btnView_r'] as $btnView){
			?>
	 		<script>
	 			window.location.href='https://192.168.18.14/docroute/main2_r.php/?id=<?php echo $id; ?>&sender=<?php echo $btnView; ?>';
	 		</script>
	 		<?php
		}
	}

	if(isset($_POST['btnView_o'])){
		foreach($_POST['btnView_o'] as $btnView){
			?>
	 		<script>
	 			window.location.href='https://192.168.18.14/docroute/main2_o.php/?id=<?php echo $id; ?>&receiver=<?php echo $btnView; ?>';
	 		</script>
	 		<?php
		}
	}

	if(isset($_POST['btnView_rr'])){
		foreach($_POST['btnView_rr'] as $btnView){
			?>
	 		<script>
	 			window.location.href='https://192.168.18.14/docroute/view.php/?id=<?php echo $id; ?>&tracking=<?php echo $btnView; ?>&type=<?php echo 'released'; ?>';
	 		</script>
	 		<?php
		}
	}

?>

<!--FORWARDED DOCUMENTS-->
<br>
<br>


</form>