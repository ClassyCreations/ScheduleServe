<?php
$schedName = "sched.txt";
$jarName = "ScheduleCheck-1.3-SNAPSHOT.jar";

header('Cache-Control: no-cache, must-revalidate'); // No Cache
header('Content-type: application/json'); // JSON Type
header('Access-Control-Allow-Origin: *'); // Allow scripts to call me

include "getScheduleJar.php";

function main(){
    global $schedName, $jarName;
    buildAndCopyJar($jarName);

    $username = $_POST['aspen_username'];
    $pass = $_POST['aspen_password'];

    $json = json_decode(readFileContents($schedName));
    if (!$username == null && !$pass == null){
        echo(runAspenJar($username, $pass, "/dev/null", false));
    } else if (time() - $json->{'asOf'} > 120) {
        error_log("Cached time: " . $json->{'asOf'} . " is greater than " . time() . " - 120, refreshing", 0);
        echo(runAspenJar(getenv('ASPEN_UNAME'), getenv('ASPEN_PASS'), $schedName, true));
    } else {
        echo(readFileContents($schedName));
    }
}

function readFileContents($schedName){
    $handle = fopen($schedName, "r");
    $contents = fread($handle, filesize($schedName));
    fclose($handle);
    return $contents;
}

/**
 *
 * @param $username String Aspen Username
 * @param $pass String Aspen Password
 * @param $file String File path to output to
 * @param $async Boolean Run async (without output)
 * @return mixed
 */
function runAspenJar($username, $pass, $file, $async){
    global $jarName;

    $command = "java -jar $jarName -f $file -u $username -p $pass";
    if ($async == true && !defined('PHP_WINDOWS_VERSION_MAJOR')){
        return exec($command . " &> /dev/null &");
    } else {
        return exec($command . " --hidePrivateData");
    }
}

function guidv4($data){ // Thanks https://stackoverflow.com/a/15875555/1709894
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

main();
