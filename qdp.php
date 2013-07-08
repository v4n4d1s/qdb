<?php

    ob_start();
    session_start();
     
    $content = array();
    $dbfile = 'db/qdb';
    $users = array(
        'skskill' => '',
        'sword' => '',
        'revan12' => '',
        'alexie' => '',
        'vanadis' => ''
    );
     
    function xutil_print($input, $autoBreak=false)
    {
        if(!$autoBreak)
        {
            echo(htmlentities($input));
            return;
        }
        echo(str_replace("\n", '<br />', htmlentities($input)));
    }
     
    function xutil_print_unsafe($input)
    {
        print($input);
    }
     
    function qdb_logout()
    {
        unset($_SESSION['authd']);
        unset($_SESSION['username']);
    }
     
    function qdb_auth($username, $password, $users)
    {
        foreach($users as $_username => $_password)
        {
            $ok = $username == $_username && $password == $_password;
            if($ok)
            {
                $_SESSION['authd'] = true;
                $_SESSION['username'] = $_username;
                return true;
            }           
        }
         
        return false;
    }
     
    function qdb_getcurrentuser()
    {
        if(isset($_SESSION['username']))
        {
            return $_SESSION['username'];
        }
        return null;
    }
     
    function qdb_authd()
    {
        if(isset($_SESSION['authd']))
        {
            return $_SESSION['authd'] === true;
        }
        return false;
    }
     
    function qdb_readall($file)
    {
        $delimiter = qdb_getdelimiter();
        $content = file_get_contents($file);
        if(strstr($content, $delimiter))
        {
            return array_reverse(explode($delimiter, $content, -1));
        }
        else
        {
            return array();
        }
    }
     
    function qdb_readfields($item)
    {
        return explode(qdb_getcntdelimiter(), $item);
    }
     
    function qdb_insert($file, $data)
    {
        $delimiter = qdb_getdelimiter();
        $contentDelimiter = qdb_getcntdelimiter();
         
        if(qdb_sec_chlen($data) && qdb_sec_notempty($data))
        {
            $file = fopen($file, 'a');
             
            $toWrite = '';
            $toWrite .= $data . $contentDelimiter;          
            $toWrite .= qdb_getcurrentuser();
             
            $toWrite .= $delimiter;
             
            fwrite($file, $toWrite);
            fclose($file);
        }
        else
        {
            die('Bad try. Max length is under 9000. Content cant be empty. ');
        }
    }
     
    function qdb_fmtqt($data)
    {
        //return preg_replace('/^(.*?)s(.*?):/ms','<1>', $data);
        return $data;
    }
     
    function qdb_dbexists($file)
    {
        return file_exists($file);
    }
     
    function qdb_sec_chlen($data)
    {
        return strlen($data) < 9000;
    }   
     
    function qdb_getdelimiter()
    {
        return "\n\r" . '????' . "\n\r";
    }
     
    function qdb_getcntdelimiter()
    {
        return ':??:';
    }
     
    function qdb_sec_notempty($data)
    {
        $data = str_replace("\n", '', $data);
        $data = str_replace(' ', '', $data);
        return !empty($data);
    }
     
    if(!qdb_dbexists($dbfile))
    {
        die('DB File doesnt exist. Please create one.');
    }
     
    if(qdb_authd())
    {       
        if(isset($_POST['logout']))
        {
            qdb_logout();
            header('Location: ' . 'index.php');
        }
     
        if(isset($_POST['data']))
        {
            $data = $_POST['data'];
            qdb_insert($dbfile, qdb_fmtqt($data));
        }       
         
    }
    else if(isset($_POST['password'])) {
        qdb_auth($_POST['username'], $_POST['password'], $users);
        header('Location: ' . 'index.php');
    }
     
?>
<html>
    <head>
        <title><img src="23x.png" style="float:left" /> QDB 2.0</title>
    </head>
    <body>
        <form method="post">
<?php                 $content = qdb_readall($dbfile);
?>
            <h1><img src="23x.png" style="float:left" /> QDB 2.0 <?php if(qdb_authd()) {?><input type="submit" name="logout" value="Logout" /><?php } ?></h1>
            
  		<?php if(qdb_authd()) { ?>
                 
                    <textarea style="height:150px;width:300px;" name="data"></textarea>
                    <br /><input type="submit" value="Commit" />
                    <br />
                 
            <?php } else { ?>
                    Username
                    <input type="text" name="username" />
                    <br />
                    Password
                    <input type="password" name="password" />
                    <br />
                    <input type="submit" value="Auth" />
            <?php } ?>
                    <br><br><br>
                <?php foreach($content as $item) {
                    $fields = qdb_readfields($item);
                     
                    xutil_print($fields[0], true);
                    xutil_print_unsafe('<br /><br /><i>posted by ');
                    xutil_print($fields[1]);
                    xutil_print_unsafe('</i><hr />');
                } ?>
        </form>
    </body>
</html>
