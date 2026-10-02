<style>
   *{
        box-sizing:  border-box;
        font-family: Calibri;
        font-size: 16px;
        font-weight: 400;
    }

    input{
    	border-radius: 5px;
    	padding: 10px;
    	border-style: solid 1 ghostwhite;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        border-style: none;

    }

    th{
        text-align: center !important;
        background-color: lightblue;
        color: gray;
        padding: 10px;
        border: 1px solid ghostwhite;
        border-radius: 8px;
    }
    td{
        text-align: left;
       	border: 1px solid ghostwhite;
       	padding: 10px;
    }
    .column{
    	float: left;
  		padding: 10px;
    }

    .custom_1{
    	background-color: green;
    }
</style>

<?php
header("Cache-Control: no cache");
session_cache_limiter("private_no_expire");
date_default_timezone_set('Asia/Taipei');

	//$conn = mysqli_connect("localhost","palecxzp","P4LECO@1974","palecxzp_pal_db");
	$conn = mysqli_connect("localhost","root","","local_pal_db");
	$tracking = "";
	$z = "";

	//if(!isset($_SESSION['DOCROUTE'])){
	    ?>
	    	<!-- <script> window.location.href='http://192.168.18.14/docroute/index.php/'</script> -->
	    <?php
  	//}

	if ($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
    }

    if(isset($_GET['id']) ){
    	$id = $_GET['id'];
    }

    if(isset($_GET['type'])){
    	$type = $_GET['type'];
    }

    if(isset($_GET['tracking']) ){
    	$code = $_GET['tracking'];

    	if(!isset($_GET['search'])){
    		$tracking = substr($code, 0, strpos($code, "*"));
	 		$receiver = substr($code, strpos($code, "*")+1, (strlen($code) - strpos($code, "*")));
    	}else{
    		$tracking = $code;
    	}
		
    }

	$query = "SELECT * FROM dr_documents as a LEFT JOIN dr_users as b ON a.author = b.id WHERE id_track = '$tracking'";
	$query_run = mysqli_query($conn, $query);

	if(mysqli_num_rows($query_run) > 0){
		foreach ($query_run as $row) {
			$tracking = $row['id_track'];
			$title = $row['title'];
			$description = $row['description'];
			$author = $row['lname'].", ".$row['fname']." ".$row['mname'];
			$datecreated =  $row['datecreated'];
			$designation = $row['designation'];
		}
	}
	
?>

<form accept="" method="POST">

	<div class="row">
		<div class="column" style="width: 30%;">
		</div>
		<div class="column" style="width: 40%; height: 400px;">
			<table style="margin-top: 20px;">
				<tr>
					<td style="text-align: center; border-style: none;">
						<label style="font-size: 42px; font-weight: 700; color: blue; ">DOCUMENT</label><br>
						<label style="font-size: 20px; font-weight: 600; color: gray; ">VIEWING</label>
					</td>
				</tr>
			</table>
			<table style="margin-top: 10px;">
				<tr>
					<td style="border-style: none;">
						<table>
							<tr>
								<th style="text-align: left !important; font-weight: 600;">DATE CREATED</th>
							</tr>
							<tr>
								<td><?php echo $datecreated; ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="border-style: none;">
						<table>
							<tr>
								<th style="text-align: left !important; font-weight: 600; width: 20%;">TRACKING ID</th>
								<th style="text-align: left !important; font-weight: 600;">DOCUMENT TITLE</th>
							</tr>
							<tr>
								<td><?php echo $tracking; ?></td>
								<td><?php echo $title; ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="border-style: none;">
						<table>
							<tr>
								<th style="text-align: left !important; font-weight: 600;">DOCUMENT DESCRIPTION</th>
							</tr>
							<tr>
								<td><p style="white-space: pre-line"><?php echo $description; ?></p></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="border-style: none;">
						<table>
							<tr>
								<th style="text-align: left !important; font-weight: 600; width: 40%;">AUTHOR</th>
								<th style="text-align: left !important; font-weight: 600;">DESIGNATION</th>
							</tr>
							<tr>
								<td><?php echo $author; ?></td>
								<td><?php echo $designation; ?></td>
							</tr>
						</table>
					</td>
				</tr>

				<tr>
					<td style="border-style: none;">

						<button style="float: right; padding: 10px; padding-left: 20px; padding-right: 20px; margin-left: 10px; margin-top: 5px;" type="button" name="btnBack" id="btnBack" onclick="window.history.back()">Back</button>
						<?php
							if(isset($_GET['type'])){

								$type = $_GET['type'];

								if($type == 'incoming'){
									?>
										<button style="float: right; padding: 10px; padding-left: 20px; padding-right: 20px; margin-left: 10px; margin-top: 5px;" name="btnDecline[]" id="btnDecline[]">Decline</button>
										<!-- value="<?php // echo $row['id_track']."*".$row['sender']; ?>" -->

										<button style="float: right; padding: 10px; padding-left: 20px; padding-right: 20px; margin-left: 10px; margin-top: 5px;" name="btnAccept[]" id="btnAccept[]" onclick="if(!confirm('Are you sure you want to accept?')) { return false };" >Accept</button>
										<!-- value="<?php //echo $row['id_track']."*".$row['sender']; ?>" -->
									<?php
								}

								if($type == 'receivables'){
									?>
										<button style="float: right; padding: 10px; padding-left: 20px; padding-right: 20px; margin-left: 10px; margin-top: 5px;" name="btnForward" id="btnForward">Forward</button>
										<button style="float: right; padding: 10px; padding-left: 20px; padding-right: 20px; margin-left: 10px; margin-top: 5px;" name="btnClose" id="btnClose">Close Document</button>
									<?php
								}
							}
						?>

					</td>
				</tr>
				<tr>
					<td style="border-style: none;">

						<!-- $remarks = $remarks."\n".$row['date']." : ".$row['sender']." -> ".$row['receiver']." -> Status: ".$row['status']."\n\t\t\t\t\tRemarks: ".$notes; -->
						<table style="margin-top: 20px;">
							<tr>
								<th colspan="5" style="font-weight: 600;">HISTORY</th>
							</tr>
							<tr>
								<th style="font-weight: 600;">DATE</th>
								<th style="font-weight: 600;">SENDER</th>
								<th style="font-weight: 600;">RECEIVER</th>
								<th style="font-weight: 600;">STATUS</th>
								<th style="font-weight: 600;">REMARKS</th>
							</tr>
							<?php
								$query = "SELECT a.id, a.id_track, b.lname as lsender, b.fname as fsender, c.lname as lreceiver, c.fname as freceiver, a.status, a.date, a.remarks FROM dr_logs as a LEFT JOIN dr_users as b ON a.sender = b.id LEFT JOIN dr_users as c ON a.receiver = c.id WHERE id_track = '$tracking'";
								$query_run = mysqli_query($conn, $query);
								$counter = 0;

								if(mysqli_num_rows($query_run) > 0){
									foreach ($query_run as $row){
										if($counter == 0){
											?>
											<tr>
												<td><?php echo $row['date']; ?></td>
												<td><?php echo $row['lsender'].", ".$row['fsender']; ?></td>
												<td><?php echo $row['lreceiver'].", ".$row['freceiver']; ?></td>
												<td><?php echo $row['status']; ?></td>
												<td><p style="white-space: pre-line"><?php echo $row['remarks']; ?></p></td>
											</tr>
											<?php
											$counter = 1;
										}else{
											?>
											<tr>
												<td style="background-color: lightyellow;"><?php echo $row['date']; ?></td>
												<td style="background-color: lightyellow;"><?php echo $row['lsender'].", ".$row['fsender']; ?></td>
												<td style="background-color: lightyellow;"><?php echo $row['lreceiver'].", ".$row['freceiver']; ?></td>
												<td style="background-color: lightyellow;"><?php echo $row['status']; ?></td>
												<td style="background-color: lightyellow;"><p style="white-space: pre-line"><?php echo $row['remarks']; ?></p></td>
											</tr>
											<?php
											$counter = 0;
										}
										
									}
								}
							?>
						</table>
					</td>
				</tr>
				
			</table>
		</div>
		<div class="column" style="width: 30%;">
		</div>
	</div>


</form>

<?php

	$date = date("Y-m-d H:i:s");

	if(isset($_POST['btnBack'])){
		if(isset($_GET['search'])){
			?>
	            <!-- <script> window.location.href='https://192.168.18.14/docroute/search.php/?id=<?php //echo $id; ?>'</script> -->
	       	<?php  
		}else{
			?>
	            <!-- <script> window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php //echo $id; ?>'</script> -->
	       	<?php  
		}
		
	}

	if(isset($_POST['btnAccept'])){

			$tracking = substr($_GET['tracking'], 0, strpos($_GET['tracking'], "*"));
			$sender = substr($_GET['tracking'], strpos($_GET['tracking'], "*")+1, (strlen($_GET['tracking']) - strpos($_GET['tracking'], "*")));
			
			$query = "INSERT INTO dr_logs(id_track, sender, receiver, status, date) VALUES ('$tracking', '$sender', '$id', 'Received', '$date')";
	    	$query_run = mysqli_query($conn, $query);

	    	?>	
		    	<script>
		    		alert("Document Received!");
		    		window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'
		    	</script>
	    	<?php
	}

	if(isset($_POST['btnDecline'])){

			// $tracking = substr($_GET['tracking'], 0, strpos($_GET['tracking'], "*"));

	 	?>
	 	<script>
	 		window.location.href='https://192.168.18.14/docroute/decline.php/?id=<?php echo $id; ?>&tracking=<?php echo $_GET['tracking']; ?>';
	 	</script>
	 	<?php
	}

	if(isset($_POST['btnForward'])){

		$tracking = substr($_GET['tracking'], 0, strpos($_GET['tracking'], "*"));
		?>
	 	<script>
	 		window.location.href='https://192.168.18.14/docroute/forward.php/?id=<?php echo $id; ?>&tracking=<?php echo $tracking; ?>';
	 	</script>
	 	<?php
	}

	if(isset($_POST['btnClose'])){

		$tracking = substr($_GET['tracking'], 0, strpos($_GET['tracking'], "*"));

		// $query = "UPDATE dr_documents SET status='closed' WHERE id_track = '$tracking'";
	   	// $query_run = mysqli_query($conn, $query);

		?>
	 	<script>
	 		// alert("Document Closed!");
	 		window.location.href='https://192.168.18.14/docroute/close.php/?id=<?php echo $id; ?>&tracking=<?php echo $code; ?>';
	 		// window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'
	 	</script>
	 	<?php
	}

?>