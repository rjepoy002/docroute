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
        background-color: blue;
        color: White;
        padding: 2px;
        border: 1px solid white;
    }
    td{
        text-align: left;
       	border: 1px solid white;
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
// header("Cache-Control: no cache");
// session_cache_limiter("private_no_expire");
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

	if(isset($_GET['tracking'])){
		$code = $_GET['tracking'];

    	$tracking = substr($code, 0, strpos($code, "*"));
	 	$receiver = substr($code, strpos($code, "*")+1, (strlen($code) - strpos($code, "*")));

    	// echo "tracking: ".$tracking."<br>";
    	// echo "receiver: ".$receiver."<br>";

	}

	if(isset($_GET['id'])){
		$id = $_GET['id'];

		$query = "SELECT * FROM dr_users WHERE id = '$id'";
		$query_run = mysqli_query($conn, $query);

			if(mysqli_num_rows($query_run) > 0){

				foreach ($query_run as $row) {
					$isgroup = $row['isgroup'];
				}

			}

	}

	if(isset($_POST['btnCancel'])){

		if($isgroup == 1){
			?>
		        <script> window.location.href='https://192.168.18.14/docroute/main2_r.php/?id=<?php echo $id ?>&sender=<?php echo $receiver; ?>'</script>
		        <!-- https://192.168.18.14/docroute/view.php/?id=1&tracking=202402-0340-12*12&type=receivables -->
		    <?php  
		}else{
			?>
		        <script> window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id ?>'</script>
		    <?php  
		}
		
	}

	if(isset($_POST['btnClose'])){
			
		$date = date("Y-m-d H:i:s");
		$txtReason = addslashes($_POST['txtReason']);

		$query = "INSERT INTO dr_logs(id_track, sender, receiver, status, date, remarks) VALUES ('$tracking', '$id', '$receiver', 'Closed', '$date', '$txtReason')";
    	$query_run = mysqli_query($conn, $query);

    	$query = "UPDATE dr_documents SET status='closed' WHERE id_track = '$tracking'";
    	$query_run = mysqli_query($conn, $query);

		?>
		<script>
			alert("Tracking #: <?php echo $tracking; ?> Closed.");
			window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id ?>'
		</script>
		<?php

	}

?>

<form action="" method="POST">
	<div class="row">
		<div class="column" style="width: 30%;">
		</div>
		<div class="column" style="width: 40%; height: 400px;">

			<table style="margin-top: 80px;">
				<tr>
					<td>
						Tracking#: <?php echo $tracking; ?><br><br>
						Document Closing Remarks <font color="green"><i>(Optional)</i></font>:
					</td>
				</tr>
				<tr>
					<td><input style="margin-bottom: 10px; width: 100%;" type="text" name="txtReason" id="txtReason" placeholder="Remarks"></td>
				</tr>
				<tr>
					<td>
						<button style="padding: 10px; float: right;" name="btnCancel" id="btnCancel">Cancel</button>
						<button style="padding: 10px; float: right; margin-right: 10px;" name="btnClose" id="btnClose">Close Document</button>
					</td>
				</tr>
			</table>

		</div>
		<div class="column" style="width: 30%;">
		</div>
	</div>
	

</form>
