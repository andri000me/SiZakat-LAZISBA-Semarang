<?php 
	include "../config/koneksi.php";
	$sql = mysqli_query($mysqli, "TRUNCATE TABLE persamaan_akun");
	$q1 = mysqli_query($mysqli, "SELECT * FROM akun WHERE jenis = '1' AND idParent != '0'  AND idakun NOT IN (SELECT idParent FROM akun)");
	
	while($p1 = mysqli_fetch_array($q1)){
		$id = $p1['idakun'];
		
		if(ISSET($_POST['akun_'.$id])){
			$pers_akun = $_POST['akun_'.$id];
			
			if(count($pers_akun > 0)){
				for($i=0;$i<count($pers_akun);$i++){
					$sql = mysqli_query($mysqli, "INSERT INTO persamaan_akun (id_penerimaan,id_penyaluran) VALUES ('$p1[kode]','$pers_akun[$i]')");
				}
			}
		}
		
		//echo "<meta http-equiv=\"refresh\" content=\"0; url=../../main.php?s=persamaan_akun\">";
	}
	
	$q1 = mysqli_query($mysqli, "SELECT * FROM akun WHERE jenis = '3' AND kode LIKE '3.1%' AND idParent != '0'  AND idakun NOT IN (SELECT idParent FROM akun)");
	
	while($p1 = mysqli_fetch_array($q1)){
		$id = $p1['idakun'];
		
		if(ISSET($_POST['akun_'.$id])){
			$pers_akun = $_POST['akun_'.$id];
			
			if(count($pers_akun > 0)){
				for($i=0;$i<count($pers_akun);$i++){
					$sql = mysqli_query($mysqli, "INSERT INTO persamaan_akun (id_penerimaan,id_penyaluran) VALUES ('$p1[kode]','$pers_akun[$i]')");
				}
			}
		}
		
		
	}
	
	echo "<meta http-equiv=\"refresh\" content=\"0; url=../../main.php?s=persamaan_akun\">";
?>