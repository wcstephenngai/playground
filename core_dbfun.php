<?php
require $_SERVER["DOCUMENT_ROOT"] . '/system/page/core_function.php';
date_default_timezone_set("Asia/Hong_Kong");
require $_SERVER["DOCUMENT_ROOT"] . '/system/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//echo date_default_timezone_get();

class databasefun
{

    private $servername = "localhost:3306";
    private $username = "uathemmafabuser";
    private $password = "n2~4tx20L";
    private $dbname = 'uathemmafab';

    /*private $servername = "10.100.5.140:3306";
    private $username = "kadooria";
    private $password = "~rQk8o23";
    private $dbname = 'rccomhk_ka';*/



    //date_default_timezone_set("Asia/Hong_Kong");

    public function connect()
    {

        //try connect

        if (!property_exists($this, 'con') || !$this->con) {

            //$myconn = mysql_connect($this->servername,$this->username,$this->password);
            $myconn = mysqli_connect($this->servername, $this->username, $this->password);
            // Check connection
            if (!$myconn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            //echo "Connected successfully";

            mysqli_set_charset($myconn, "utf8");
            mysqli_character_set_name($myconn);


            if ($myconn) {
                $seldb = mysqli_select_db($myconn, $this->dbname);
                if ($seldb) {
                    $this->con = true;

                    //mysql_set_charset($myconn,"utf8");

                    return true;
                    echo "yes connect";
                    echo $dbname;
                } else {
                    return false;
                }
            } else {
                //die( "Unable to connect!!" );
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
                return false;
            }
        } else {

            return true;
        }
    }



    public function disconnect()
    {

        $mynewcon = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);

        if ($this->con) {

            if (mysqli_close($mynewcon)) {
                $this->con = false;
                return true;
            } else {
                return false;
            }
        }
    }



    public function select($table, $rows = ' * ', $where = null, $order = null, $limit = null)
    {

        $mynew = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);

        //setting up sql     
        $sql = ' SELECT ' . $rows . ' FROM `' . $table . '`';
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        if (!empty($order)) {
            $sql .= ' ORDER BY ' . $order;
        }
        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        //echo $sql . '<br><br>';
        //mysql_query("set character set 'utf8'");//读库 
        //mysql_query("set names 'utf8'");//写库 
        mysqli_set_charset($mynew, "utf8");
        mysqli_character_set_name($mynew);


        $query = mysqli_query($mynew, $sql);
        if ($query) {

            $this->numResults = mysqli_num_rows($query);
            $this->result = array(); // Define $result as an empty array
            for ($i = 0; $i < $this->numResults; $i++) {
                $r = mysqli_fetch_array($query);

                $key = array_keys($r);
                for ($x = 0; $x < count($key); $x++) {
                    // Sanitizes keys so only alphavalues are allowed
                    if (!is_int($key[$x])) {
                        if (mysqli_num_rows($query) > 1)
                            $this->result[$i][$key[$x]] = $r[$key[$x]];
                        else if (mysqli_num_rows($query) < 1)
                            $this->result = null;
                        else
                            $this->result[$key[$x]] = $r[$key[$x]];
                    }
                }
            }
            //return true; 

            //print_r( $this->result );

            return $this->result;
        } else {
            return false;
        }
    }




    public function insert($table, $values, $rows = null)
    {
        $new = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);

        $insert = 'INSERT INTO ' . $table;
        if ($rows != null) {
            $insert .= ' (' . $rows . ')';
        }

        $placeholders = [];
        $params = [];
        // Process the values array
        if (is_array($values)) {
            foreach ($values as $value) {
                $placeholders[] = '?';
                $params[] = $value;
            }
        }

        $values = implode(', ', $placeholders);
        $insert .= ' VALUES (' . $values . ')';

        mysqli_set_charset($new, "utf8");

        $stmt = mysqli_prepare($new, $insert);
        if ($stmt) {
            // Bind parameters dynamically
            if (!empty($params)) {
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } elseif (is_null($param)) {
                        $types .= 'n';
                    } else {
                        throw new Exception('Unsupported data type.');
                    }
                }
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }

            // Execute the statement
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($result) {
                return true;
            } else {
                throw new Exception(mysqli_error($new));
            }
        } else {
            throw new Exception(mysqli_error($new));
        }
    }




    //public function delete()        {   }


    public function delete($table, $where = null)
    {

        $newcon = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);

        if ($where == null) {
            $delete = 'DELETE ' . $table;
        } else {
            //echo $delete = 'DELETE FROM ' . mysql_real_escape_string ( $table ) .' WHERE ' .  $where  ; 
            $delete = 'DELETE FROM ' .  $table . ' WHERE ' .  $where;
        }
        $del = mysqli_query($newcon, $delete);

        if ($del) {
            return true;
        } else {
            return false;
        }
    }


    /*
    public function delete($table,$where = null)
    {
        if($this->tableExists($table))
        {
            if($where == null)
            {
                $delete = 'DELETE '.$table; 
            }
            else
            {
                $delete = 'DELETE FROM '.$table.' WHERE '.$where; 
            }
            $del = @mysql_query($delete);
 
            if($del)
            {
                return true; 
            }
            else
            {
               return false; 
            }
        }
        else
        {
            return false; 
        }
    }
    */


    public function update($table, $rows, $where)
    {

        $new = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
        /*
        echo $table;
        echo '<br>';
        print_r ( $rows );
        echo '<br>';
        print_r( $where ); 
        echo '<br>';
   
        foreach ($where as $key => $val) {
            print "$key = $val\n";
        } 
        */

        //set sql where 
        if (count($where) >= 1) {

            $i = 0;
            $temp_where = '';
            foreach ($where as $key => $val) {
                $i++;
                if ($i >= 2) {
                    $temp_where .= " AND ";
                }
                $temp_where .= " $key = '" . mysqli_real_escape_string($new, $val) . "' ";
            }
        } else {
            //no value 
            echo 'error';
            exit;
        }

        $where = ' WHERE ' . $temp_where;
        unset($temp_where);


        if (count($rows) >= 1) {

            $values = '';
            $i = 0;

            foreach ($rows as $key => $val) {

                $i++;
                if ($i >= 2) {
                    $values .= " , ";
                }
                $values .= " $key='" . mysqli_real_escape_string($new, $val) . "' ";;
            }
        } else {
            //no value     
            echo 'error';
            exit;
        }
        //echo $values; 
        //echo '<br><br>';
        //mysqli_query("set character set 'utf8'");//读库 
        //mysqli_query("set names 'utf8'");//写库 

        mysqli_set_charset($new, "utf8");
        mysqli_character_set_name($new);


        $sql = " UPDATE " . $table . " SET " . $values . $where;
        $query = mysqli_query($new, $sql);

        //echo $sql;

        if ($query) {
            return true;
        } else {
            return false;
        }
    }


    /*
    public function update($table,$rows,$where)
    {
        if($this->tableExists($table))
        {
            // Parse the where values
            // even values (including 0) contain the where rows
            // odd values contain the clauses for the row
            for($i = 0; $i < count($where); $i++)
            {
                if($i%2 != 0)
                {
                    if(is_string($where[$i]))
                    {
                        if(($i+1) != null)
                            $where[$i] = '"'.$where[$i].'" AND ';
                        else
                            $where[$i] = '"'.$where[$i].'"';
                    }
                }
            }
            $where = implode('=',$where);
             
             
            $update = 'UPDATE '.$table.' SET ';
            $keys = array_keys($rows); 
            for($i = 0; $i < count($rows); $i++)
           {
                if(is_string($rows[$keys[$i]]))
                {
                    $update .= $keys[$i].'="'.$rows[$keys[$i]].'"';
                }
                else
                {
                    $update .= $keys[$i].'='.$rows[$keys[$i]];
                }
                 
                // Parse to add commas
                if($i != count($rows)-1)
                {
                    $update .= ','; 
                }
            }
            $update .= ' WHERE '.$where;
            $query = @mysql_query($update);
            if($query)
            {
                return true; 
            }
            else
            {
                return false; 
            }
        }
        else
        {
            return false; 
        }
    }
    */

    public function logadd($user, $str)
    {

        $values[]  = date('Y-m-d H:i:s');
        $values[]  = $user;
        $values[]  = substr($str, 0, 200);
        $rows = ' date , userid , logcontent ';

        $this->insert("log", $values, $rows);
    }

    public function escape($str)
    {
        $newmyconn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
        return mysqli_real_escape_string($newmyconn, $str);
    }

    //Fail Login Log and Lock Account Functions. 

    public function recordLoginAttempt(int $userid, bool $success, string $userAgent, string $ipAddress): bool
    {
        //$currentTime = date('Y-m-d H:i:s'); // Get current timestamp

        // Check if a row exists for the user and timestamp
        $where = "userid = $userid";
        $existingRow = $this->select('login_attempts', '*', $where, "", "");
        // Fetch the data (assuming you are using mysqli)

        if ($existingRow) {
            // Update the existing row
            if (!$success) { // Increment failed attempts if login is unsuccessful
                $existingRow['failed_attempts']++;
            }
            $rows = [
                'success' => $success ? 1 : 0,
                'userAgent' => $userAgent,
                'ipAddress' => $ipAddress,
                'failed_attempts' => $existingRow['failed_attempts'],
            ];
            $arr_where = array();
            $arr_where['userid'] = $userid;
            return $this->update('login_attempts', $rows, $arr_where);
        } else {
            // Insert a new row
            $values = [
                'userid' => $userid,
                'success' => $success ? 1 : 0,
                'userAgent' => $userAgent,
                'ipAddress' => $ipAddress,
                'failed_attempts' => !$success ? 1 : 0, // Increment failed attempts if login is unsuccessful
            ];
            $rows = 'userid, success, userAgent, ipAddress, failed_attempts';
            return $this->insert('login_attempts', $values, $rows);
        }
    }

    public function getFailedLoginAttempts(int $userid, int $timeframe = 3600): int
    {
        $where = "userid = $userid AND success = 0";
        $result = $this->select('login_attempts', 'failed_attempts', $where);

        return $result ? $result['failed_attempts'] : 0; // Return the failed_attempts count
    }

    public function emaillockinfo(int $userid, string $email): bool
    {
        //check username of the userid
        $where = "id = $userid";
        $admin_email = $this->select('options', 'content', "meta_key = 'super_admin_email'", "", "1");
        $admin_email = unserialize($admin_email['content'])['value'];
        $result = $this->select('user', 'name', $where, "", "1");
        $username = $result['name'];
        $subject = 'Your Account has been locked @ Hong Kong Housing Society';
        $body = 'Dear ' . $username . ',<br>' .
            'Your account has been locked.<br>' .
            'Please contact the administrator to unlock your account by sending your email address and username<br>' .
            'to the following email address: ' . $admin_email .
            'Thank you.<br>' .
            'Hong Kong Housing Society';

        $smtp_setting = array();

        $this->logadd($userid, 'Account locked Email sent');
        return $this->sendsmtpEmail($subject, $body, $smtp_setting, $email);
    }

    public function lockAccount(int $userid): bool
    {
        $rows = ['locked' => 1]; // Set the 'locked' flag to 1
        $where = "id='" . $userid . "'";
        $arr_where = array();
        $arr_where['id'] = $userid;
        $this->logadd($userid, 'Account locked');
        $email_arr = $this->select('user', 'email', $where, "", "1");
        $email = $email_arr['email'];
        $this->emaillockinfo($userid, $email);
        return $this->update('user', $rows, $arr_where); // Assuming 'users' table stores user data
    }

    public function unlockAccount(int $userid): bool
    {
        // Reset failed login attempts
        $this->resetFailedLoginAttempts($userid);

        // Unlock the account
        $rows = ['locked' => 0];
        $where = "id = $userid";
        $arr_where = array();
        $arr_where['id'] = $userid;
        $this->logadd($userid, 'Account unlocked');
        return $this->update('user', $rows, $arr_where);
    }

    public function isAccountLocked(int $userid): bool
    {
        $where = "id = $userid AND locked = 1";
        $result = $this->select('user', '*', $where);
        return !empty($result);
    }

    public function resetFailedLoginAttempts(int $userid): bool
    {
        $where = "userid = $userid";
        $rows = ['failed_attempts' => 0]; // Reset failed attempts to 0
        $arr_where = array();
        $arr_where['userid'] = $userid;
        return $this->update('login_attempts', $rows, $arr_where);
    }

    public function logLoginAttempt(int $userid, string $userAgent, string $ipAddress, bool $success): bool
    {
        $values = [
            'userid' => $userid,
            'timestamp' => date('Y-m-d H:i:s'), // Timestamp should be in the correct position
            'userAgent' => $userAgent,
            'ipAddress' => $ipAddress,
            'success' => $success ? 1 : 0,
        ];
        $rows = 'userid, timestamp, userAgent, ipAddress, success';
        return $this->insert('login_attempts_log', $values, $rows);
    }

    public function relink2fa(int $userid, int $sessionuser): bool
    {
        if ($userid == $sessionuser) {
            $rows = array();
            $rows = ['secret' => ''];
            $rows = ['is_tfa_enabled' => 3];
            $arr_where = array();
            $arr_where['id'] = $userid;
            return $this->update('user', $rows, $arr_where);
        } else {
            return false;
        }
    }

    /**
     * Get an AES key from a static password and a secret salt
     * 
     * @param string $password Your weak password here
     * @param int $keysize Number of bytes in encryption key
     */
    public function getKeyFromPassword($password, $keysize = 16)
    {
        $password = hex2bin($password);
        return hash_pbkdf2(
            'sha256',
            $password,
            '\x2d\xb7\x68\x1a\x28\x15\xbe\x06\x33\xa0\x7e\x0e\x8f\x79\xd5\xdf',
            100000,
            $keysize,
            true
        );
    }

    public function lastInsertId($table)
    {
        $this->select($table, 'id', '', 'id DESC', '1');
        return $this->result['id'];
    }

    public function sendsmtpEmail($subject, $body, $smtp_setting, $email)
    {

        // Create a new PHPMailer instance 
        $mail = new PHPMailer(true);
        if ($email == '') {
            return false;
        }
        //get smtp configuration
        $smtp_host_arr = $this->select("options", " * ", " meta_key = 'smtp_host' ", "", "1");
        $smtp_username_arr = $this->select("options", " * ", " meta_key = 'smtp_username' ", "", "1");
        $smtp_password_arr = $this->select("options", " * ", " meta_key = 'smtp_password' ", "", "1");
        $smtp_secure_arr = $this->select("options", " * ", " meta_key = 'smtp_secure' ", "", "1");
        $smtp_port_arr = $this->select("options", " * ", " meta_key = 'smtp_port' ", "", "1");
        //put it into a array
        $smtp_setting = array();
        $smtp_setting = array(
            "host" => unserialize($smtp_host_arr['content'])['value'],
            "username" => unserialize($smtp_username_arr['content'])['value'],
            "password" => unserialize($smtp_password_arr['content'])['value'],
            "secure" => unserialize($smtp_secure_arr['content'])['value'],
            "port" => unserialize($smtp_port_arr['content'])['value'],
        );

        // SMTP configuration 
        $mail->isSMTP();
        $mail->Host = $smtp_setting['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_setting['username'];
        $mail->Password = $smtp_setting['password'];
        $mail->SMTPSecure = $smtp_setting['secure'];
        $mail->Port = $smtp_setting['port'];
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->isHTML(true); // Set email format to HTML
        //check if $smtp_setting['username'] is a valid email and add @hkhs.com if it is not an email
        if (!filter_var($smtp_setting['username'], FILTER_VALIDATE_EMAIL)) {
            $smtp_setting['username'] = $smtp_setting['username'] . '@hkhs.com';
        }

        // Set the From and To addresses 
        $mail->setFrom($smtp_setting['username'], 'System Auto');
        $mail->addCustomHeader('Content-Type', 'text/html; charset=utf-8');
        //check if is admin email
        if ($email == 'admin_email') {
            $admin_email_arr = $this->select("user", " * ", " level = 'dmd_super' ", "", "");
            if (!empty($admin_email_arr)) {
                foreach ($admin_email_arr as $key => $value) {
                    if (empty($admin_email_arr[0]['id'])) {
                        $value = $admin_email_arr;
                    }
                    if ($value['email'] == '') {
                        continue;
                    }
                    $mail->addBCC($value['email'], 'Housing Society Admin');

                    if (empty($admin_email_arr[0]['id'])) {
                        break;
                    }
                }
            } else {
                return false;
            }
        } else {
            $mail->addAddress($email, 'Housing Society User');
        }


        // Set email subject and body 
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Send the email 
        if ($mail->send()) {
            return true;
        } else {
            // try using php mail() function if the above fails
            $to = 'recipient@example.com';
            $subject = 'Hello from PHP mail()';
            $message = 'This is a test email sent using PHP mail() function.';
            $headers = 'From: your-email@example.com' . "\r\n" .
                'Reply-To: your-email@example.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            if (mail($to, $subject, $message, $headers)) {
                return true;
            } else {
                return false;
            }
        }
    }
}
