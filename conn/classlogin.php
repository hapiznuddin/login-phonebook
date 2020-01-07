<?php
//conn/ClassLogin.php

Class Login{
	var $db;

	public function __construct(){
		global $db;
		#menghubungkan variabel database $db ke class Login
		$this->db = $db;
	}

	public function cek_login(){
		/*kondisi user dinyatakan login adalah : 
		1. Memiliki $_COOKIE['adv_token']; (yang dibuat di method true_login() tadi)
		2. $_COOKIE['adv_token'] terdaftar di tabel tb_admin_log, dan dalam keadaan masih belum expired
		3. IP dan User Agent sesuai dengan token yang terdaftar
		*/
		if(isset($_COOKIE['adv_token'])){
			$token = $_COOKIE['adv_token'];
			$now = date("Y-m-d H:i:s");
			$cek = $this->db->query("SELECT * FROM tb_admin_log WHERE token = ".$this->db->quote($token)." AND expired > ".$this->db->quote($now));
			if($cek){
				#kalau token di cookie tersebut ada, lakukan pengecekan IP dan User Agent
				$row = $cek->fetch();
				if($row['ip'] == $_SERVER['REMOTE_ADDR'] || $row['useragent'] == $_SERVER['HTTP_USER_AGENT']){
					//kondisi bisa disesuaikan utk kebutuhan dengan ATAU / DAN
					//kondisi DAN boleh dipakai, tapi terlalu strict.. Lebih baik pakai ATAU saja.
					$username = $row['username'];

					//kembalikan data user yg sedang login,, siapa tahu nanti ingin diolah
					$get_admin = $this->db->query("SELECT * FROM tb_admin WHERE user = ".$this->db->quote($username));
					$rget = $get_admin->fetch();

					return array(
						"username" => $rget['user'],
						"name" => $rget['name'],
						"email" => $rget['email'],
						"priviledge" => $rget['priviledge']
					);

				}

			}
		}
		return false;
	}


	public function salah_login_action($username){
		//logic : dipanggil saat user salah memasukkan username/password.
		//username, tgl, ip, dan user agent dicatat dengan FLAG=0.

		$tgl = date("Y-m-d H:i:s");
		$ip = $_SERVER['REMOTE_ADDR'];
		$useragent = $_SERVER['HTTP_USER_AGENT'];

		//memasukkan data ke tb_admin_log dengan flag STAT = 0.
		$save = $this->db->prepare("INSERT INTO tb_admin_log VALUES (NULL, ?, '', '', ?, ?, ?, 0)");
		$save->execute(array(
			$tgl, $username, $ip, $useragent
		));
		return true;
	}


	public function cek_salah_login($limit=5){
		#method ini dipanggil sekali di login-proses paling atas. 
		#$limit bisa disesuaikan sesuai kebutuhan kita. 
		//cek apakah di tabel tb_admin_log ada 5 IP yang sama dalam keadaan salah login (STAT = 0)

		$ip = $_SERVER['REMOTE_ADDR'];
		$cek = $this->db->prepare("SELECT * FROM tb_admin_log WHERE stat = 0 AND ip = ?");

		$cek->execute(array($ip));
		if($cek->rowCount() >= $limit)
			return false;
		return true;
	}


	public function true_login($username, $expired){
		#method yang dipanggil ketika username dan password sudah tepat dimasukkan

		$tgl = date("Y-m-d H:i:s");
		if($expired <> 0){
			#kalau remember me dicentang, tanggal expirenya adalah 1 tahun dari sekarang.
			$expireddb = date("Y-m-d H:i:s",strtotime($expired));
		}
		else{
			#kalau remember me tidak dicentang, secara default user dapat login selama 6 jam saja.
			$expireddb = date("Y-m-d H:i:s",strtotime("+6 hours"));
		}

		$ip = $_SERVER['REMOTE_ADDR'];
		$useragent = $_SERVER['HTTP_USER_AGENT'];

		$token = sha1($ip.$expireddb."string_random_apasaja".microtime()); //intinya membuat karakter acak saja
		//$token ini penting,, nantinya akan disimpan sebagai COOKIE

		//apabila ada kesalahan login sebelumnya dengan IP & user agent yang sama sebelumnya harus ditandai dulu 
		//penandaan dilakukan dengan mengubah FLAG dari 0 menjadi 9, sehingga di pengecekan selanjutnya data ini tidak akan dianggap
		$upd = $this->db->query("UPDATE tb_admin_log SET stat = 9 WHERE token = '' AND ip = ".$this->db->quote($ip)." AND useragent = ".$this->db->quote($useragent));


		//memasukkan data lengkap ke tb_admin_log dengan flag STAT = 1.
		$save = $this->db->prepare("INSERT INTO tb_admin_log VALUES (NULL, ?, ?, ?, ?, ?, ?, 1)");
		$save->execute(array(
			$tgl, $expireddb, $token, $username, $ip, $useragent
		));


		//simpan token ke cookie
		$expr = 0;
		if($expired <> 0){
			$expr = intval(strtotime($expired));
		}
		setcookie("adv_token", $token, $expr, "/");
		#kalau remember me tidak dicentang, cookie akan otomatis bertindak sebagai session
		#kalau dicentang, cookie akan terus disimpan

		return true;
    }

	public function logout(){
		#dipanggil saat user logout dari sistem.

		if(isset($_COOKIE['adv_token'])){
			$token = $_COOKIE['adv_token'];

			//cara menghapus cookie adalah dengan mengubah tanggal expirednya menjadi sekarang
			$now = date("Y-m-d H:i:s");
			unset($_COOKIE['adv_token']);
			setcookie("adv_token",null,$now,"/");
			
			#jangan lupa tanggal expired di database diupdate juga, supaya session token yang sudah logout tidak dihijack
			$this->db->query("UPDATE tb_admin_log SET expired = ".$this->db->quote($now)." WHERE token = ".$this->db->quote($token));
		}

		return true;
	}

	public function login_redir(){
		//method yang akan selalu dipanggil di seluruh halaman non index dan non login, 
		//untuk mengecek apabila user tidak memiliki akses langsung diredirect ke halaman login
		if(!$this->cek_login())
			header("location:index.php");
	}

}