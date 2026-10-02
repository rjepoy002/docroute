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

    if(isset($_GET['id'])){
    	$id = $_GET['id'];
    }

	$query = "SELECT COUNT(id) as tracking FROM dr_documents";
	$query_run = mysqli_query($conn, $query);

	if(mysqli_num_rows($query_run) > 0){
		foreach ($query_run as $row) {
			$tracking = $row['tracking'];
		}
	}
	
	for($n = 1; $n < (5 - strlen($tracking+1)); $n++){
		$z = $z."0";
	}

	$z = $z.($tracking+1);
	$tracking = date("Ym-").$z;
    
?>

<form accept="" method="POST">

	<div class="row">
		<div class="column" style="width: 30%;">
		</div>
		<div class="column" style="width: 40%; height: 400px;">
			<table style="margin-top: 10px;">
				<tr>
					<td style="text-align: center; border-style: none;">
						<label style="font-size: 42px; font-weight: 700; color: blue; ">DOCUMENT CREATION</label><br>
						<!-- <label style="font-size: 20px; font-weight: 600; color: gray; ">CREATION</label> -->
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
								<td><input style="width: 100%;" readonly type="text" name="txtDate" id="txtDate" value="<?php echo date("Y-m-d H:i:s"); ?>"></td>
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
								<td><input style="width: 100%;" readonly type="text" name="txtTracking" id="txtTracking" value="<?php echo $tracking; ?>"></td>
								<td><input style="width: 100%;" type="text" name="txtTitle" id="txtTitle" placeholder="Document Title" value="<?php if(isset($_POST['txtTitle'])){echo $_POST['txtTitle'];} ?>"></td>
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
								<td><textarea style="width: 100%; height: 100px; border-style: solid; border-width: 2px;" name="txtDescription" id="txtDescription" placeholder="Document Description"><?php if(isset($_POST['txtDescription'])){echo $_POST['txtDescription'];} ?></textarea></td>
							</tr>
						</table>
					</td>
					
				</tr>
				<tr>
					<td style="border-style: none;">
						<table>
							<tr>
								<th style="text-align: left !important; font-weight: 600; width: 20%;">DEPARTMENT</th>
								<th style="text-align: left !important; font-weight: 600;">FORWARD TO</th>
							</tr>
							<tr>
								<td>
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
								<td>
									<?php
									if(isset($_POST['cboDepartment'])){
										$department = $_POST['cboDepartment'];
									}
									
									?>

									<select style="width: 100%; padding: 10px; border-style: solid; border-width: 2px;" name="cboReceiver" id="cboReceiver">
										<option value="">--SELECT--</option>
										<?php

										$query = "SELECT * FROM dr_users WHERE department = '$department'";
					    				$query_run = mysqli_query($conn, $query);

						    				if(mysqli_num_rows($query_run) > 0){
						    					foreach ($query_run as $row) {
						    						if($row['id'] <> $id){
							    						?>
							    						<option value="<?php echo $row['id']; ?>" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="ACOD") echo "selected";?> ><?php echo $row['name']." (".$row['designation'].")"; ?></option>
							    						<?php
						    						}
						    					}
						    				}

										?>
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="border-style: none;">
						<table>
							<tr>
								<th style="text-align: left !important; font-weight: 600;">REMARKS <font color="green"><i>(Optional)</i></font></th>
							</tr>
							<tr>
								<td>
									<textarea style="width: 100%; height: 100px; border-style: solid; border-width: 2px;" name="txtRemarks" id="txtRemarks" placeholder="Remarks"><?php if(isset($_POST['txtRemarks'])){echo $_POST['txtRemarks'];} ?></textarea>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="border-style: none;">
						<input style="float: right; padding: 10px; margin-left: 10px; margin-top: 5px;" type="submit" name="btnCancel" id="btnCancel" value="Cancel">
						<input style="float: right; padding: 10px; margin-top: 5px;" type="submit" name="btnSubmit" id="btnSubmit" value="Submit" onclick="if(!confirm('Are all the information correct?')) { return false }">
					</td>
				</tr>
			</table>
		</div>
		<div class="column" style="width: 30%;">
		</div>
	</div>


</form>

<?php

	if(isset($_POST['btnCancel'])){
		?>
            <script> window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'</script>
       	<?php  
	}

	if(isset($_POST['btnSubmit'])){

		if(!empty($_POST['txtTitle']) && !empty($_POST['txtDescription']) && !empty($_POST['cboDepartment']) && !empty($_POST['cboReceiver'])){

			$txtTitle = $_POST['txtTitle'];
			$txtDescription = $_POST['txtDescription'];
			$cboDepartment = $_POST['cboDepartment'];
			$cboReceiver = $_POST['cboReceiver'];
			$date = date("Y-m-d H:i:s");
			$remarks = $_POST['txtRemarks'];

			$query = "INSERT INTO dr_documents(id_track, title, description, author, datecreated, status) VALUES ('$tracking', '$txtTitle', '$txtDescription', '$id', '$date', 'open')";
       		$query_run = mysqli_query($conn, $query);

       		$query = "INSERT INTO dr_logs(id_track, sender, receiver, status, date, remarks) VALUES ('$tracking', '$id', '$cboReceiver', 'Pending', '$date','$remarks')";
       		$query_run = mysqli_query($conn, $query);

			?>
            	<script>
            		alert("Document Created")
            		window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'
            	</script>
       		<?php

		}else{
			?>
            	<script>
            		alert("Please fill up the required information.")
            	</script>
       		<?php 
		}
		 
	}

?>