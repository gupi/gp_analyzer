<?php

/**
 * 
 * @author Gunter Pietzsch
 *
 */
class rex_analyzer {
  var $html_elements;
  var $modules;
  var $languages;
  var $mod_usage;
  var $templates;
  var $temp_usage;
  var $dollar_rex_pattern;
  var $rex_pattern;
  var $value_pattern;
  var $db;

  function __construct() {
    $this->db = rex_sql::factory ();
    $this->html_elements = $this->modules = $this->templates = array ();
    $this->mod_usage = array ();
    $this->temp_usage = array ();
    $this->dollar_rex_pattern = '|\$REX\s*\[[\'\"](.*)[\'\"]\]|';
    $this->rex_pattern = '(REX_(\w*\[)\d{1,2}\]|REX_(\w*))';
    $this->value_pattern = '|[^_](VALUE\[\d*\])|';
    $this->loadModules ();
    $this->loadModUsage ();
    $this->loadTemplates ();
    $this->loadTempUsage ();
    $this->loadLanguages ();
  }

  /**
   * laod all language records into an array;
   */
  function loadLanguages() {
    $this->languages = $this->db->getArray ( "SELECT * FROM `rex_clang` ORDER BY `id`" );
    foreach ($this->languages as $k=>$l) {
      $this->languages[$k]['new_id'] = $k+1;
    } 
  }
  /**
   * laod all modules records into an array;
   */
  function loadModules() {
    $mods = $this->db->getArray ( "SELECT * FROM `rex_module`" );
    foreach ( $mods as $val ) {
      $this->modules [$val ['id']] = $val;
      $this->mod_usage [$val ['id']] = array (
        'dollarRexInput' => array (),
        'dollarRexOutput' => array (),
        'dRI' => array (),
        'dRO' => array (),
        'RexInput' => array (),
        'RexOutput' => array (),
        'dri_line' => array (),
        'dro_line' => array (),
        'ri_line' => array (),
        'ro_line' => array (),
        'v_line' => array (),
        't_line' => array (),
        'articles' => array () 
      );
    }
  }

  /**
   * load all templates records into an array;
   */
  function loadTemplates() {
    $temps = $this->db->getArray ( "SELECT * FROM `rex_template`" );
    foreach ( $temps as $val ) {
      $this->templates [$val ['id']] = $val;
      $this->temp_usage [$val ['id']] = array (
        'dollarRex' => array (),
        'templates' => array (),
        'PHP' => array (),
        'dr_line' => array (),
        'te_line' => array (),
        'php_line' => array (),
        'articles' => array () 
      );
    }
  }

  /**
   * load details of templates into an array
   */
  function loadTempUsage() {
    foreach ( $this->templates as $key => $val ) {
      $matches = array ();
      preg_match_all ( $this->dollar_rex_pattern, $val ['content'], $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
      foreach($matches as $match) {
        $this->temp_usage [$key] ['dollarRex'][] = $match [0][0];
        $this->temp_usage [$key] ['dr_line'][$match [0][0]][] = $match [0] [1] - strlen ( str_replace ( "\n", "", substr ( $val ['content'], 0, $match [0] [1] ) ) ) + 1;
      }
      $matches = array ();
      preg_match_all ( '|REX_TEMPLATE\[(\d*)\]|', $val ['content'], $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
      foreach($matches as $match) {
        $this->temp_usage [$key] ['templates'][] = $match [0][0];
        $this->temp_usage [$key] ['te_line'][$match [0][0]][] = $match [0] [1] - strlen ( str_replace ( "\n", "", substr ( $val ['content'], 0, $match [0] [1] ) ) ) + 1;
      }
     
      $matches = array ();
      preg_match_all ( '|<\?php(.*?)\?>|', $val ['content'], $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
      foreach($matches as $match) {
        $this->temp_usage [$key] ['PHP'][] = $match [1][0];
        $this->temp_usage [$key] ['php_line'][$match [1][0]][] = $match [0] [1] - strlen ( str_replace ( "\n", "", substr ( $val ['content'], 0, $match [0] [1] ) ) ) + 1;
      }
    }    
  }

  /**
   * load details of modules into an array
   */
  function loadModUsage() {
    foreach ( $this->modules as $key => $val ) {
      $matches = array ();
      preg_match_all ( $this->dollar_rex_pattern, $val ['eingabe'], $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
      foreach($matches as $match) {
        $this->mod_usage [$key] ['dollarRexInput'][] = $match [0][0];
        $this->mod_usage [$key] ['dri_line'][$match [0][0]][] = $match [0] [1] - strlen ( str_replace ( "\n", "", substr ( $val ['eingabe'], 0, $match [0] [1] ) ) ) + 1;
        $this->mod_usage [$key] ['dRI'][] = $match [1][0] ;
      }
      $matches = array ();
      preg_match_all ( $this->dollar_rex_pattern, $val ['ausgabe'], $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
      foreach($matches as $match) {
        $this->mod_usage [$key] ['dollarRexOutput'][] = $match [0][0];
        $this->mod_usage [$key] ['dro_line'][$match [0][0]][] = $match [0] [1] - strlen ( str_replace ( "\n", "", substr ( $val ['ausgabe'], 0, $match [0] [1] ) ) ) + 1;
        $this->mod_usage [$key] ['dRO'][] = $match [1][0] ;
      }
      $matches = array ();
      preg_match_all ( $this->rex_pattern, $val ['eingabe'], $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
      foreach($matches as $match) {
        $this->mod_usage [$key] ['RexInput'][] =  $match [0][0] ;
        $this->mod_usage [$key] ['ri_line'][$match [0][0]][] = $match [0] [1] - strlen ( str_replace ( "\n", "", substr ( $val ['eingabe'], 0, $match [0] [1] ) ) ) + 1;
      }
      $matches = array ();
      preg_match_all ( $this->rex_pattern, $val ['ausgabe'], $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
      foreach($matches as $match) {
        $this->mod_usage [$key] ['RexOutput'][] =  $match [0][0] ;
        $this->mod_usage [$key] ['ro_line'][$match [0][0]][] = $match [0] [1] - strlen ( str_replace ( "\n", "", substr ( $val ['ausgabe'], 0, $match [0] [1] ) ) ) + 1;
      }
      $matches = array ();
      preg_match_all ( $this->value_pattern, $val ['eingabe'], $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
      foreach($matches as $match) {
        $this->mod_usage [$key] ['VALUE'][] = $match [1][0] ;
        $this->mod_usage [$key] ['v_line'][$match [1][0]][] = $match [1] [1] - strlen ( str_replace ( "\n", "", substr ( $val ['eingabe'], 0, $match [1] [1] ) ) ) + 1;
      }
    }
  }

  /**
   *
   * adjustment of getURL() values to required anker layout
   * e.g. /level1/level2.html => #level1_level2
   *
   * adding value to $this->html_elements (array) by calling makeElement();
   *
   * @param object $cat          
   * @return string
   */
  function makUp($cat) {
    $anker = "#" . $cat->getUrl ();
    $anker = str_replace ( "/", "_", $anker );
    $anker = str_replace ( ".html", "", $anker );
    if ($anker == "# ") {
      $anker = "#".strtolower ( $cat->getName () );
    }
    $this->makeElement ( $anker, $cat );
    return $anker;
  }

  /**
   *
   * @param string $anker          
   * @param object $cat
   *          called by makeUp()
   */
  function makeElement($anker, $cat) {
    $this->html_elements [] = "<div id='" . substr ( $anker, 1 ) . "'>";
    $this->html_elements [] = $this->makeElementDetail ( $cat );
    $this->html_elements [] = "<br>" . $this->showTopButton ();
    $this->html_elements [] = "</div>";
  }

  /**
   * provides html contents from $this->html_elements
   *
   * @return string
   */
  function showElements() {
    return wrap_rex_out("Kategorien","<div id='elements'>\n" . join ( "\n", $this->html_elements ) . "</div>\n");
  }

  /**
   * collection of all $REX_nnn variables found in $subject
   *
   * @param string $subject          
   * @return string
   */
  function showDollarREX($subject) {
    $pattern = '|\$REX\s*\[[\'\"](.*)[\'\"]\]|';
    $pieces = array ();
    $found = preg_match_all ( $pattern, $subject, $matches, PREG_PATTERN_ORDER );
    for($i = 0; $i < $found; $i ++) {
      $pieces [] = "<h6> ---> " . $matches [0] [$i] . "</h6>";
    }
    return join ( "\n", $pieces );
  }

  /**
   * collection and preparation of all REX_NNN constants and REX_VALUE/REX_HTML_VALUE/...
   * found in $subject
   *
   * @param string $subject          
   * @return string
   */
  function showREX($subject) {
    $pattern = '(REX_(\w*\[)\d{1,2}\]|REX_(\w*))';
    $pieces = array ();
    $found = preg_match_all ( $pattern, $subject, $matches, PREG_PATTERN_ORDER );
    for($i = 0; $i < $found; $i ++) {
      $pieces [] = "<h6> ---> " . $matches [0] [$i] . "</h6>";
    }
    return join ( "\n", $pieces );
  }

  /**
   * preparation of category details
   * called by makeElement()
   *
   * @param object $cat          
   * @return string
   */
  function makeElementDetail($cat) {
    $pieces = array ();
    $pieces [] = "<hr>";
    $pieces [] = "<h2>" . $cat->getName () . "</h2>";
    $articles = $cat->getArticles ();
    foreach ( $articles as $art ) {
      $status = ($art->isOnline () ? "online" : "offline");
      if (! $art->isStartarticle ()) {
        $pieces [] = "<h4><b>Artikel: </b>" . $art->getName () . " | <b>Id: </b>" . $art->getId () . "  | <b>Template: </b>" . $this->showTemplateName ( $art->getTemplateId () ) . " | <b>Status: </b> " . $status . " | <b>Sprache: </b> " .$this->languages[$art->getClang()]['name'] . "</h4>";
      } else {
        $pieces [] = "<h4><b>Startartikel: </b>" . $art->getName () . " | <b>Id: </b>" . $art->getId () . "  | <b>Template: </b>" . $this->showTemplateName ( $art->getTemplateId () ) . " | <b>Status: </b> " . $status .  " | <b>Sprache: </b> " . $this->languages[$art->getClang()]['name'] . "</h4>";
      }
      $pieces [] = $this->getSlicesOfArticle ( $art );
      $this->temp_usage [$art->getTemplateId ()] ['articles'] [] = $art->getId ();
    }
    return wrap_rex_out ( "Kategorie-Details", join ( "\n", $pieces ) );
  }

  /**
   * preparation of template name
   *
   * @param integer $id          
   * @return string
   */
  function showTemplateName($id) {
    $template = $this->db->getArray ( "SELECT * FROM `rex_template` WHERE `id`=$id" );
    return $template [0] ['name'];
  }

  /**
   * preparation of module record
   *
   * @param integer $id          
   * @return array
   */
  function showModule($id) {
    $module = $this->db->getArray ( "SELECT * FROM `rex_module` WHERE `id`=$id" );
    return $module [0];
  }

  /**
   *
   * @param object $art          
   * @return string
   */
  function getSlicesOfArticle($art) {
    $slices = OOArticleSlice::getSortedSlices ( $art->getId () );
    $pieces = array ();
    foreach ( $slices as $slice ) {
      $mid = $slice->getModuleId ();
      $mod = $this->showModule ( $mid );
      $this->mod_usage [$mid] ['articles'] [] = $art->getId ();
      $pieces [] = "<h4> - Slice: " . $slice->getId () . " | Module: " . $mod ['name'] . "</h4>";
      $pieces [] = "<h5>Modul-Eingabe</h5>";
      $pieces [] = $this->showDollarREX ( $mod ['eingabe'] );
      $pieces [] = $this->showREX ( $mod ['eingabe'] );
      $pieces [] = "<h5>Modul-Ausgabe</h5>";
      $pieces [] = $this->showDollarREX ( $mod ['ausgabe'] );
      $pieces [] = $this->showREX ( $mod ['ausgabe'] );
      $pieces [] = "<hr>";
    }
    return join ( "\n", $pieces );
  }

  function showTopButton() {
    return "<a href = '#analyze'><button class='btn btn-primary'>nach Oben</button></a>";
  }

  /**
   * provides the navigation to all details of the category/article/slice structure of the site
   * 
   * @return string
   */
  function getFullAnalyze() {
    $pieces = array ();
    $pieces [] = '<ul class="analyze0">';
    $pieces [] = '<li><a href="#l_overview">Sprachen</a>';
    $pieces [] = '<li><a href="#a_overview">Addons</a>';
    $pieces [] = '<li><a href="#t_overview">Templates</a>';
    $pieces [] = '<li><a href="#m_overview">Modules</a>';
    $pieces [] = '</ul>';
    foreach ($this->languages as $l) {
      $pieces [] = '<hr>';
      $pieces [] = '<b>'.$l['name'].'</b><br>';
      $pieces [] = '<ul class="analyze1">';
      $clang = $l['id'];
      foreach ( OOCategory::getRootCategories (FALSE,$clang) as $lev1 ) {
        $pieces [] = '<li><a href="' . $this->makUp ( $lev1 ) . '">' . $lev1->getName () . '</a>';
        if (count ( $lev1->getChildren () ) > 0) {
          $pieces [] = '<ul class="analyze2">';
          foreach ( $lev1->getChildren () as $lev2 ) {
            $pieces [] = '<li><a href="' . $this->makUp ( $lev2 ) . '">' . $lev2->getName () . '</a>'; // *******
            if (count ( $lev2->getChildren () ) > 0) {
              $pieces [] = '<ul class="analyze3">';
              foreach ( $lev2->getChildren () as $lev3 ) {
                $pieces [] = '<li><a href="' . $this->makUp ( $lev3 ) . '">' . $lev3->getName () . '</a>';
                if (count ( $lev3->getChildren () ) > 0) {
                  $pieces [] = '<ul class="analyze4">';
                  foreach ( $lev3->getChildren () as $lev4 ) {
                    $pieces [] = '<li><a href="' . $this->makUp ( $lev4 ) . '">' . $lev4->getName () . '</a>';
                  }
                  $pieces [] = '</ul>';
                }
                $pieces [] = '</li>';
              }
              $pieces [] = '</ul>';
            }
            $pieces [] = '</li>';
          }
          $pieces [] = '</ul>';
        }
        $pieces [] = '</li>';
      }
      $pieces [] = '</ul>';
    }
    return wrap_rex_out ( "Navigation zu den Details", join ( "\n", $pieces ),"analyze" );
  }

  /**
   * shows all collected details of the available templates
   * 
   * @return string
   */
  function showTemplates() {
    $style = array ();
    $style [] = "<style scoped>";
    $style [] = "#t_overview table td.first {font-weight:bold !important;}";
    $style [] = "#t_overview table td {padding:2px 5px;}";
    $style [] = "#t_overview table td {vertical-align:top;}";
    $style [] = "</style>";
    
    $pieces = array ();
    
    foreach ( $this->temp_usage as $key => $val ) {
      $pieces [] = "<h4><b>Template: </b> | <b>ID: </b>" . $key . " | <b>Name: </b>" . $this->templates [$key] ['name'] . "</h4>";
      $pieces [] = '<table>';
      foreach ($val['dr_line'] as $f=>$l) {
        $pieces [] = '<tr>';
        $pieces[] = '<td class="first">$REX</td><td>'.$f .'</td><td>Zeile: '.join("; ",$l).'</td></tr>';
        $pieces [] = "</tr>";
      }
      foreach ($val['te_line'] as $f=>$l) {
        $pieces [] = "<tr>";
        $pieces[] = "<td class='first'>TEMPLATES</td><td>".$f ."</td><td>Zeile: ".join("; ",$l)."</td></tr>";
        $pieces [] = "</tr>";
      }
      foreach ($val['php_line'] as $f=>$l) {
        $pieces [] = "<tr>";
        $pieces[] = "<td class='first'>PHP</td><td>".$f ."</td><td>Zeile: ".join("; ",$l)."</td></tr>";
        $pieces [] = "</tr>";
      }            
      $pieces [] = "</table>";
      $pieces [] = "<hr>";
    }
    $pieces [] = "<br>" . $this->showTopButton ();
    return join ( "\n", $style ) . wrap_rex_out ( "Template - Übersicht", join ( "\n", $pieces ),"t_overview" );
  }

  /**
   * shows all collected details of the available modules
   * 
   * @return string
   */
  function showModules() {
    $style = array ();
    $style [] = "<style scoped>";
    $style [] = "#m_overview table td.first {width:200px !important;}";
    $style [] = "#m_overview table td.right {text-align:right;}";
    $style [] = "#m_overview table td {vertical-align:top;}";
    $style [] = "</style>";

    $pieces = array();
    foreach($this->mod_usage as $k=>$v) {
      $pieces [] = "<b>Module: ".$this->modules[$k]['name']. "</b><br>";
      $pieces [] = "<table>";
      $pieces [] = "<thead><tr><th colspan='2'>Eingabe:</th></tr></tbody>";
      $pieces [] = "<tbody>";
      foreach ($v['dri_line'] as $f=>$l) {
        $pieces [] = "<tr>";
        $pieces[] = "<td class='first'>".$f ."</td><td>Zeile: ".join("; ",$l)."</td></tr>";
        $pieces [] = "</tr>";
      }
      foreach ($v['ri_line'] as $f=>$l) {
        $pieces [] = "<tr>";
        $pieces[] = "<td class='first'>".$f ."</td><td>Zeile: ".join("; ",$l)."</td></tr>";
        $pieces [] = "</tr>";
      }
      foreach ($v['v_line'] as $f=>$l) {
        $pieces [] = "<tr>";
        $pieces[] = "<td class='first'>".$f ."</td><td>Zeile: ".join("; ",$l)."</td></tr>";
        $pieces [] = "</tr>";
      }
      $pieces [] = "</tbody></table><hr>";
      $pieces [] = "<table>";
      $pieces [] = "<thead><tr><th colspan='2'>Ausgabe:</th></tr></tbody>";
      $pieces [] = "<tbody>";
      foreach ($v['dro_line'] as $f=>$l) {
        $pieces [] = "<tr>";
        $pieces[] = "<td class='first'>".$f ."</td><td>Zeile: ".join("; ",$l)."</td></tr>";
        $pieces [] = "</tr>";
      }
      foreach ($v['ro_line'] as $f=>$l) {
        $pieces [] = "<tr>";
        $pieces[] = "<td class='first'>".$f ."</td><td>Zeile: ".join("; ",$l)."</td></tr>";
        $pieces [] = "</tr>";
      }
      $pieces [] = "</tbody></table><hr>";
    }
    $pieces [] = "<br>" . $this->showTopButton ();
    return join ( "\n", $style ) . wrap_rex_out ( "Module - Übersicht", join ( "\n", $pieces ),"m_overview" );
  }
  function showLanguages() {
    $style = array ();
    $style [] = "<style scoped>";
    $style [] = "#l_overview table td,#l_overview table th {vertical-align:top;padding:2px 6px;}";
    $style [] = "#l_overview table td.text-center {text-align:center;}";
    $style [] = "</style>";
    
    $pieces = array();
//     $pieces [] = '<div id="l_overview">';
    
    $pieces [] = '<table>';
    $pieces [] = '<thead>';
    $pieces [] = '<tr>';
    $pieces [] = '<th>ID (r4)</th><th>Name</th><th>Rev.</th><th>ID (r5)</th>';
    $pieces [] = '</tr>';
    $pieces [] = '</thead>';
    foreach($this->languages as $k=>$l) {
      $pieces [] = '<tbody>';
      $pieces [] = '<tr>';
      $pieces [] = '<td class="text-center">'.$l['id'].'</td>';
      $pieces [] = '<td class="text-center">'.$l['name'].'</td>';
      $pieces [] = '<td class="text-center">'.($l['rev']?$l['rev']:"-").'</td>';
      $pieces [] = '<td class="text-center">'.$l['new_id'].'</td>';
      $pieces [] = '</tr>';
      $pieces [] = '</tbody>';
    }
    $pieces [] = '</table>';
//     $pieces [] = '<pre>';
//     $pieces [] = print_r($this->languages,TRUE);
//     $pieces [] = '</pre>';
    $pieces [] = "<br>" . $this->showTopButton ();
//     $pieces [] = '</div>';
    return join ( "\n", $style ) . wrap_rex_out("Sprachen - Übersicht", join ( "\n", $pieces ),"l_overview");
  }
  function showAddons() {
    GLOBAL $REX;
    $base = $REX['ADDON']['install'];
    $addons = array();
    foreach ($base as $addon=>$inst) {
      $addons [$addon]['inst'] = ($inst?"X":"");
      $addons [$addon]['act'] = (OOAddon::isActivated($addon)?"X":"");
      $addons [$addon]['sys'] = (OOAddon::isSystemAddon($addon) ?"X":"");
      $addons [$addon]['autor'] = OOAddon::getAuthor($addon);
    }

    $style = array ();
    $style [] = "<style scoped>";
    $style [] = "#a_overview table td,#a_overview table th {vertical-align:top;padding:2px 6px;}";
    $style [] = "#a_overview table td.text-center {text-align:center;}";
    $style [] = "</style>";
    
    $pieces = array();
    $pieces [] = "<table>";
    $pieces [] = "<thead><tr><th>Addon</th><th>Installiert</th><th>Aktiviert</th><th>System</th><th>Author</th></tr></thead>";
    $pieces [] = "<tbody>";
    foreach($addons as $key=>$val) {
      $pieces [] = "<tr>";
      $pieces [] = "<td><b><i>".$key."</i></b></td>";
      $pieces [] = "<td class='text-center'>".$val['inst']."</td>";
      $pieces [] = "<td class='text-center'>".$val['act']."</td>";
      $pieces [] = "<td class='text-center'>".$val['sys']."</td>";
      $pieces [] = "<td>".$val['autor']."</td>";
      $pieces [] = "</tr>";
    }
    $pieces [] = "</tbody></table>";
    $pieces [] = "<br>" . $this->showTopButton ();
    return join ( "\n", $style ) . wrap_rex_out("Addons - Übersicht", join ( "\n", $pieces ),"a_overview");
  }
}
 