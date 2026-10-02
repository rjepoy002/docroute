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
			<table style="margin-top: 140px;">
				<tr>
					<td>
						<label style="font-size: 28px; font-weight: 600; color: limegreen; ">PALECO</label><br>
						<label style="font-size: 42px; font-weight: 700; color: blue; ">DOCU ROUTE</label><br>
						<label style="font-size: 20px; font-weight: 600; color: gray; ">L O G I N</label>
					</td>
				</tr>
				<tr>
					<td><br></td>
				</tr>
				<tr>
					<td>
						<input style="width: 100%;" type="text" name="txtUsername" id="txtUsername" placeholder="Username"><br>
					</td>
				</tr>
				<tr>
					<td>
						<input style="width: 100%;" type="password" name="txtPassword" id="txtPassword" placeholder="Password"><br>
					</td>
				</tr>
				<tr>
					<td>
						<a style="float: left; margin-top: 10px;" href="http://192.168.18.14/docroute/register.php">Register</a>
						<button style="background-color: limegreen; color: white; border-style: none !important; padding: 15px; padding-left: 20px; padding-right: 20px; float: right; margin-top: 10px" name="btnLogin" id="btnLogin">LOGIN</button>
					</td>
				</tr>
			</table>
		</div>
		<div class="column" style="width: 43%;">
		</div>
	</div>

</form>

<?php
session_start();
	$conn = mysqli_connect("localhost","root","","local_pal_db");

	if ($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
	}

	if(!empty($_POST['txtUsername']) && !empty($_POST['txtPassword'])){

		$txtUsername = $_POST['txtUsername'];
		$txtPassword = $_POST['txtPassword'];

		if(isset($_POST['btnLogin'])){

			$query = "SELECT * FROM dr_users WHERE username = '$txtUsername' AND password = '$txtPassword'";
			$query_run = mysqli_query($conn, $query);


			if(mysqli_num_rows($query_run) > 0){
				
				foreach ($query_run as $row) {
					$id = $row['id'];
				}

				 ?>
            		<script> window.location.href='https://192.168.18.14/docroute/main.php/?id=<?php echo $id; ?>'</script>
        		<?php
        		$_SESSION['DOCROUTE'] = $txtUsername;
			}else{
				?>
				<script>
					alert("Username or Password Incorrect");
				</script>
				<?php
			}

		}

	}


?>