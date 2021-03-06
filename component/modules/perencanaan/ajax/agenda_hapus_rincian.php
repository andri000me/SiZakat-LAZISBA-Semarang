<?php
/*
 * agenda_hapus_rincian.php
 * ==> AJAX server untuk menghapus rincian agenda
 *
 * Digunakan pada :
 * o AM_SIZ_RA_DTLKGT | Detil Kegiatan
 */

	// Var inits
	$errorDesc		= null;
	$dataRincian	= null;
	
	// POST inputs
	$idRincian		= intval($_POST['idr']);
	$idAgenda		= -1;
	$bulanAgenda	= 0;
	$tahunAgenda	= 0;
	$idKegiatan		= -1;
	
	// Cek input
	if (empty($idRincian)) {
		$errorDesc = "Parameter tidak lengkap.";
	}
	
	if ($errorDesc == null) {
		// Cek rincian agenda
		$queryCekRincian = sprintf(
				"SELECT * FROM ra_rincian_agenda WHERE id_rincian=%d LIMIT 1",
				$idRincian);
		$resultCekRincian = mysqli_query($mysqli, $queryCekRincian);
		
		if ($resultCekRincian != null) {
			$dataRincian	= mysqli_fetch_array($resultCekRincian);
		} else {
			$errorDesc = "Query tidak berhasil: ".mysqli_error($mysqli);
		}
		
		if ($dataRincian != null) {
			$idAgenda = $dataRincian['id_agenda'];
			// Cek item rincian
			$queryCekKegiatan = sprintf(
					"SELECT * FROM ra_agenda AS a, ra_kegiatan AS k ".
					"WHERE a.id_kegiatan=k.id_kegiatan AND a.id_agenda=%d",
					$idAgenda);
			$resultCekKegiatan = mysqli_query($mysqli, $queryCekKegiatan);
				
			if ($resultCekKegiatan != null) {
				if (mysqli_num_rows($resultCekKegiatan) == 0) {
					$errorDesc = "Data kegiatan atau agenda tidak ditemukan.";
				} else {
					$divisiUser		= $_SESSION['siz_divisi'];
					$isAdmin		= ($divisiUser == 99);
			
					$dataAgenda		= mysqli_fetch_array($resultCekKegiatan);
					if (!$isAdmin && ($dataAgenda['divisi']!=$divisiUser)) {
						$errorDesc = "Akses tidak diperbolehkan.";
					}
					$timeAgenda		= strtotime($dataAgenda['tgl_mulai']);
					$bulanAgenda	= date("n", $timeAgenda);
					$tahunAgenda	= date("Y", $timeAgenda);
					$idKegiatan		= $dataAgenda['id_kegiatan'];
				}
			} else {
				$errorDesc = "Query tidak berhasil: ".mysqli_error($mysqli);
			}
		} else {
			$errorDesc = "Rincian tidak ditemukan dalam database.";
		}
	}
	
	// Laksanakan query dan keluarkan output
	if ($errorDesc == null) {
		$queryHapus  = sprintf(
			"DELETE FROM ra_rincian_agenda WHERE id_rincian=%d", $idRincian
		);
		
		$resultSimpan = (IS_DEBUGGING?true:mysqli_query($mysqli, $queryHapus));
		if ($resultSimpan) {
			require_once COMPONENT_PATH."/libraries/querybuilder.php";
			
			$jumlahBaru = update_anggaran_agenda($idAgenda);
			// Hitung jumlah agenda bulan
			$queryHitung = sprintf(
					"SELECT SUM(jumlah_anggaran) FROM ra_agenda ".
					"WHERE id_kegiatan=%d AND MONTH(tgl_mulai)=%d AND YEAR(tgl_mulai)=%d"
					, $idKegiatan, $bulanAgenda, $tahunAgenda);
			$totalAnggaranBulan = querybuilder_getscalar($queryHitung);
			
			echo json_encode(array(
					'status'		=> 'ok',
					'id_a'			=> $idAgenda,
					'old_row_id'	=> $idRincian,
					't_agenda'		=> to_rupiah($jumlahBaru),
					't_bulan'		=> to_rupiah($totalAnggaranBulan),
					'bln'			=> $bulanAgenda
			));
		} else {
			echo json_encode(array(
					'status' => 'error',
					'error' => "Query error: ".mysqli_error($mysqli)
			));
		}
		
	} else {
		echo json_encode(array(
				'status' => 'error',
				'error' => $errorDesc
		));
	}