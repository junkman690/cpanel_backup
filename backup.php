<?php
// Automatic cPanel FTP backup
// This script contains passwords. Do not put it in a public folder.
  
// ********* THE FOLLOWING ITEMS NEED TO BE CONFIGURED *********
//Websites to backup. You need to create an API user. Auth need to be the cPanel username and API token seperate by : 
//https://api.docs.cpanel.net/cpanel/tokens/

// Site 0
$sites['0']['domain'] = 'example.com';
$sites['0']['auth'] = 'site1:longapicode';
 
//Stie 1
$sites['1']['domain'] = 'example.net';
$sites['1']['auth'] = 'site2:longapicode';

// Credentials for FTP to Backup Box
$ftp = true;
$sleeptime = 300; //Time in seconds between each site
$ftp_type = 'passive'; //'passive' for Passive FTP connection. 'active' for Active FTP https://api.docs.cpanel.net/openapi/cpanel/operation/fullbackup_to_ftp/
$ftpaddress = 'ftp.example.com'; //FTP Address
$localftp = '127.0.0.1';//FTP local adresss to create folders
$ftpacct = 'user'; // FTP account
$ftppass = 'password'; // FTP password
$email_notify = 'me@test.com'; // Email address for backup notification
$ftpdir = '/'; //Directory backups will be save in
$ftp_port = '21'; //FTP Port

// *********** NO CONFIGURATION ITEMS BELOW THIS LINE *********

$date = date("Y-m-d");
$backup_dir = $ftpdir.$date;
$conn_id = ftp_connect($localftp);
$login_result = ftp_login($conn_id, $ftpacct, $ftppass);

//Create backup directory if it doesn't already exist
ftp_chdir($conn_id, $ftpdir);

if (ftp_chdir($conn_id, $date))
	{
	echo "Date directory already there\n";
	}
else
	{
	ftp_mkdir($conn_id, $date);
	echo "Making date directory\n";
	}

//Backup the sites to FTP  
foreach ($sites as $site)
{
$query = "https://" . $site['domain'] . ":2083" . "/execute/Backup/fullbackup_to_ftp?" . "variant=" . $ftp_type . "&host=" . $ftpaddress . "&username=" . $ftpacct . "&password=" . $ftppass . "&port=" .  $ftp_port . "&directory=" . $backup_dir . "&email=" . $email_notify;
echo "Backing up " . $site['domain'] . "\n";
echo "using " . $query . "\n";
$curl = curl_init();                                // Create Curl Object
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);       // Allow self-signed certs
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);       // Allow certs that do not match the hostname
curl_setopt($curl, CURLOPT_HEADER,0);               // Do not include header in output
curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       // Return contents of transfer on curl_exec
$header[0] = "Authorization: cpanel " . $site['auth'] . "\n\r";
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
curl_setopt($curl, CURLOPT_URL, $query);            // execute the query
$result = curl_exec($curl);
if ($result == false) {
    error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");   
                                                    // log error if curl exec fails
}
curl_close($curl);

print $result;

sleep($sleeptime);
}
  
?>
