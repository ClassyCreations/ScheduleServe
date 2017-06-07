<?php
function buildAndCopyJar($jarName){
    if (file_exists($jarName)) return;
    if (file_exists("build/libs/$jarName")) {
        if (!copy("build/libs/$jarName", "$jarName")) {
            error_log("Unable to copy and build jar!");
        }
    } else if (file_exists("build.gradle") && file_exists("gradlew")) {
        {
            if (!defined('PHP_WINDOWS_VERSION_MAJOR')){
                exec("./gradlew clean build");
            } else {
                exec("gradlew.bat clean build");
            }
            if (!copy("build/libs/$jarName", "$jarName")) {
                error_log("Unable to copy and build jar!");
            }
        }
    } else {
        error_log($jarName . " not found in " . getcwd() . ", cloning from git...");
        exec("git clone https://github.com/MelroseSTLs/ScheduleCheck.git");
        chdir("ScheduleCheck");
        buildAndCopyJar($jarName);
        chdir("..");
        copy("ScheduleCheck/" . $jarName, $jarName);
    }
}