 <?php

  include_once('../string_util.php');
  include_once('../constants.php');
  include_once('../config_database.php');

  define('FILTER_CONTAIN', '1');
  define('FILTER_EXCLUDE', '2');

  class xQuery  {

  public $query = '';
  public $sumFields = '';

  public $filterField = '';
  public $filterValue = '';
  public $filterType = '';

  public $rowsPerView = 10;
  public $pageNum = '';

  public $refreshRecordCount = false;
  public $additionalData;

  public $attributes;

  // grid pages count
  private $mTotalPages;
  // grid items count
  public $mItemsCount;
  // index of page to be returned
  private $mReturnedPage;

  // database handler
  private $conn = '';
  //XML structure
  private $xml = '';


  function __construct()
  {
    // create the MySQL connection
    // $this->conn = new MySQL();
    // $this->conn->connect();

    $this->conn = mysql_connect(DB_HOST, DB_USER, DB_PASS);
    mysql_select_db(DB_NAME, $this->conn);

  }

  // class destructor, closes database connection
  function __destruct()
  {

  }
  /*
  private function validateAttribs()
  {
    if($this->pageNum == ''){
      throw new Exception("The attribute number pages is empty. Field requerid ");
    }

  }
  */
  // returns the total number of records for the grid
  public function createXML()
  {
    try {

      // call countAllRecords to get the number of grid records
      $this->mItemsCount = $this->countAllRecords();

      $xmlHead = '<?xml version="1.0" encoding="utf-8"?>'."\n";
      $xmlHead .= '<datapacket Version="1.0">'."\n";
      //$xmlHead .= '<action>'.$this->action.'</action>'."\n";
      $this->xml = $xmlHead;

      $this->createMetadata();
      $this->createData();
      $this->createAdditionalData();
      $this->xml .= $this->getParamsXML();
      $this->xml .= "\n".'</datapacket>'."\n";

      return $this->xml;

    } catch (Exception $e) {
      // handle the error
      throw new Exception("Error: ".$e);
    }

  }

  private function createAdditionalData()
  {
    //Query operation
    $queryOperation = '';
    $queryString = '';

    $this->xml .= '<additional_data>'."\n";
    if (isset($this->additionalData))
    {
      $this->xml .= $this->additionalData;
    }
    if (isset($this->attributes))
    {
      $this->xml .= '<attributes>'."\n";

        $oper = $this->attributes[0]['operation'];
        $title = $this->attributes[0]['title'];
        $column = $this->attributes[0]['column'];
        $type = $this->attributes[0]['type'];
        $unit_measure = $this->attributes[0]['unit_measure'];
        $documentation = $this->attributes[0]['documentation'];
        $link = $this->attributes[0]['link'];

        if((isset($oper)) and ($oper != ''))
        {
          $this->createResult($oper, $type, $column);
        }

        $this->xml .= '<documentation>'.$documentation.'</documentation>'."\n";
        $this->xml .= '<title>'.$title.'</title>'."\n";
        $this->xml .= '<column>'.$column.'</column>'."\n";
        $this->xml .= '<unit_measure>'.$unit_measure.'</unit_measure>'."\n";
        $this->xml .= '<link>'.$link.'</link>'."\n";

      $this->xml .= '</attributes>'."\n";
    }


    $this->xml .= "</additional_data>\n";

     //error_log("xquery.php:createAdditionalData: "." \n", 3, "/var/www/html/error.log");
  }


  private function createResult($oper, $type, $column)
  {
    $queryString = '';
    // create the SQL query that returns a page of products
    $queryString = $this->createSubpageQuery($this->query);

    if(($oper == 'sum') and ($type == 'TIME')){
      $queryString =  "select  SEC_TO_TIME(".$oper."(TIME_TO_SEC(".$column."))) ".$column." from (".$queryString.") xyzx";
    }
    else
    {
      $queryString =  "select ".$oper."(".$column.") ".$column." from (".$queryString.") xyzx";
    }

    $result = mysql_query($queryString, $this->conn);
    $row = mysql_fetch_assoc($result);
    $this->xml .= '<operation>'.$row[$column].'</operation>'."\n";

  }


  // read a page of products and save it to $this->grid
  private function createData()
  {

    try {

      // create the SQL query that returns a page of products
      $queryString = $this->createSubpageQuery($this->query);

      // execute the query
      if ($result = mysql_query($queryString, $this->conn))
      {
        $this->xml .= '<data>'."\n";

        // fetch associative array
        while ($row = mysql_fetch_assoc($result))
        {
          // build the XML structure containing products
          $this->xml .= '<row>'."\n";
          foreach($row as $name=>$val){
//            $this->xml .= '<field name="'.$name.'">'.htmlspecialchars($val).'</field>'."\n";
            $this->xml .= '<field name="'.$name.'"><![CDATA['.$val.']]></field>'."\n";
          }
          $this->xml .= '</row>'."\n";
        }
        $this->xml .= '</data>'."\n";
      }

    }catch (Exception $e) {
      // handle the error
      throw new Exception("Error: ".$e);
    }
  }


  // read a page of products and save it to $this->grid
  private function createMetadata()
  {

    try {

      // create the SQL query that returns a page of products
      $queryString = $this->createSubpageQuery($this->query);

      // execute the query
      if ($result = mysql_query($queryString, $this->conn))
      {
            // print_r($result);
        $this->xml .= '<metadata>';
        $numFields = mysql_num_fields($result);

        for($i=0; $i<= $numFields -1; $i++){
          $this->xml .= '<field>'."\n";
            $this->xml .= '<name>'. mysql_field_name($result, $i).'</name>'."\n";
            $this->xml .= '<type>'. mysql_field_type($result, $i).'</type>'."\n";
            $this->xml .= '<length>'. mysql_field_len($result, $i).'</length>'."\n";
            $this->xml .= '<flags>'. mysql_field_flags($result, $i).'</flags>'."\n";
          $this->xml .= '</field>'."\n";
        }
        $this->xml .= '</metadata>'."\n";

      }

    } catch (Exception $e) {
      // handle the error
      throw new Exception("Error: ".$e);
    }
  }

  // returns data about the current request (number of grid pages, etc)
  private function getParamsXML()
  {
    // calculate the previous page number
    $previous_page = ($this->mReturnedPage == 1) ? '' : $this->mReturnedPage-1;

//error_log("xquery:getParamsXML:next_page ".$next_page." \n", 3, "/var/www/html/error.log");

    // calculate the next page number
    $next_page = ($this->mTotalPages == $this->mReturnedPage) ? '' : $this->mReturnedPage + 1;

//error_log("xquery:getParamsXML:previous_page ".$previous_page." \n", 3, "/var/www/html/error.log");

    // return the parameters
    return '<params>' ."\n".
           '<returned_page>' . $this->mReturnedPage . '</returned_page>'."\n".
           '<total_pages>' . $this->mTotalPages . '</total_pages>'."\n".
           '<rows_per_view>' . $this->rowsPerView . '</rows_per_view>'."\n".
           '<items_count>' . $this->mItemsCount . '</items_count>'."\n".
           '<filter_field>' . $this->filterField . '</filter_field>'."\n".
           '<filter_value>' . $this->filterValue . '</filter_value>'."\n".
           '<filter_type>' . $this->filterType . '</filter_type>'."\n".
           '<previous_page>' . $previous_page . '</previous_page>'."\n".
           '<next_page>' . $next_page . '</next_page>'."\n".
           '</params>'."\n";
  }

  // returns the total number of records for the grid
  private function countAllRecords()
  {
    /* if the record count isn't already cached in the session,
       read the value from the database */

//error_log("xquery.php:countAllRecords:query: ".$this->query." \n", 3, "/var/www/html/error.log");

    if (($this->refreshRecordCount == true) || (!isset($_SESSION['record_count'])))
    {
      // the query that returns the record count
      $count_query = $this->createFilter($this->query);

      $count_query =  "select count(*) record_count from (".$count_query.") xyzx";

      // execute the query and fetch the result
      //echo($count_query);
      $result = mysql_query($count_query, $this->conn);

      session_start();
      $row = mysql_fetch_assoc($result);

      /* retrieve the first column of the first row (it represents the
      records count that we were looking for), and save its value in
      the session */

      // retrieve the first returned row
      $_SESSION['record_count'] = $row['record_count'];

      }

//error_log("xquery.php:countAllRecords:record_count: ".$row['record_count']." \n", 3, "/var/www/html/error.log");

      $this->refreshRecordCount = false;
      // read the record count from the session and return it
      return $_SESSION['record_count'];
  }

  // receives a SELECT query that returns all products and modifies it
  // to return only a page of products
  private function createSubpageQuery($queryString)
  {

    $queryString = $this->createFilter($queryString);

    // if we have few products then we don't implement pagination
    if ($this->mItemsCount <= $this->rowsPerView)
    {
      $this->pageNum = 1;
      $this->mTotalPages = 1;
    }
    // else we calculate number of pages and build new SELECT query
    else if($this->pageNum == ''){
      return $queryString;
    }
    else
    {
      $this->mTotalPages = ceil($this->mItemsCount / $this->rowsPerView);
      $start_page = ($this->pageNum - 1) * $this->rowsPerView;
      $queryString .= ' LIMIT ' . $start_page . ',' . $this->rowsPerView;
    }
    // save the number of the returned page
    $this->mReturnedPage = $this->pageNum;
    // returns the new query string
    return $queryString;
  }

  private function createFilter($queryString)
  {
    $strFilter= '';

    if($this->filterValue != '')
    {
      if($this->filterType == FILTER_CONTAIN){
        $strFilter .= " HAVING ".$this->filterField." LIKE '%".$this->filterValue."%' ";
      }else{
        $strFilter .= " HAVING ".$this->filterField." NOT LIKE '%".$this->filterValue."%' ";
      }
      return $queryString = str_insertFromLast('ORDER BY', $strFilter, $queryString);
    }
    return $queryString;
  }


  /*
  private function seterror($value) {
    $this->error = $value;
  }

  public function geterror() {
    return $this->error;
  }
  */


}
?>
