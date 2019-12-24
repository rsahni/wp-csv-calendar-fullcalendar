<?php
global $wpdb;

// Table name
$tablename = $wpdb->prefix."timetable_calendar";

// Import CSV
if(isset($_POST['butimport'])){

  // File extension
  $extension = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_EXTENSION);

  // If file extension is 'csv'
  if(!empty($_FILES['fileToUpload']['name']) && $extension == 'csv'){

    $totalInserted = 0;
    $totalUpdatedRows = 0;

    // Open file in read mode
    $csvFile = fopen($_FILES['fileToUpload']['tmp_name'], 'r');

    fgetcsv($csvFile); // Skipping header row

    // Read file
    while(($filesop = fgetcsv($csvFile, 1000, ",")) !== FALSE){
        // Assign value to variables
        $eventTitle = $filesop[0];
        $startDateTime = $filesop[1] . ' ' . date("H:i:s", strtotime($filesop[2]));
        $endDateTime = $filesop[3] . ' ' . date("H:i:s", strtotime($filesop[4]));
   
        // Check record already exists or not
        $cntSQL = "SELECT count(*) as count FROM {$tablename} where startDateTime='".$startDateTime."'";
        $record = $wpdb->get_results($cntSQL, OBJECT);

        if($record[0]->count==0){
            // Check if variable is empty or not
            if(!empty($eventTitle) && !empty($startDateTime) && !empty($endDateTime) ) {

                // Insert Record
                $wpdb->insert($tablename, array(
                    'eventTitle' =>$eventTitle,
                    'startDateTime' =>$startDateTime,
                    'endDateTime' =>$endDateTime
                ));

                if($wpdb->insert_id > 0){
                    $totalInserted++;
                }
            }
        }else{
            // Check if variable is empty or not
            if(!empty($eventTitle) && !empty($startDateTime)) {
    
                // Update Record
                $result = $wpdb->update($tablename, array('eventTitle' =>$eventTitle), array('startDateTime'=>$startDateTime));
       
                if($result !== false){
                    $totalUpdatedRows++;
                }
            }
        }
    }

    echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";
    echo "<h3 style='color: green;'>Total record Updated : ".$totalUpdatedRows."</h3>";
    
  }else{
    echo "<h3 style='color: red;'>Invalid Extension</h3>";
  }

}

if(isset($_POST['deleteImport'])){
    // File extension
  $extension = pathinfo($_FILES['deleteFileToUpload']['name'], PATHINFO_EXTENSION);

  // If file extension is 'csv'
  if(!empty($_FILES['deleteFileToUpload']['name']) && $extension == 'csv'){

    $totalDeleted = 0;

    // Open file in read mode
    $csvFile = fopen($_FILES['deleteFileToUpload']['tmp_name'], 'r');

    fgetcsv($csvFile); // Skipping header row

    // Read file
    while(($filesop = fgetcsv($csvFile, 1000, ",")) !== FALSE){
        // Assign value to variables
        $eventTitle = $filesop[0];
        $startDateTime = $filesop[1] . ' ' . date("H:i:s", strtotime($filesop[2]));

        // Check record already exists or not
        $cntSQL = "SELECT count(*) as count FROM {$tablename} where startDateTime='".$startDateTime."'";
        $record = $wpdb->get_results($cntSQL, OBJECT);

        if($record[0]->count > 0){

            // Check if variable is empty or not
            if(!empty($eventTitle) ) {
                // Delete Record
                $result = $wpdb->delete($tablename, array('eventTitle' =>$eventTitle, 'startDateTime'=>$startDateTime));
                if($result !== false){
                    $totalDeleted++;
                }else{
                    echo 'error: ' . $wpdb->last_error;
                }
            }
        }
    }

    echo "<h3 style='color: red;'>Total record Deleted : ".$totalDeleted."</h3>";
    
  }else{
    echo "<h3 style='color: red;'>Invalid Extension</h3>";
  }
}

?>

<h2>Upload CSV to Insert or Update Calendar</h2>
<!-- Form -->
<form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
  <input type="file" name="fileToUpload" id="fileToUpload" >
  <input type="submit" name="butimport" value="Import">
</form>
<h2>Upload CSV to Delete Entry From Calendar</h2>
<!-- Delet Form -->
<form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
    <input type="file" name="deleteFileToUpload" id="deleteFileToUpload" >
    <input type="submit" name="deleteImport" value="Delete">
</form>

<h2>All Entries</h2>
<!-- Record List -->
<table width='100%' border='1' style='border-collapse: collapse;'>
   <thead>
   <tr>
     <th>ID</th>
     <th>Event Title</th>
     <th>Start Date Time</th>
     <th>End Date Time</th>
     <th>Created on</th>
   </tr>
   </thead>
   <tbody>
   <?php
   // Fetch records
   $entriesList = $wpdb->get_results("SELECT * FROM ".$tablename." order by id desc");
   if(count($entriesList) > 0){
     $count = 0;
     foreach($entriesList as $entry){
        $id = $entry->id;
        $eventTitle = $entry->eventTitle;
        $startDateTime = $entry->startDateTime;
        $endDateTime = $entry->endDateTime;
        $createdOn = $entry->createdOn;

        echo "<tr>
        <td>".++$count."</td>
        <td>".$eventTitle."</td>
        <td>".$startDateTime."</td>
        <td>".$endDateTime."</td>
        <td>".$createdOn."</td>
        </tr>
        ";
     }
   }else{
     echo "<tr><td colspan='5'>No record found</td></tr>";
  }
  ?>
  </tbody>
</table>