<?php
	//session_start();
	include"component/config/koneksi.php";
	require_once "component/libraries/injection.php";
		
		$query = mysqli_query($mysqli, "SELECT * FROM user WHERE id_user = '$_SESSION[iduser]'");
		$d = mysqli_fetch_array($query);
		
		$nama		= $d['nama'];
		$alamat 	= $d['alamat'];
		$hp			= $d['hp'];
		$pj 		= $d['pj'];
		$id 		= $d['id_user'];
		$user		= $d['username'];
		$pass		= $d['password'];
	
?>
<div class="col-12">
      <div class="widget-box">
        <div class="box gradient">
          <div class="widget-title">
            <h5>
            <i class="icon-book"></i><span>Ubah Akun Pribadi</span>
            </h5>
          </div>
          <div class="widget-content nopadding">
            <form class="form-horizontal row-fluid" action="component/server/edit_ukm_pibadi.php" Method="POST">
			  <div class="form-group">
					<?php
						if(ISSET($_SESSION['success']) || ISSET($_SESSION['error'])){
							if(ISSET($_SESSION['success'])){
								echo '<div class="alert alert-success" style="margin:10px;">'.$_SESSION['success'].'</div>';
								unset($_SESSION['success']);
							}
							
							if(ISSET($_SESSION['error'])){
								echo '<div class="alert alert-error" style="margin:10px;">'.$_SESSION['error'].'</div>';
								unset($_SESSION['error']);
							}
						}
					?>
				</div>
              <div class="form-row control-group row-fluid form-group">
                <label class="control-label span3" for="normal-field">Nama UKM</label>
                <div class="controls span12">
                  <input type="text"  style='width:80%' name="nama" required='required' value='<?php echo (ISSET($nama))?$nama:"";?>'/>
                </div>
              </div>
			  <div class="form-row control-group row-fluid form-group">
                <label class="control-label span3" for="normal-field">Alamat</label>
                <div class="controls span12">
                  <input type="text"  style='width:80%' name="alamat" required='required' value='<?php echo (ISSET($alamat))?$alamat:"";?>'/>
                </div>
              </div>
			  <div class="form-row control-group row-fluid form-group">
                <label class="control-label span3" for="normal-field">No Telp</label>
                <div class="controls span12">
                  <input type="text"  style='width:80%' name="telp" required='required' value='<?php echo (ISSET($hp))?$hp:"";?>'/>
                </div>
              </div>
			  <div class="form-row control-group row-fluid form-group">
                <label class="control-label span3" for="normal-field">Penanggung Jawab</label>
                <div class="controls span12">
                  <input type="text"  style='width:80%' name="pj" required='required' value='<?php echo (ISSET($pj))?$pj:"";?>'/>
                </div>
              </div>
			    <div class="form-row control-group row-fluid form-group">
                <label class="control-label span3" for="normal-field">Username</label>
                <div class="controls span12">
                  <input type="text"  style='width:80%' name="username" required='required' value='<?php echo (ISSET($user))?$user:"";?>'/>
                </div>
              </div>
			    <div class="form-row control-group row-fluid form-group">
                <label class="control-label span3" for="normal-field">Password</label>
                <div class="controls span12">
                  <input type="password"  style='width:80%' name="password" />
                </div>
              </div>
			  
			 <div class="form-actions">
                  <button type="submit" name='save' class="btn btn-primary btn-small">save</button>
			</div>
              </div>
            </form>
          </div>
          </div>
          </div>