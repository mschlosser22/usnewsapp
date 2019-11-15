<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
Curl Test Command
curl --verbose -H 'User-Agent: YourApplication/1.0' 'https://www.usnews.com/best-colleges/rankings/national-universities'
*/

// GET params used to check if select value is selected
$schoolTypeSelectValue = (!empty($_GET['schoolType']) ? $_GET['schoolType'] : "");
$sortBySelectValue = (!empty($_GET['sort_by']) ? $_GET['sort_by'] : "");

// Select values for School Type
$schoolTypeSelect = array(
  array(
    "name" => "All Rankings",
    "value" => ""
  ),
  array(
    "name" => "National Universities",
    "value" => "national-universities"
  ),
  array(
    "name" => "National Liberal Arts Colleges",
    "value" => "national-liberal-arts-colleges"
  ),
  array(
    "name" => "Regional Universities North",
    "value" => "regional-universities-north"
  ),
  array(
    "name" => "Regional Universities South",
    "value" => "regional-universities-south"
  ),
  array(
    "name" => "Regional Universities Midwest",
    "value" => "regional-universities-midwest"
  ),
  array(
    "name" => "Regional Universities West",
    "value" => "regional-universities-west"
  ),
  array(
    "name" => "Regional Colleges North",
    "value" => "regional-colleges-north"
  ),
  array(
    "name" => "Regional Colleges South",
    "value" => "regional-colleges-south"
  ),
  array(
    "name" => "Regional Colleges Midwest",
    "value" => "regional-colleges-midwest"
  ),
  array(
    "name" => "Regional Colleges West",
    "value" => "regional-colleges-west"
  ),
  array(
    "name" => "Business",
    "value" => "business"
  ),
  array(
    "name" => "Engineering (Doctorate Offered)",
    "value" => "engineering-doctorate"
  ),
  array(
    "name" => "Engineering (Doctorate Not Offered)",
    "value" => "engineering-no-doctorate"
  ),
  array(
    "name" => "Historically Black Colleges and Universities",
    "value" => "hbcu"
  ),
  array(
    "name" => "First Year Experience",
    "value" => "first-year-experience-programs"
  ),
  array(
    "name" => "Internship",
    "value" => "internship-programs"
  ),
  array(
    "name" => "Learning Community",
    "value" => "learning-community-programs"
  ),
  array(
    "name" => "Senior Capstone",
    "value" => "senior-capstone-programs"
  ),
  array(
    "name" => "Service Learning",
    "value" => "service-learning-programs"
  ),
  array(
    "name" => "Study Abroad",
    "value" => "study-abroad-programs"
  ),
  array(
    "name" => "Undergraduate Research/Creative Projects",
    "value" => "undergrad-research-programs"
  ),
  array(
    "name" => "Writing in the Disciplines",
    "value" => "writing-programs"
  )
);

// Select values for Sort By
$sortBySelect = array(
  array(
    "name" => "Rankings (high to low)",
    "value" => "rank:asc"
  ),
  array(
    "name" => "Alphabetically (A-Z)",
    "value" => "schoolName:asc"
  ),
  array(
    "name" => "Alphabetically (Z-A)",
    "value" => "schoolName:desc"
  ),
  array(
    "name" => "Tuition and Fees (low to high)",
    "value" => "tuition:asc"
  ),
  array(
    "name" => "Tuition and Fees (high to low)",
    "value" => "tuition:desc"
  ),
  array(
    "name" => "Enrollment (low to high)",
    "value" => "enrollment:asc"
  ),
  array(
    "name" => "Enrollment (high to low)",
    "value" => "enrollment:desc"
  ),
  array(
    "name" => "Acceptance Rate (low to high)",
    "value" => "acceptanceRate:asc"
  ),
  array(
    "name" => "Acceptance Rate (high to low)",
    "value" => "acceptanceRate:desc"
  )
);

// Global defaults
$CSV = "";
$response = null; // Final response
$count = 0; // Call count
$maxCallCount = 20; // Each call return 10 items, max set to 200 items
$httpcode = "";

// API call function
class GetSchools {

  public $items;

  function __construct($params = null) {
    $this->items = [];
    // Initialize instance
    $this->call($params);
  }

  private function call($params) {
    global $count;
    global $maxCallCount;
    global $httpcode;

    // Make sure we're not exceeding max call count
    if ($count < $maxCallCount) {
      $count++;

      // If params string is not provided, check GET for values
      if ($params == null) {
        // Properly extract values to create param string
        $schoolType = $_GET['schoolType'];
        $sortGet = explode(":", $_GET['sort_by']);
        $sort = $sortGet[0];
        $sortDirection = $sortGet[1];
        // Param string
        $params = "_sort=".$sort."&_sortDirection=".$sortDirection."&schoolType=".$schoolType;
      }

      // API URL
      $url = "https://www.usnews.com/best-colleges/api/search?".$params;

      if (function_exists('curl_version')) {
        // Make curl call
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.64 Safari/537.31');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_VERBOSE, true);

        $ret = curl_exec($curl); // Works!
        
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpcode != 200) {
          $httpcode = "Return code is {$httpcode} \n".curl_error($curl);
        }

        curl_close($curl);
        // Convert return into a JSON object
        $json = json_decode($ret);
      } else {
        die("Curl module not found.");
      }

      if ($json != null) {
        // Store items from this instance
        $this->items = $json->data->items;

        // Check if there are several pages
        if ($json->data->totalPages > 1) {
          // Determine if total pages have been met, may require multiple API calls
          if ($json->data->page_index != $json->data->totalPages) {
            // Check if _page key exist
            if (array_key_exists("_page", $json->data->query)) {
              $json->data->query->_page = $json->data->query->_page + 1;
              $newParams = $json->data->query;
            } else {
              // Else add param
              parse_str($params, $parsed_params);
              $parsed_params['_page'] = 2;
              $newParams = $parsed_params;
            }
            // Serialize params to string for next call
            $nextPageParams = http_build_query($newParams);
            // Sleep to throttle API calls
            sleep(1); // One second delay
            // Get next page
            $next = new GetSchools($nextPageParams);
            $this->items = array_merge($this->items, $next->items);
          }
        }
      }
    }
  }
}

// Make initial call if params are found from URL address
if (!empty($_GET['sort_by'])) {
  $data = new GetSchools();
  $response = $data->items;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>USNews Rankings Tool</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <style>
      * {
        transform: translateZ(0);
      }
      body {
        font-size: 14px;
      }
      body.loading .loading {
        display: flex;
      }
      .loading {
        position: fixed;
        width: 100vw;
        height: 100vh;
        top: 0;
        left: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background-color: rgba(0,0,0,0.5);
        color: #ffffff;
      }
      nav {
        background-color: #005A70;
        color: #ffffff;
        padding: 10px;
      }
      nav form > .row > .col-12 {
        display: flex;
        align-items: center;
      }
      nav form > .row > .col-12 label {
        display: flex;
        align-items: center;
      }
      .btn-primary {
        border-color: #0085A2;
        background-color: #0085A2;
        outline: none;
      }
      .btn-primary:focus {
        outline: none;
      }
      .btn-primary:hover {
        border-color: rgba(9, 211, 255, 1);
        background-color: rgba(9, 211, 255, 1);
      }
      .filters {
        max-width: 1200px;
      }
      .code-sample {
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
      }
      nav form > .row > .col-12 .form-group {
        width: 100%;
      }
      nav form > .row > .col-12 select {
        max-width: 100%;
        width: 100%;
      }
      table {
        width: 500px !important;
        margin: 0 auto;
      }
      table th, table td {
        text-align: left;
      }
      @media (min-width: 768px) {
        nav form > .row > .col-12 .form-group, nav form > .row > .col-12 label {
          margin-bottom: 0;
        }
        nav form > .row > .col-12 select {
          max-width: 230px;
        }
      }
    </style>
  </head>
  <body>
    <!-- Nav bar -->
    <nav class="nav justify-content-center">
      <form id="SearchForm" class="filters container-fluid">
        <div class="row">
          <div class="col-12 col-sm-12 col-md-2">
            <label>USNews Rankings Tool</label>
          </div>
          <div class="col-12 col-sm-12 col-md-4">
            <div class="form-group row">
              <label for="schoolTypeSelect" class="col-12 col-sm-4">School Type</label>
              <div class="col-12 col-sm-8">
                <select id="schoolTypeSelect" class="form-control" id="schoolType" name="schoolType">
                  <?php 
                    foreach($schoolTypeSelect as $option) {
                      $selected = ($schoolTypeSelectValue == $option['value'] ? "selected" : "");
                      echo '<option value="'.$option['value'].'" '.$selected.'>'.$option['name'].'</option>';
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-12 col-md-4">
            <div class="form-group row">
              <label for="sortBySelect" class="col-12 col-sm-4">Sort By</label>
              <div class="col-12 col-sm-8">
                <select id="sortBySelect" class="form-control" id="sort_by" name="sort_by">
                  <?php 
                    foreach($sortBySelect as $option) {
                      $selected = ($sortBySelectValue == $option['value'] ? "selected" : "");
                      echo '<option value="'.$option['value'].'" '.$selected.'>'.$option['name'].'</option>';
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-12 col-md-2 text-center">
            <button id="SubmitButton" type="submit" class="btn btn-primary btn-sm">Get Results</button>
          </div>
        </div>
      </form>
    </nav>
    <!-- Data table -->
    <div class="container">
      <div class="row">
        <div class="col-12 text-center">
          <br/>
          <p><strong>Row Count: <?php echo ($response == null ? "0" : count($response));?></strong></p>
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">School</th>
                <th scope="col">Rank</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if ($response != null) {
                  $CSV = "School,Rank|";
                  for ($i = 0; $i < count($response); $i++) {
                    $school = $response[$i];
                    $rank = $school->ranking->displayRank;
                    $listNumber = $i+1;
                    $CSV .= $school->institution->displayName.",".$rank.($i == (count($response) - 1) ? "" : "|");
                    echo '<tr>
                      <td>'.$listNumber.'</td>
                      <td>'.$school->institution->displayName.'</td>
                      <td>'.$rank.'</td>
                    </tr>';
                  }
                } else {
                  echo '<tr>
                    <td col="3">No ranks listed - '.$httpcode.'</td>
                  </tr>';
                }
              ?>
            </tbody>
          </table>
          <form action="export-csv.php" method="POST">
            <input type="hidden" name="data" value="<?php echo $CSV;?>"/>
						<button type="submit" class="btn btn-primary btn-sm">Export CSV</button>
          </form>
          <br/><br/>
        </div>
      </div>
    </div>
    <!-- Loading screen -->
    <div class="loading"><div>Getting Data...</div></div>
    <!-- JS -->
    <script>
      let $formButton = $('#SubmitButton');
      let $form = $('#SearchForm');
      $formButton.click(() => {
      console.log("Submitted");
      $('body').addClass("loading");
      $form.submit();
      });
    </script>
  </body>
</html>
