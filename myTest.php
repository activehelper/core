<?php
               
include("import/config_database.php");
include("import/class.mysql.php");

error_reporting(E_ALL);

echo "Host:".$SQL->db_host."<br>";
echo "user:".$SQL->db_user."<br>";
echo "Pwd :".$SQL->db_pass."<br>";
echo "Db  :".$SQL->db_name."<hr>";

function insertAccout() {
        
        global $SQL;
        global $table_prefix;
        
        $sqlstmt = "INSERT INTO ".$table_prefix."accounts (id_account,id_account_type,login,password,creation_date,expire_date,status) VALUES(99,2,'trial','test',NOW(),NOW(),0);";
        
        echo $sqlstmt."<hr>";
        
        $SQL->insertquery($sqlstmt);
        
        if ($SQL->db_error) {
                return false;
        }
        
        return true;
}

if (insertAccout()) 
        echo "successfull.";
else
        echo "error.";

?>
