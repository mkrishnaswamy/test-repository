<?php
// main dir = location on server.
// foreach(folder in maindir)
// {
// folder = one folder in maindir
// make a lot
// for each file in folder/images
// make an object
// for each file in folder/forms
// add it to lot documents
// end
require("testlots.php");

      //$fc = fopen("Voices_Content_Batch_Upload_Sheet_20100112.csv","r");
      $fc = fopen("import_csv/Voices_Content_Batch_Upload_Sheet_20110207.csv","r");
      //$lot_num = 402;
      $lot_num = 0; 
      while(($data = fgetcsv($fc)) !== FALSE)
      {
	if(count(array_filter($data)) == 0) continue;
	$lot_num = $lot_num + 1;
	$first_name = $data[1];
	$middle_name = $data[2];
	$last_name = $data[0];
	$suffix = $data[3];
	$ca_id  = $data[4];
	$folder_name = $data[5];
	$transfer_date = $data[7];
	$number_of_forms = $data[8];
	$number_of_files = $data[9];
	$creation_dates = array_filter(array_slice($data,10));
// 	if($creation_dt_one) array_push($creation_dates, $creation_dt_one);
	print_r($creation_dates);


//print $folder_name;
	preg_match( '/\\\([^\\\]+)\\\?$/' , $folder_name , $filenames);
	//print "Folder_name : $folder_name: $filenames[1]\n";
 	print "\nFilename: $filenames[1]\n";

addlot($lot_num, $filenames[1], $first_name, $middle_name, $last_name, $suffix, $ca_id, $creation_dates, $transfer_date, $number_of_files, $number_of_forms);

}



?>
