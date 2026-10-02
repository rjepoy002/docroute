<head>
</head>
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
session_start();
date_default_timezone_set('Asia/Taipei');

	//$conn = mysqli_connect("localhost","palecxzp","P4LECO@1974","palecxzp_pal_db");
	$conn = mysqli_connect("localhost","root","","local_pal_db");
	$tracking = "";
	$z = "";

	if ($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
    }

	if(!isset($_SESSION['DOCROUTE'])){
	    ?>
	    	<script> window.location.href='http://192.168.18.14/docroute/index.php/'</script>
	    <?php
	 }

    if(isset($_GET['id']) ){
    	$id = $_GET['id'];
    }

    if(isset($_GET['tracking']) ){
    	$tracking = $_GET['tracking'];
    }

	$query = "SELECT * FROM dr_documents as a LEFT JOIN dr_users as b ON a.author = b.id WHERE id_track = '$tracking'";
	$query_run = mysqli_query($conn, $query);

	if(mysqli_num_rows($query_run) > 0){
		foreach ($query_run as $row) {
			$tracking = $row['id_track'];
			$title = $row['title'];
			$description = $row['description'];
			$author = $row['name'];
			$datecreated =  $row['datecreated'];
			$designation = $row['designation'];
		}
	}

	$query = "SELECT a.id, a.id_track, b.name as sender, c.name as receiver, a.status, a.date, a.remarks FROM dr_logs as a LEFT JOIN dr_users as b ON a.sender = b.id LEFT JOIN dr_users as c ON a.receiver = c.id WHERE id_track = '$tracking'";
	$query_run = mysqli_query($conn, $query);
	$remarks = "";

	if(mysqli_num_rows($query_run) > 0){
		foreach ($query_run as $row){
			if(empty($row['remarks'])){
				$notes = "None";
			}else{
				$notes = $row['remarks'];
			}
			$remarks = $remarks."\n".$row['date']." : ".$row['sender']." -> ".$row['receiver']." -> Status: ".$row['status']."\n\t\t\t\t\tRemarks: ".$notes;
		}
	}
	
?>

<form accept="" method="POST">			    			
	<div class="row" style="margin-top: 50px;">
		<div class="column" style="width: 23%;">
		</div>
		<div class="column" style="width: 25%; height: 500px; border-style: solid; border-color: ghostwhite; background-color: ghostwhite; border-radius: 10px; margin-right: 10px;">
			<table style="margin-top: 10px;">
				<tr>
					<td style="text-align: center;">
						<label style="font-size: 28px; font-weight: 600; color: green; ">FORWARD TO</label>
					</td>
				</tr>
			</table>
			<table style="margin-top: 20px;">
				<tr>
					<td>
						Department:
						<?php
							$query = "SELECT * FROM dr_users GROUP BY department";
		    				$query_run = mysqli_query($conn, $query);

		    				if(mysqli_num_rows($query_run) > 0){
		    					?>
		    						<select style="width: 100%; padding: 10px; border-style: solid; border-width: 2px;" name="cboDepartment" id="cboDepartment" onchange="this.form.submit()">
		    							<option value="">--SELECT--</option>
		    					<?php
					    				foreach ($query_run as $row) {
					    					?>
					    					<option value="<?php echo $row['department']; ?>" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']==$row['department']) echo "selected";?>><?php echo $row['department']; ?></option>
					    					<?php
					    				}
			    				?>
			    					</select>
			    				<?php
			    			}

						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php

						if(isset($_POST['cboDepartment'])){
							$department = $_POST['cboDepartment'];
						}
						
						?>
						Name:
						<select style="width: 100%; padding: 10px; border-style: solid; border-width: 2px;" name="cboReceiver" id="cboReceiver" onchange="this.form.submit()">
							<option value="">--SELECT--</option>
							<?php

								$query = "SELECT * FROM dr_users WHERE department = '$department'";
			    				$query_run = mysqli_query($conn, $query);

				    			if(mysqli_num_rows($query_run) > 0){
				    				foreach ($query_run as $row) {
				    					if($row['id'] <> $id){
				    						?>
					    						<option value="<?php echo $row['id']; ?>" <?php if (isset($_POST['cboReceiver']) && $_POST['cboReceiver']==$row['id']) echo "selected";?> ><?php echo $row['name']." (".$row['designation'].")"; ?>
					    						</option>
					    					<?php
				    					}
					    				
				    				}
				    			}

							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						Remarks: <font color="red"><i>(Required)</i></font>
						<textarea style="width: 100%; height: 100px; border-style: solid; border-width: 2px;" name="txtRemarks_f" id="txtRemarks_f" placeholder="Remarks"><?php if(isset($_POST['txtRemarks_f'])) echo $_POST['txtRemarks_f']; ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<button style="float: right; padding: 10px; padding-left: 20px; padding-right: 20px; margin-left: 10px; margin-top: 10px;" name="btnBack" id="btnBack" >Back</button>
						<button style="float: right; padding: 10px; padding-left: 20px; padding-right: 20px; margin-left: 10px; margin-top: 10px;" name="btnForward" id="btnForward" >Forward</button>
					</td>
				</tr>
			</table>
		</div>

		<div class="column" style="width: 30%; height: 550px; border-style: solid; border-color: ghostwhite; background-color: ghostwhite; border-radius: 10px;">
			<table style="margin-top: 10px;">
				<tr>
					<td style="text-align: center;">
						<label style="font-size: 28px; font-weight: 600; color: blue;">DOCUMENT DETAILS</label>
					</td>
				</tr>
			</table>
			<table style="margin-top: 20px;">
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
								<th style="text-align: left !important; font-weight: 600; width: 25%;">TRACKING ID</th>
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
								<td><?php echo $description; ?></td>
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
			</table>
		</div>

		<div class="column" style="width: 10%;">
		</div>
	</div>


</form>

<?php

	if(isset($_POST['btnBack'])){
		?>
            <script> window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'</script>
       	<?php  
	}

	if(isset($_POST['btnForward'])){
		if(!empty($_POST['txtRemarks_f']) && $_POST['cboReceiver'] <> ""){

			$remarks = $_POST['txtRemarks_f'];
			$date = date("Y-m-d H:i:s");
			$receiver = $_POST['cboReceiver'];

			// echo "Tracking: ".$tracking."<br>";
			// echo "Sender: ".$id."<br>";
			// echo "Receiver: ".$receiver."<br>";
			// echo "Status: Forwared"."<br>";
			// echo "Date: ".$date."<br>";
			// echo "Remarks: ".$remarks;

			$query = "INSERT INTO dr_logs(id_track, sender, receiver, status, date, remarks) VALUES ('$tracking', '$id', '$receiver', 'Pending', '$date', '$remarks')";
	    	$query_run = mysqli_query($conn, $query);

			?>
			<script>
				alert("Forwarded");
				window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'
			</script>
			<?php
		}
	}
	
?>