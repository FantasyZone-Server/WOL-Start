<?php
 $allow_ip = explode("\n", file_get_contents('ips.txt'));
$oBlock_ip = new allowIp($allow_ip);
if( !$oBlock_ip->checkIP() ) {
	exit('您的IP为:'.$oBlock_ip->ip.'，没有使用权限');
} else {
	include("../wol.php");
	$WOL = new WOL("ip.fantasyzone.cc","B4:2E:99:0B:9F:F8","520");
	$status = $WOL->wake_on_wan();
	echo $status;
}
class allowIp {
	function __construct($allow_ip) {
		if (empty($allow_ip)) {
			return false;
		}
		$this->allow_ip = $allow_ip;
		$this->ip = '';
	}
	private function makePregIP($str) {
		if (strstr($str,"-")) {
			$aIP = explode(".",$str);
			foreach ($aIP as $k=>$v) {
				if (!strstr($v,"-")) {
					$preg_limit .= $this->makePregIP($v);
					$preg_limit .= ".";
				} else {
					$aipNum = explode("-",$v);
					for ($i=$aipNum[0];$i<=$aipNum[1];$i++) {
						$preg .=$preg?"|".$i:"[".$i;
					}
					$preg_limit .=strrpos($preg_limit,".",1)==(strlen($preg_limit)-1)?$preg."]":".".$preg."]";
				}
			}
		} else {
			$preg_limit = $str;
		}
		return $preg_limit;
	}
	private function getAllBlockIP() {
		if ($this->allow_ip) {
			$i = 1;
			foreach ($this->allow_ip as $k=>$v) {
				$ipaddres = $this->makePregIP($v);
				$ip = str_ireplace(".","\.",$ipaddres);
				$ip = str_replace("*","[0-9]{1,3}",$ip);
				$ipaddres = "/".$ip."/";
				$ip_list[] = $ipaddres;
				$i++;
			}
		}
		return $ip_list;
	}
	public function checkIP() {
		$iptable = $this->getAllBlockIP();
		$IsJoined = false;
		//取得用户ip
		$Ip = $this->get_client_ip();
		$Ip = trim($Ip);
		//在白名单中
		if ($iptable) {
			foreach($iptable as $value) {
				if (preg_match("{$value}",$Ip)) {
					$IsJoined = true;
					break;
				}
			}
		}
		//不在白名单中
		if( !$IsJoined ) {
			return false;
		}
		return true;
	}
	private function get_client_ip() {
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
		            $ip = getenv("HTTP_CLIENT_IP"); else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
		            $ip = getenv("HTTP_X_FORWARDED_FOR"); else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
		            $ip = getenv("REMOTE_ADDR"); else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
		            $ip = $_SERVER['REMOTE_ADDR']; else
		            $ip = "unknown";
		$this->ip = $ip;
		return($ip);
	}
}
?>