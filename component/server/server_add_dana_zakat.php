<?php
	/*save_mustahik.php*/
	session_start();
	include "../config/koneksi.php";
	include "../libraries/injection.php";
	if(ISSET($_POST['save'])){
		$tgl 			= clear_injection($_POST['tgl']);
		//$no_transaksi 	= clear_injection($_POST['no_transaksi']);
		$jenis_transaksi = clear_injection($_POST['jenis_transaksi']);
		//$rekening 		= clear_injection($_POST['rekening']);
		//$mustahik 		= clear_injection($_POST['mustahik']);
		$amilin 		= clear_injection($_POST['amilin']);
		$jumlah 		= clear_injection($_POST['jumlah']);
		$ket 		= clear_injection($_POST['keterangan']);
		$pers 		= clear_injection($_POST['sumber']);

		//-------------------------------------------------------------------------------------------------------------
		$q = mysql_query("SELECT * FROM persamaan_akun WHERE id_persamaan = '$pers' ");
		$fetch1 = mysql_fetch_array($q);
		$id_penerimaan = $fetch1['id_penerimaan'];
		
		$q = mysql_query("SELECT * FROM saldo_awal WHERE id_akun = '$id_penerimaan' ");
		$fetch1 = mysql_fetch_array($q);
		$saldo_awal = $fetch1['saldo'];
		
		$q2 = mysql_query("SELECT SUM(jumlah) as jum FROM penerimaan WHERE id_akun = '$id_penerimaan' ");
		$fetch2 = mysql_fetch_array($q2);
		$jumlah1 = $fetch2['jum'];

		$q3 = mysql_query("SELECT SUM(y.jumlah) as jum2 FROM penyaluran y, persamaan_akun s WHERE s.id_penerimaan = '$id_penerimaan' AND y.id_akun = s.id_penyaluran");
		$fetch3 = mysql_fetch_array($q3);
		$jumlah2 = $fetch3['jum2'];
		
		$total = $saldo_awal + $jumlah1 - $jumlah2;
		
				
		//-------------------------------------------------------------------------------------------------------------
		if($jumlah > $total){
			$_SESSION['error'] = "Saldo anda tidak mencukupi untuk melakukan penyaluran, Total saldo = ".$total."";
			//echo mysql_error();
			echo "<meta http-equiv=\"refresh\" content=\"0; url=../../main.php?s=form_dana_zakat_s\">";
		}else{
		
			$sql = mysql_query("
				INSERT INTO penyaluran (id_penyaluran,tanggal,id_akun,jumlah,keterangan,id_teller,id_persamaan) VALUES 
				('','$tgl','$jenis_transaksi','$jumlah','$ket','$amilin','$pers')
			");
			
			if($sql){
				$_SESSION['success'] = "Data Transaksi Penyaluran Berhasil Ditambah"; 
				echo "<meta http-equiv=\"refresh\" content=\"0; url=../../main.php?s=form_dana_zakat_s\">";
			}else{
				$_SESSION['error'] = "Terdapat Kesalahan Dalam Pemrosesan : ".mysql_error();
				echo "<meta http-equiv=\"refresh\" content=\"0; url=../../main.php?s=form_dana_zakat_s\">";
			}
		
		}
		
		///--------------------------------------------------------------------------------------------------------------

	}else{
		$_SESSION['error'] = "Proses Dibatalkan";
			echo "<meta http-equiv=\"refresh\" content=\"0; url=../../main.php?s=form_dana_zakat_s\">";
	}
	
?>