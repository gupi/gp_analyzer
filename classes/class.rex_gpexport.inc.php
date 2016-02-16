<?php
/**
 *
 * @author Gunter Pietzsch
 *
 */
class rex_exporter {
  var $source_tables;
  var $dest_tables;
  var $source_structures;
  var $dest_structures;
  var $mapping_table;
  var $mapping_dollar_rex;
  var $analyze;
  var $db;
  
  function __construct() {
  	$this->db = rex_sql::factory();
  	$this->source_tables = array(rex_article,rex_article_slice,rex_template,rex_module,rex_module_action);
  	$this->dest_tables = array(r4_article,r4_article_slice,r4_template,r4_module,r4_module_action);
  	$this->createDestination(TRUE);
    foreach($this->source_tables as $table){
  		$this->loadSourceTableStructure($table);
  	}
    foreach($this->dest_tables as $table){
  		$this->loadDestTableStructure($table);
  	}
  	$this->setupMapping();
  	$this->doTableMapping();
  	$this->dest_structures = array();
  	foreach($this->dest_tables as $table){
  	  $this->loadDestTableStructure($table);
  	}
  	$this->analyze = new rex_analyzer();
  	$this->setSlicePriority();
  }
  
  function setupMapping() {
  	$this->mapping_table = array();
  	$this->mapping_table ["r4_article"][]= array("attributes","drop");
  	$this->mapping_table ["r4_article"][]= array("re_id","parent_id");
  	$this->mapping_table ["r4_article"][]= array("startpage","startarticle");
  	$this->mapping_table ["r4_article"][]= array("prior","priority");
  	$this->mapping_table ["r4_article"][]= array("clang","clang_id");
  	$this->mapping_table ["r4_article_slice"][]= array("re_article_slice_id","priority");
  	$this->mapping_table ["r4_article_slice"][]= array("next_article_slice_id","drop");
  	$this->mapping_table ["r4_article_slice"][]= array("clang","clang_id");
  	$this->mapping_table ["r4_article_slice"][]= array("ctype","ctype_id");
  	$this->mapping_table ["r4_article_slice"][]= array("file1","media1");
  	$this->mapping_table ["r4_article_slice"][]= array("file2","media2");
  	$this->mapping_table ["r4_article_slice"][]= array("file3","media3");
  	$this->mapping_table ["r4_article_slice"][]= array("file4","media4");
  	$this->mapping_table ["r4_article_slice"][]= array("file5","media5");
  	$this->mapping_table ["r4_article_slice"][]= array("file6","media6");
  	$this->mapping_table ["r4_article_slice"][]= array("file7","media7");
  	$this->mapping_table ["r4_article_slice"][]= array("file8","media8");
  	$this->mapping_table ["r4_article_slice"][]= array("file9","media9");
  	$this->mapping_table ["r4_article_slice"][]= array("file10","media10");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist1","medialist1");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist2","medialist2");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist3","medialist3");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist4","medialist4");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist5","medialist5");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist6","medialist6");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist7","medialist7");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist8","medialist8");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist9","medialist9");
  	$this->mapping_table ["r4_article_slice"][]= array("filelist10","medialist10");
  	$this->mapping_table ["r4_article_slice"][]= array("modultype_id","module_id");
  	$this->mapping_table ["r4_module"][] = array("eingabe","input");
  	$this->mapping_table ["r4_module"][]= array("ausgabe","output");
  	$this->mapping_table ["r4_module"][]= array("category_id","drop");
  	$this->mapping_table ["r4_template"][]= array("label","drop");

  	$this->mapping_dollar_rex = array();
  	$this->mapping_dollar_rex ["REDAXO"] = 'rex::isBackend()';
  	$this->mapping_dollar_rex ["SERVER"] =	'rex::getServer()';
  	$this->mapping_dollar_rex ["ERROR_EMAIL"] =	'rex::getErrorEmail() ';
  	$this->mapping_dollar_rex ["HTDOCS_PATH"] =	'rex_path::base()';
  }
  function setSlicePriority() {
    $sql = "SELECT `id`,`article_id`,`priority` FROM `r4_article_slice` ORDER BY`article_id`,`priority` DESC";
    $slices = $this->db->getArray($sql);
    $pio = array();
    $pri = 0;
    $art = $slices[0]['article_id'];
    foreach ($slices as $k=>$v) {
      if ($v['article_id'] == $art) {
        $pri++;
      } else {
        $pri = 1;
        $art = $v['article_id'];
      }
      $slices[$k]['priority'] = $pri;
      $this->db->getArray("UPDATE `r4_article_slice` SET `priority`=".$pri." WHERE `id`=".$v['id']);
    }
//     return "<pre>".print_r($slices,TRUE)."</pre>";
    return;
  }
  function showDetail($title,$string) {
    return wrap_rex_output($title, $string);
  }
  function exportTable($table) {
    GLOBAL $REX;
    $filename = $REX['HTDOCS_PATH']."/files/gp_export/".$table."_".date("YmdHis").".csv";
    if($file = fopen($filename,"w")){
      $vals=$this->db->getArray("SELECT * FROM `".$table."`",MYSQL_NUM);
      $fields = $this->db->getFieldnames();
      fputcsv($file, $fields);
      foreach ($vals as $v) {
        fputcsv($file, $v);
      }
      fclose($file);
      return "Datei: ".$filename." wurde angelegt.";
    } else {
      return "Datei: ".$filename." konnte nicht angelegt werden!";
    }
  }
  function createDestination($w_data=true) {
  	foreach($this->source_tables as $k=>$v) {
  	  $this->db->setQuery("DROP TABLE IF EXISTS `".$this->dest_tables[$k]."`;");
  	  $this->db->setQuery("CREATE TABLE `".$this->dest_tables[$k]."` LIKE `".$v."`;");
  	  if ($w_data) {
  	    $this->db->setQuery("INSERT ".$this->dest_tables[$k]." SELECT * FROM `".$v."`;");
  	  }
  	}
  }
  function loadSourceTableStructure($table) {
  	$struc = $this->db->getArray("SHOW COLUMNS FROM `".$table."`;");
  	foreach ($struc as $v) {
  	  $this->source_structures[$table][$v['Field']] = $v;
//   	  unset($this->source_structures[$table][$v['Field']]['Field']);
  	}
  	return ;
  }
  function loadDestTableStructure($table) {
  	$struc = $this->db->getArray("SHOW COLUMNS FROM `".$table."`;");
  	foreach ($struc as $v) {
  	  $this->dest_structures[$table][$v['Field']] = $v;
//   	  unset($this->source_structures[$table][$v['Field']]['Field']);
  	}
  	return ;
  }
  function showTableStructure($table) {
//   	return array($this->source_structures[$table]);
  	return array($this);
  }
  function doTableMapping() {
  	foreach ($this->mapping_table as $k=>$v) {
  	  foreach ($v as $map) {
  	  	if($map[1] == "drop") {
  	  	  $this->db->setQuery("ALTER TABLE `".$k."` DROP COLUMN `".$map[0]."`;");
  	  	} else {
  	  	  $typ =$this->dest_structures[$k][$map[0]]['Type'];
  	  	  $this->db->setQuery("ALTER TABLE `".$k."` CHANGE COLUMN `".$map[0]."` `".$map[1]."` ".$typ.";");
  	  	}
  	  }
  	}
  }
  function showDbTables($show_source) {
    $pieces = array ();
    if ($show_source) {
      $source = $this->source_tables;
      $title = "Quell-Tabellen";
    } else {
      $source = $this->dest_tables;
      $title = "Ziel-Tabellen";
    }
    foreach ($source as $table) {
      $pieces [] = $table."<br>";
    }
    return wrap_rex_output($title, join ( "\n", $pieces ));    
  }
  function showTableAdjustments() {
    $style = array ();
    $style [] = "<style scoped>";
    $style [] = "#a_overview table th {min-width:100px !important;}";
    $style [] = "#a_overview table td {min-width:100px !important;}";
    $style [] = "#a_overview table td {vertical-align:top;}";
    $style [] = "</style>";

    $pieces = array ();
    $pieces [] = "<div id='a_overview'>";
    $pieces [] = "<table>";
    $pieces [] = "<thead>";
    $pieces [] = "<tr>";
    $pieces [] = "<th>Tabelle</th><th>R4 Feldname</th><th>R5 Feldname</th>";
    $pieces [] = "</tr>";
    $pieces [] = "</thead>";
    $pieces [] = "<tbody>";
    foreach ($this->mapping_table as $k=>$v) {
      foreach ($v as $tab) {
      $pieces [] = "<tr>";
      $pieces [] = "<td>".$k."</td><td>".$tab[0]."</td><td>".($tab[1] != "drop"?$tab[1]:"*** Feld wird gelöscht ***")."</td>";
      $pieces [] = "</tr>";
      }
    }
    $pieces [] = "</tbody>";
    $pieces [] = "</table>";
    $pieces [] = "</div>";
    return join ( "\n", $style ).wrap_rex_output("Tabellen-Anpassungen", join ( "\n", $pieces ));    
  }
  function showDollarRexAdjustments() {
    $style = array ();
    $style [] = "<style scoped>";
    $style [] = "#d_overview table th {min-width:200px !important;}";
    $style [] = "#d_overview table td {min-width:200px !important;}";
    $style [] = "#d_overview table td {vertical-align:top;}";
    $style [] = "</style>";

    $pieces = array ();
    $pieces [] = "<div id='d_overview'>";
    $pieces [] = "<table>";
    $pieces [] = "<thead>";
    $pieces [] = "<tr>";
    $pieces [] = "<th>R4 Variable</th><th>R5 Variable</th>";
    $pieces [] = "</tr>";
    $pieces [] = "</thead>";
    $pieces [] = "<tbody>";
    foreach ($this->mapping_dollar_rex as $k=>$v) {
      $pieces [] = '<tr>';
      $pieces [] = '<td>$REX["'.$k.'"]</td><td>'.$v.'</td>';
      $pieces [] = '</tr>';
    }
    $pieces [] = "</tbody>";
    $pieces [] = "</table>";
    $pieces [] = "</div>";
    return join ( "\n", $style ).wrap_rex_output('$REX-Anpassungen', join ( "\n", $pieces ));    
  }
  function showDollarRexUsage() {
    $pieces = array ();
    $dr1 = array();
    $dr2 = array();
    foreach($this->analyze->mod_usage as $val) {
      foreach($val['dollarRexInput'] as $v) {
        $dr1[] = $v;
      }
      foreach($val['dollarRexOutput'] as $v) {
        $dr1[] = $v;
      }
    }
    foreach($this->analyze->temp_usage as $val) {
      foreach($val['dollarRex'] as $v) {
        $dr2[] = $v;
      }
    }
    $pieces [] = "<pre>";
    $pieces [] = "Modules: \n";
    $pieces [] = print_r(array_keys ( array_flip ( $dr1 ) ),true);
    $pieces [] = "Templates: \n";
    $pieces [] = print_r(array_keys ( array_flip ( $dr2 ) ),true);
    $pieces [] = "/<pre>";

    return join ( "\n", $style ).wrap_rex_output('$REX-Usage', join ( "\n", $pieces ));
  }
}