<?
defined('IN_DCR') or exit('No permission.'); 

/**
* 发送邮件类 来源于网上 非本人原创
* @version 1.0.6
* @copyright 2006-2010
* @package class
*/
class smtp
{
	/* Public Variables */
	var $smtp_port;
	var $time_out;
	var $host_name;
	var $log_file;
	var $relay_host;
	var $debug;
	var $auth;
	var $user;
	var $pass;

	/* Private Variables */
	var $sock;

	/* Constractor */
	function smtp( $relay_host = "", $smtp_port = 25, $auth = false, $user, $pass )
	{
		$this->debug = false;
		$this->smtp_port = $smtp_port;
		$this->relay_host = $relay_host;
		$this->time_out = 30; //is used in fsockopen()
		#
		$this->auth = $auth;//auth
		$this->user = $user;
		$this->pass = $pass;
		#
		$this->host_name = "localhost"; //is used in HELO command
		$this->log_file = "";
		
		$this->sock = false;
	}

/* Main Function */
	function sendmail( $to, $from, $subject = "", $body = "", $mailtype, $cc = "", $bcc = "", $additional_headers = "" )
	{
		$mail_from = $this->get_address( $this->strip_comment($from) );
		$body = ereg_replace( "(^|(\r\n))(\\.)", "\\1.\\3", $body);
		$header .= "MIME-Version:1.0\r\n";
		if( $mailtype == "HTML" )
		{
			$header .= "Content-Type:text/html\r\n";
		}
		$header .= "To: ".$to."\r\n";
		if ( !empty($cc) )
		{
			$header .= "Cc: ".$cc."\r\n";
		}
		$header .= "From: $from<" . $from . ">\r\n";
		$header .= "Subject: " . $subject . "\r\n";
		$header .= $additional_headers;
		$header .= "Date: " . date("r") . "\r\n";
		$header .= "X-Mailer:By Redhat (PHP/" . phpversion() . ")\r\n";
		list( $msec, $sec ) = explode( " ", microtime() );
		$header .= "Message-ID: <" . date("YmdHis", $sec) . "." . ($msec * 1000000) . "." . $mail_from . ">\r\n";
		$TO = explode( ",", $this->strip_comment($to) );
		
		if ($cc != "")
		{
			$TO = array_merge($TO, explode(",", $this->strip_comment($cc)));
		}
		
		if ($bcc != "") 
		{
			$TO = array_merge($TO, explode(",", $this->strip_comment($bcc)));
		}
		
		$sent = true;
		foreach ($TO as $rcpt_to)
		{
			$rcpt_to = $this->get_address($rcpt_to);
			if (!$this->smtp_sockopen($rcpt_to))
			{
				cls_app::log("Error: Cannot send email to " . $rcpt_to . "\n");
				$sent = false;
				continue;
			}
			if ($this->smtp_send($this->host_name, $mail_from, $rcpt_to, $header, $body))
			{
				cls_app::log("E-mail has been sent to <" . $rcpt_to . ">\n");
			} else
			{
				cls_app::log("Error: Cannot send email to <" . $rcpt_to . ">\n");
				$sent = false;
			}
			fclose($this->sock);
			cls_app::log("Disconnected from remote host\n");
		}
		//echo "<br>";
		//echo $header;
		return $sent;
	}

/* Private Functions */

	function smtp_send($helo, $from, $to, $header, $body = "")
	{
		if (!$this->smtp_putcmd("HELO", $helo))
		{
			return $this->smtp_error("sending HELO command");
		}
		if($this->auth)
		{
			if ( !$this->smtp_putcmd( "AUTH LOGIN", base64_encode($this->user) ) )
			{
				return $this->smtp_error("sending HELO command");
			}
		
			if ( !$this->smtp_putcmd( '', base64_encode($this->pass) ) )
			{
				return $this->smtp_error("sending HELO command");
			}
		}
		if (!$this->smtp_putcmd("MAIL", "FROM:<" . $from . ">"))
		{
			return $this->smtp_error("sending MAIL FROM command");
		}
		
		if (!$this->smtp_putcmd("RCPT", "TO:<" . $to . ">"))
		{
			return $this->smtp_error("sending RCPT TO command");
		}
		
		if (!$this->smtp_putcmd("DATA"))
		{
			return $this->smtp_error("sending DATA command");
		}
		
		if (!$this->smtp_message($header, $body))
		{
			return $this->smtp_error("sending message");
		}
		
		if (!$this->smtp_eom())
		{
			return $this->smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
		}
		
		if (!$this->smtp_putcmd("QUIT"))
		{
			return $this->smtp_error("sending QUIT command");
		}
		
		return true;
	}

	function smtp_sockopen($address)
	{
		if ($this->relay_host == "")
		{
			return $this->smtp_sockopen_mx($address);
		} else
		{
			return $this->smtp_sockopen_relay();
		}
	}

	function smtp_sockopen_relay()
	{
		cls_app::log("Trying to ".$this->relay_host.":".$this->smtp_port."\n");
		$this->sock = @fsockopen($this->relay_host, $this->smtp_port, $errno, $errstr, $this->time_out);
		if ( !( $this->sock && $this->smtp_ok() ) )
		{
			cls_app::log("Error: Cannot connenct to relay host " . $this->relay_host . "\n");
			cls_app::log("Error: " . $errstr . " (" . $errno . ")\n");
			return false;
		}
		cls_app::log("Connected to relay host " . $this->relay_host . "\n");
		return true;
	}

	function smtp_sockopen_mx($address)
	{
		$domain = ereg_replace("^.+@([^@]+)$", "\\1", $address);
		if (!@getmxrr($domain, $MXHOSTS))
		{
			cls_app::log("Error: Cannot resolve MX \"" . $domain . "\"\n");
			return false;
		}
		foreach ($MXHOSTS as $host)
		{
			cls_app::log("Trying to " . $host . ":" . $this->smtp_port . "\n");
			$this->sock = @fsockopen($host, $this->smtp_port, $errno, $errstr, $this->time_out);
			if (!($this->sock && $this->smtp_ok()))
			{
				cls_app::log("Warning: Cannot connect to mx host " . $host . "\n");
				cls_app::log("Error: " . $errstr . " (" . $errno . ")\n");
				continue;
			}
			cls_app::log("Connected to mx host " . $host . "\n");
			return true;
		}
		cls_app::log("Error: Cannot connect to any mx hosts (" . implode(", ", $MXHOSTS) . ")\n");
		return true;
	}

	function smtp_message($header, $body)
	{
		fputs( $this->sock, $header . "\r\n" . $body );
		$this->smtp_debug( "> " . str_replace("\r\n", "\n" . "> ", $header . "\n> " . $body . "\n> ") );
		
		return true;
	}

	function smtp_eom()
	{
		fputs($this->sock, "\r\n.\r\n");
		$this->smtp_debug(". [EOM]\n");
		
		return $this->smtp_ok();
	}

	function smtp_ok()
	{
		$response = str_replace("\r\n", "", fgets($this->sock, 512));
		$this->smtp_debug( $response . "\n" );
		
		if (!ereg("^[23]", $response))
		{
			fputs($this->sock, "QUIT\r\n");
			fgets($this->sock, 512);
			cls_app::log("Error: Remote host returned \"" . $response . "\"\n");
			return false;
		}
		return true;
	}

	function smtp_putcmd($cmd, $arg = "")
	{
		if ( $arg != "" )
		{
			if( $cmd == "" )
			{
				$cmd = $arg;
			}else
			{
				$cmd = $cmd . " " . $arg;
			}
		}
		
		fputs($this->sock, $cmd . "\r\n");
		$this->smtp_debug("> " . $cmd . "\n");
		
		return $this->smtp_ok();
	}

	function smtp_error($string)
	{
		cls_app::log("Error: Error occurred while " . $string . ".\n");
		return false;
	}

	function strip_comment($address)
	{
		$comment = "\\([^()]*\\)";
		while ( ereg($comment, $address) )
		{
			$address = ereg_replace($comment, "", $address);
		}
		
		return $address;
	}

	function get_address($address)
	{
		$address = ereg_replace("([ \t\r\n])+", "", $address);
		$address = ereg_replace("^.*<(.+)>.*$", "\\1", $address);
		
		return $address;
	}

	function smtp_debug($message)
	{
		if ($this->debug)
		{
			echo $message."<br>";
		}
	}
}
?>