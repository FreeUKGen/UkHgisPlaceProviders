<?php
/*to run this, make sure $stDataDir and $stOutputName are set to the correct
  paths, (and that you have php insalled), then do:
  php create_places_list.php
  from the command line in the directory with this php script.
*/

//input directory and output path/filename:
$stDataDir = ".";
$stOutputName = "./places_from_public_domain_data.csv";

//globals to accumulate from all files without exact duplicates
$rgCsv = Array(); // the array of output line arrays
$rgLineStrings = Array(); // used for checking for duplicates

//accumulate the data from one of the nga.mil public domain files, keep only
//the fields we want.
function load_ngs_data($stFilename, $filesrc){
  global $rgCsv;
  global $rgLineStrings;
  $numLines = 0;
  $numLinesIn = 0;
  $fin = fopen($stFilename,"rb");
  if($fin){
    echo "reading ".basename($stFilename)."\n";
    $stLine = fgets($fin);//skip the header line
    while(FALSE !== ($stLine = fgets($fin))){
      ++$numLinesIn;
      $rgLineCsv = str_getcsv($stLine,"\t");
      $lat = $rgLineCsv[3];
      $long = $rgLineCsv[4];
      $latlong = $lat.','.$long;
      //$placeid = $rgLineCsv[1];
      $src = "PD-$filesrc";
      //19: SHORT_FORM  //21: SORT_NAME_RO  //22: FULL_NAME_RO
      //23: FULL_NAME_ND_RO  //24: SORT_NAME_RG  //25: FULL_NAME_RG
      //26: FULL_NAME_ND_RG
      $rgFields=Array(19,21,22,23,24,25,26);
      foreach($rgFields as $fld){
	$name = strtolower($rgLineCsv[$fld]);
	if((''!=$name)&&(null!==$name)){
	  $lineArray = Array($name,$lat,$long,$src/*,$placeid*/);
	  $lineString = implode('',$lineArray);
	  if(!IsSet($rgLines[$lineString])){//ignore exact duplicate lines
	    $rgCsv[$numLines++] = $lineArray;
	    $rgLines[$lineString] = 1;
	  }
	}
      }	
    }
  }
  else
    echo "Could not open input file '$stFilename'\n";
  fclose($fin);
  echo " linesIn=$numLinesIn  linesOut=$numLines  (multiple spellings possible per line)\n";
}


load_ngs_data("$stDataDir/uk_administrative_a.txt.csv","a");
load_ngs_data("$stDataDir/uk_localities_l.txt.csv","l");
load_ngs_data("$stDataDir/uk_populatedplaces_p.txt.csv","p");
load_ngs_data("$stDataDir/uk_spot_s.txt.csv","s");



//create the output public domain list in csv format
$fout = fopen("$stOutputName","wb");
if($fout){
  fwrite($fout,"name,lat,long,source\n");
  foreach($rgCsv as $line){
    fputcsv($fout, $line);
  }
}
fclose($fout);

?>