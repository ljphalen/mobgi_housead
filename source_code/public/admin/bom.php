<?php
if(isset($data['id'])) $tmp['id'] = intval($data['id']);
if(isset($data['name'])) $tmp['name'] = $data['name'];
if(isset($data['limit_type'])) $tmp['limit_type'] = $data['limit_type'];
if(isset($data['limit_range'])) $tmp['limit_range'] = $data['limit_range'];
if(isset($data['mode_type'])) $tmp['mode_type'] = $data['mode_type'];
if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
if(isset($data['account_id'])) $tmp['account_id'] = $data['account_id'];
if(isset($data['status'])) $tmp['status'] = $data['status'];
//此文件用于快速测试UTF8编码的文件是不是加了BOM，并可自动移除
if (isset($_GET['dir'])) { //设置文件目录  
    $basedir = $_GET['dir'];
} else {
    $basedir = '.';
}
$auto=1; //是否自动移除发现的BOM信息。1为是，0为否。
checkdir($basedir);
function checkdir($basedir)
{
    if ($dh = opendir($basedir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                if (!is_dir($basedir . "/" . $file)) {
                    echo "filename: $basedir/$file " . checkBOM("$basedir/$file") . " <br>";
                } else {
                    $dirname = $basedir . "/" . $file;
                    checkdir($dirname);
                }
            }
        }
        closedir($dh);
    }
}
function checkBOM($filename)
{
    global $auto;
    $contents   = file_get_contents($filename);
    $charset[1] = substr($contents, 0, 1);
    $charset[2] = substr($contents, 1, 1);
    $charset[3] = substr($contents, 2, 1);
    if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
        if ($auto == 1) {
            $rest = substr($contents, 3);
            rewrite($filename, $rest);
            return ("<font color='red'>BOM found, automatically removed.</font>");
        } else {
            return ("<font color='red'>BOM found.</font>");
        }
    } else
        return ("BOM Not Found.");
}

function rewrite($filename, $data)
{
    $filenum = fopen($filename, "w");
    flock($filenum, LOCK_EX);
    fwrite($filenum, $data);
    fclose($filenum);
}
?>