<style type="text/css">
	
	*{
		font-family: Tahoma;
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

	table{
		border-style: solid;
		border-width: 1px;
		border-color: lightgray;
	}

.column {
  float: left;
}

.left, .right {
  width: 25%;
}

.middle {
  width: 50%;
}
</style>

<table style="background-color: darkgreen; width: 100%; padding: 10px;">
	<tr>
		<td style="color: white;">DASHBOARD</td>
	</tr>
</table>
<br>
<?php

	$conn = mysqli_connect("localhost","root","","local_pal_db");

	if ($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
	}

	//get list of users into array

	$query = "SELECT * FROM dr_users";
	$query_run = mysqli_query($conn, $query);

	foreach($query_run as $row){
		$users[] = $row['name'];
	}

	if(isset($_GET['id'])){
		$id = $_GET['id'];

		$query = "SELECT * FROM dr_users WHERE id = '$id'";
		$query_run = mysqli_query($conn, $query);


			if(mysqli_num_rows($query_run) > 0){
				
				foreach ($query_run as $row) {
					$name = $row['name'];
					$designation = $row['designation'];
				}

			}

		echo "Welcome ".$name." (".$designation."), ";
		?>
			<a href="http://localhost/doc_route/index.php"><font color="red"><b>Logout</b></font></a>
		<?php
	}

?>
<br>
<br>
<a href="http://localhost/doc_route/new.php/?id=<?php echo $id; ?>">CREATE</a>
<br><br>

<div class="row">
  <div class="column">COLUMN 1</div>
  <div class="column">COLUMN 2</div>
  <div class="column">COLUMN 3</div>
</div>

<form action="" method="POST">
<table width="100%">
	<tr>
		<td colspan="6" style="font-size: 28px; font-weight: 600; font-style: italic; background-color: limegreen; color: white; padding: 20px;">
			INCOMING DOCUMENTS
		</td>
	</tr>
	<tr>
		<th width="10%">DATE</th>
		<th width="10%">DOC_TRACKING#</th>
		<th>TITLE</th>
		<th>DESCRIPTION</th>
		<th width="15%">SENDER</th>
		<th width="10%">ACTION</th>
	</tr>

	<?php //INCOMING DOCUMENTS

		$query = "SELECT DISTINCT(a.id_track), date, b.title, b.description, a.sender, a.status, b.status FROM dr_logs as a LEFT JOIN dr_documents as b ON a.id_track = b.id_track WHERE receiver = '$id' and a.status = 'Pending' and b.status = 'open' LIMIT 10";
		$query_run = mysqli_query($conn, $query);
		$counter = 0;

			if(mysqli_num_rows($query_run) > 0){
				
				foreach ($query_run as $row) {
					if($counter == 0){

						?>
						<tr>
							<td><center><?php echo $row['date']; ?></center></td>
							<td><center><?php echo $row['id_track']; ?></center></td>
							<td><?php echo $row['title']; ?></td>
							<td><?php echo $row['description']; ?></td>
							<td>
								<?php
									echo $users[$row['sender']-1];
								?>
							</td>
							<td>
								<center>
								
								<button style="padding:10px;" name="btnAccept[]" id="btnAccept[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>" onclick="if(!confirm('Are you sure you want to accept?')) { return false };" >Accept</button>
								<button style="padding:10px;" name="btnDecline[]" id="btnDecline[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>">Decline</button>

								</center>
							</td>
						</tr>
						<?php

						$counter = 1;
					}else{

						?>
						<tr>
							<td style="background-color: lightyellow;"><center><?php echo $row['date']; ?></center></td>
							<td style="background-color: lightyellow;"><center><?php echo $row['id_track']; ?></center></td>
							<td style="background-color: lightyellow;"><?php echo $row['title']; ?></td>
							<td style="background-color: lightyellow;"><?php echo $row['description']; ?></td>
							<td style="background-color: lightyellow;">
								<?php
									echo $users[$row['sender']-1];
								?>
							</td>
							<td style="background-color: lightyellow;">
								<center>
								
								<button style="padding:10px;" name="btnAccept[]" id="btnAccept[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>" onclick="if(!confirm('Are you sure you want to accept?')) { return false };" >Accept</button>
								<button style="padding:10px;" name="btnDecline[]" id="btnDecline[]" value="<?php echo $row['id_track']."*".$row['sender']; ?>">Decline</button>

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
	
<br>
<br>

<table width="100%">
	<tr>
		<td colspan="7" style="font-size: 28px; font-weight: 600; font-style: italic; background-color: green; color: white; padding: 20px;">RECEIVABLES</td>
	</tr>

	<tr>
		<th width="10%">DATE</th>
		<th width="10%">DOC_TRACKING#</th>
		<th>TITLE</th>
		<th>DESCRIPTION</th>
		<th width="15%">SENDER</th>
		<th width="10%">STATUS</th>
		<th width="10%">ACTION</th>
	</tr>
	<tr>
	<?php 
		$query = "SELECT date, a.id_track, title, description, a.sender, a.status FROM dr_logs as a LEFT JOIN dr_documents as b ON a.id_track = b.id_track WHERE receiver = $id and a.status <> 'Pending' LIMIT 10";
		$query_run = mysqli_query($conn, $query);
		$counter = 0;

			if(mysqli_num_rows($query_run) > 0){
				
				foreach ($query_run as $row) {
					if($counter == 0){

						?>
						<tr>
							<td><center><?php echo $row['date']; ?></center></td>
							<td><center><?php echo $row['id_track']; ?></center></td>
							<td><?php echo $row['title']; ?></td>
							<td><?php echo $row['description']; ?></td>
							<td>
								<?php
									echo $users[$row['sender']-1];
								?>
							</td>
							<td><center><?php echo $row['status']; ?></center></td>
							<td>
								<center>
								
								<input style="padding: 10px" type="submit" name="btnView[]" id="btnView[]" value="View">
								
								</center>
							</td>
						</tr>
						<?php

						$counter = 1;
					}else{

						?>
						<tr>
							<td style="background-color: lightyellow;"><center><?php echo $row['date']; ?></center></td>
							<td style="background-color: lightyellow;"><center><?php echo $row['id_track']; ?></center></td>
							<td style="background-color: lightyellow;"><?php echo $row['title']; ?></td>
							<td style="background-color: lightyellow;"><?php echo $row['description']; ?></td>
							<td style="background-color: lightyellow;">
								<?php
									echo $users[$row['sender']-1];
								?>
							</td>
							<td style="background-color: lightyellow;"><center><?php echo $row['status']; ?></center></td>
							<td style="background-color: lightyellow;">
								<center>
								
								<input style="padding: 10px" type="submit" name="btnView[]" id="btnView[]" value="View">
								
								</center>
							</td>
						</tr>
						<?php

						$counter = 0;
					}

				}

			}
	?>
	</tr>

</table>

<?php

	$date = date("Y-m-d H:i:s");

	if(isset($_POST['btnAccept'])){
		foreach ($_POST['btnAccept'] as $btnAccept) {

			$tracking = substr($btnAccept, 0, strpos($btnAccept, "*"));
			$sender = substr($btnAccept, strpos($btnAccept, "*")+1, (strlen($btnAccept) - strpos($btnAccept, "*")));
			
			$query = "INSERT INTO dr_logs(id_track, sender, receiver, status, date) VALUES ('$tracking', '$sender', '$id', 'Received', '$date')";
	    	$query_run = mysqli_query($conn, $query);

	    	$query = "UPDATE dr_documents SET status='closed' WHERE id_track = '$tracking'";
	    	$query_run = mysqli_query($conn, $query);

	    	?>	
		    	<script>
		    		alert("Document Received!");
		    	</script>
	    	<?php
	    	echo "<meta http-equiv='refresh' content='0'>";

		}
	}

	if(isset($_POST['btnDecline'])){

	 	foreach ($_POST['btnDecline'] as $btnDecline) {

	 		?>
	 		<script>
	 			window.location.href='https://localhost/doc_route/decline.php/?id=<?php echo $id; ?>&tracking=<?php echo $btnDecline; ?>';
	 		</script>
	 		<?php
	 	}	
	}

	if(isset($_POST['btnForward'])){

		foreach($_POST['btnForward'] as $btnForward){
			echo $btnForward;
		}

	}
?>

<!--FORWARDED DOCUMENTS-->
<br>
<br>

<table width="100%">
	<tr>
		<td colspan="7" style="font-size: 28px; font-weight: 600; font-style: italic; background-color: royalblue; color: white; padding: 20px;">OUTGOING</td>
	</tr>

	<tr>
		<th width="10%">DATE</th>
		<th width="10%">DOC_TRACKING#</th>
		<th>TITLE</th>
		<th>DESCRIPTION</th>
		<th width="15%">LAST RECEIVER</th>
		<th width="10%">STATUS</th>
		<th width="10%">ACTION</th>
	</tr>
	<tr>
	<?php


		$query = "SELECT a.id_track, a.max_id, b.status as log_status, b.sender, b.receiver, b.date, c.title, c.description, c.author FROM
					(SELECT id_track, MAX(id) as max_id FROM dr_logs GROUP BY id_track) a
					LEFT JOIN
					(SELECT id, status, sender, receiver, date FROM dr_logs) b ON a.max_id = id 
					LEFT JOIN
				    dr_documents as c ON a.id_track = c.id_track WHERE c.author = '$id' LIMIT 10";
		$query_run = mysqli_query($conn, $query);
		$counter = 0;

			if(mysqli_num_rows($query_run) > 0){
				
				foreach ($query_run as $row) {
					if($counter == 0){
						?>
						<tr>
							<td><?php echo $row['date']; ?></td>
							<td><?php echo $row['id_track']; ?></td>
							<td><?php echo $row['title']; ?></td>
							<td><?php echo $row['description']; ?></td>
							<td>
								<?php
									if($id == $row['receiver']){
										echo $users[$row['sender']-1];
									}else{
										echo $users[$row['receiver']-1];
									}
								?>
							</td>
							<td><?php echo $row['log_status']; ?></td>
							<td>
								<center>

								<button style="padding: 10px" name="btnView[]" id="btnView[]">View</button>
								<?php
									if($row['log_status'] == "Declined"){
										?>
											<button style="padding: 10px" name="btnForward[]" id="btnForward[]" value="<?php echo $row['id_track']; ?>">Forward</button>
										<?php
									}

								?>

								<center>
							</td>
						</tr>
						<?php

						$counter = 1;
					}else{

						?>
						<tr>
							<td style="background-color: lightyellow;"><?php echo $row['date']; ?></td>
							<td style="background-color: lightyellow;"><?php echo $row['id_track']; ?></td>
							<td style="background-color: lightyellow;"><?php echo $row['title']; ?></td>
							<td style="background-color: lightyellow;"><?php echo $row['description']; ?></td>
							<td style="background-color: lightyellow;">
								<?php
									if($id == $row['receiver']){
										echo $users[$row['sender']-1];
									}else{
										echo $users[$row['receiver']-1];
									}
								?>
							</td>
							<td style="background-color: lightyellow;"><?php echo $row['log_status']; ?></td>
							<td style="background-color: lightyellow;">
								<center>

								<button style="padding: 10px" name="btnView[]" id="btnView[]">View</button>
								<?php
									if($row['log_status'] == "Declined"){
										?>
											<button style="padding: 10px" name="btnForward[]" id="btnForward[]" value="<?php echo $row['id_track']; ?>">Forward</button>
										<?php
									}

								?>

								<center>
							</td>
						</tr>
						<?php

						$counter = 0;
					}


				}

			}
	?>
	</tr>

</table>

</form>