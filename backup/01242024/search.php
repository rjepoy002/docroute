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

    select{
    	padding: 10px;
    	border-radius: 5px;
    	border-style: solid 1 ghostwhite;
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

	$query = "SELECT * FROM dr_documents as a LEFT JOIN dr_users as b ON a.author = b.id WHERE b.id = '$id'";
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
?>

<form accept="" method="POST">

	<div class="row">
		<div class="column" style="width: 20%; margin-left: 10%;">
			<table style="margin-top: 20px;">
				<tr>
					<td>
						<table>
							<tr>
								<td style="border-style: none;">
									<a style="color: gray; font-size: 18px;" href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'>Back to Dashboard</a>
								</td>
							</tr>
							
							<tr>
								<!-- <button style="padding: 10px; float: right; padding-left: 15px; padding-right: 15px; margin-left: 10px;" name="btnBack" id="btnBack">Back</button> -->
								
								<th style="text-align: left !important; font-weight: 600; width: 10%;">SEARCH</th>
							</tr>
							<tr>
								<td style="border-style: none;">
									Category:
									<select style="width: 100%; margin-top: 10px;" name="cboCategory" id="cboCategory">
										<option value="id_track" <?php if (isset($_POST['cboCategory']) && $_POST['cboCategory']=="id_track") echo "selected";?>>Tracking ID</option>
										<option value="author" <?php if (isset($_POST['cboCategory']) && $_POST['cboCategory']=="author") echo "selected";?>>Author</option>
										<option value="title" <?php if (isset($_POST['cboCategory']) && $_POST['cboCategory']=="title") echo "selected";?>>Title</option>
									</select>
								</td>
							</tr>
							<tr>
								<td style="border-style: none;">
									<input style="width: 100%;" type="text" name="txtSearch" id="txtSearch" placeholder="<?php if(isset($_POST['cboCategory']) && $_POST['cboCategory'] == 'id_track') echo "YYYYMM-####"; ?>"  value="<?php if(isset($_POST['txtSearch'])){echo $_POST['txtSearch'];} ?>"></td>
								</td>
							</tr>
							<tr>
								<td style="border-style: none;">
									<button style="padding: 10px; float: right;" name="btnSearch" id="btnSearch">Search</button>
								</td>
							</tr>
							<tr>
								<td style="border-style: none;">
									<p id="records"></p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div class="column" style="width: 60%; height: 400px;">
			<table style="margin-top: 20px;">
				<tr>
					<td style="border-style: none;">
						<table>
							<tr>
								<th style="font-weight: 600; width: 15%;">DATE</th>
								<th style="font-weight: 600; width: 10%;">TRACKING#</th>
								<th style="font-weight: 600;">TITLE</th>
								<th style="font-weight: 600; width: 16%;">AUTHOR</th>
								<th style="font-weight: 600; width: 5%;">STATUS</th>
								<th style="font-weight: 600; width: 5%;">ACTION</th>
							</tr>
							<?php 

								if(isset($_POST['btnSearch'])){

									$txtSearch = $_POST['txtSearch'];

									switch ($_POST['cboCategory']) {
										case 'id_track':
											$query = "SELECT a.datecreated as date, a.id_track, a.title, b.name, a.status  FROM dr_documents as a LEFT JOIN dr_users as b ON a.author = b.id WHERE a.id_track like '%$txtSearch%'";
											break;
										case 'author':
											$query = "SELECT a.datecreated as date, a.id_track, a.title, b.name, a.status  FROM dr_documents as a LEFT JOIN dr_users as b ON a.author = b.id WHERE name like '%$txtSearch%'";
											break;
										case 'title':
											$query = "SELECT a.datecreated as date, a.id_track, a.title, b.name, a.status  FROM dr_documents as a LEFT JOIN dr_users as b ON a.author = b.id WHERE title like '%$txtSearch%'";
											break;
									}

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
													<td><?php echo $row['name']; ?></td>
													<td><?php echo $row['status']; ?></td>
													<td>
														<center>
															<button style="padding: 5px;" name="btnView[]" id="btnView[]" value="<?php echo $row['id_track']; ?>">View</button>
														</center>
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
													<td style="background-color: lightyellow;"><?php echo $row['name']; ?></td>
													<td style="background-color: lightyellow;"><?php echo $row['status']; ?></td>
													<td style="background-color: lightyellow;">
														<center>
															<button style="padding: 5px;" name="btnView[]" id="btnView[]" value="<?php echo $row['id_track']; ?>">View</button>
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
												<label style="font-size: 21px; padding: 20px;"><center>No documents found</center></label>
											</td>
										</tr>
										<?php
									}

								}else{
									?>
									<tr>
										<td colspan="6">
											<label style="font-size: 21px; padding: 20px;"><center>No documents found</center></label>
										</td>
									</tr>
									<?php
								}
								
							?>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</div>


</form>

<?php

	$date = date("Y-m-d H:i:s");

	// if(isset($_POST['btnBack'])){
	// 	?>
    <!-- //         <script> window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'</script> -->
        	<?php  
	// }

	if(isset($_POST['btnView'])){
		foreach($_POST['btnView'] as $btnView){
			echo $btnView;
			?>
	 		<script>
	 			window.location.href='https://192.168.18.14/docroute/view.php/?id=<?php echo $id; ?>&tracking=<?php echo $btnView; ?>&search=1';
	 		</script>
	 		<?php
		}
	}

?>