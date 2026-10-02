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
        text-align: center;
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

<form action="" method="POST">
	<div class="row">
		<div class="column" style="width: 43%;">
		</div>
		<div class="column" style="width: 14%; height: 400px;">
			<table style="margin-top: 100px;">
				<tr>
					<td>
						<label style="font-size: 28px; font-weight: 600; color: limegreen; ">PALECO</label><br>
						<label style="font-size: 42px; font-weight: 700; color: blue; ">DOCU ROUTE</label><br>
						<label style="font-size: 20px; font-weight: 600; color: gray; ">FORGOT PASSWORD</label>
					</td>
				</tr>
			</table>
			<table style="margin-top: 20px;">
				<tr>
					<td><input style="width: 100%;" type="text" name="txtLname" id="txtLname" placeholder="Last Name" value="<?php if(isset($_POST['txtLname'])){echo $_POST['txtLname'];} ?>"></td>
				</tr>
				<tr>
					<td><input style="width: 100%;" type="text" name="txtUsername" id="txtUsername" placeholder="Username" value="<?php if(isset($_POST['txtUsername'])){echo $_POST['txtUsername'];} ?>"></td>
				</tr>
				<tr>
					<td colspan="2">
						<button style="cursor: pointer; padding: 15px; padding-left: 20px; padding-right: 20px; float: right; margin-top: 10px" name="btnCancel" id="btnCancel">Cancel</button>
						<button style="cursor: pointer; padding: 15px; padding-left: 20px; padding-right: 20px; float: right; margin-top: 10px; margin-right: 10px;" name="btnSubmit" id="btnSubmit">Submit</button>
					</td>
				</tr>
			</table>
		</div>
	</div>
</form>

<?php
session_start();
date_default_timezone_set('Asia/Taipei');

	$conn = mysqli_connect("localhost","root","","local_pal_db");


	if ($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
	}


	if(isset($_POST['btnCancel'])){
		?>
            <script> window.location.href='https://192.168.18.14/docroute/index.php/'</script>
        <?php  
	}

	if(isset($_POST['btnSubmit'])){
		if(!empty($_POST['txtLname']) && !empty($_POST['txtUsername'])){
			$txtLname = $_POST['txtLname'];
			$txtUsername = $_POST['txtUsername'];

			$query = "SELECT * FROM dr_users WHERE lname = '$txtLname' AND username = '$txtUsername'";
			$query_run = mysqli_query($conn, $query);

			if(mysqli_num_rows($query_run) > 0){

				foreach($query_run as $row){
					$txtPassword = $row['password'];
				}

				?>
					<script>
						alert("Password: <?php echo $txtPassword; ?>")
					</script>
				<?php

			}else{
				?>
					<script>
						alert("Last name and Username not matched or Invalid Input.")
					</script>
				<?php
			}
		}
	}


?>