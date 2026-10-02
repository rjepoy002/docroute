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
						<label style="font-size: 20px; font-weight: 600; color: gray; ">REGISRATION</label>
					</td>
				</tr>
			</table>
			<table style="margin-top: 20px;">
				<tr>
					<td><input style="width: 100%;" type="text" name="txtName" id="txtName" placeholder="Fullname" value="<?php if(isset($_POST['txtName'])){echo $_POST['txtName'];} ?>"></td>
				</tr>
				<tr>
					<td><input style="width: 100%;" type="text" name="txtDesignation" id="txtDesignation" placeholder="Designation" value="<?php if(isset($_POST['txtDesignation'])){echo $_POST['txtDesignation'];} ?>"></td>
				</tr>
				<tr>
					<td>
						<select style="width: 100%; border-radius: 5px; padding: 10px; border: 2px solid;" name="cboDepartment" id="cboDepartment">
							<option value="ACOD" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="ACOD") echo "selected";?>>ACOD</option>
							<option value="ANOD" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="ANOD") echo "selected";?>>ANOD</option>
							<option value="ASOD" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="ASOD") echo "selected";?>>ASOD</option>
							<option value="CPD" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="CPD") echo "selected";?>>CPD</option>
							<option value="FSD" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="FSD") echo "selected";?>>FSD</option>
							<option value="ISD" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="ISD") echo "selected";?>>ISD</option>
							<option value="IAD" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="IAD") echo "selected";?>>IAD</option>
							<option value="OGM" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="OGM") echo "selected";?>>OGM</option>
							<option value="TSD" <?php if (isset($_POST['cboDepartment']) && $_POST['cboDepartment']=="TSD") echo "selected";?>>TSD</option>
						</select>
					</td>
				</tr>
				<tr>
					<td><input style="width: 100%;" type="text" name="txtUsername" id="txtUsername" placeholder="Username" value="<?php if(isset($_POST['txtUsername'])){echo $_POST['txtUsername'];} ?>"></td>
				</tr>
				<tr>
					<td><input style="width: 100%;" type="password" name="txtPassword" id="txtPassword" placeholder="Password" value="<?php if(isset($_POST['txtPassword'])){echo $_POST['txtPassword'];} ?>"></td>
				</tr>
				<tr>
					<td><input style="width: 100%;" type="password" name="txtConfirm" id="txtConfirm" placeholder="Confirm Password" value="<?php if(isset($_POST['txtConfirm'])){echo $_POST['txtConfirm'];} ?>"></td>
				</tr>

				<tr>
					<td colspan="2">
						<button style="background-color: red; color: white; border-style: none !important; padding: 15px; padding-left: 20px; padding-right: 20px; float: right; margin-top: 10px" name="btnCancel" id="btnCancel">Cancel</button>
						<button style="background-color: limegreen; color: white; border-style: none !important; padding: 15px; padding-left: 20px; padding-right: 20px; float: right; margin-top: 10px; margin-right: 10px;" name="btnRegister" id="btnRegister">Register</button>

					</td>
				</tr>
			</table>
		</div>
		<div class="column" style="width: 43%;">
		</div>
</form>
<?php
session_start();
	$conn = mysqli_connect("localhost","root","","local_pal_db");

	if ($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
	}


	if(isset($_POST['btnCancel'])){
		 ?>
            <script> window.location.href='https://192.168.18.14/docroute/index.php/'</script>
        <?php  
	}

	if(isset($_POST['btnRegister'])){
		if(!empty($_POST['txtName']) && !empty($_POST['txtDesignation']) && !empty($_POST['cboDepartment']) && !empty($_POST['txtUsername']) && !empty($_POST['txtPassword']) && !empty($_POST['txtConfirm'])){

			$txtName = $_POST['txtName'];
			$txtDesignation = $_POST['txtDesignation'];
			$cboDepartment = $_POST['cboDepartment'];
			$txtUsername = $_POST['txtUsername'];
			$txtPassword = $_POST['txtPassword'];
			$txtConfirm = $_POST['txtConfirm'];
			$date = date("Y-m-d h:i:s");

			if($txtPassword == $txtConfirm){

				$query = "INSERT INTO dr_users(name, designation, department, username, password, datecreated) VALUE ('$txtName', '$txtDesignation', '$cboDepartment', '$txtUsername', '$txtPassword', '$date')";
				$query_run = mysqli_query($conn, $query);

				?>
				<script>
					alert("Successfully Registered!");
					window.location.href='https://192.168.18.14/docroute/index.php/'
				</script>
				<?php

			}else{
				?>
				<script>alert("Password and confirm password does not match.");</script>
				<?php
			}

		}
	}

?>

