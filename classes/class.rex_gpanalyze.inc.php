<?php

/**
 * 
 * @author Gunter Pietzsch
 *
 */
class rex_analyzer {
  var $html_elements;
  var $modules;
  var $mod_usage;
  var $templates;
  var $temp_usage;
  var $dollar_rex_pattern;
  var $rex_pattern;
  var $db;

  function __construct() {
    $this->db = rex_sql::factory ();
    $this->html_elements = $this->modules = $this->templates = array ();
    $this->mod_usage = array ();
    $this->temp_usage = array ();
    $this->dollar_rex_pattern = '|\$REX\s*\[[\'\"](.*)[\'\"]\]|';
    $this->rex_pattern = '(REX_(\w*\[)\d{1,2}\]|REX_(\w*))';
    $this->loadModules ();
    $this->loadModUsage ();
    $this->loadTemplates ();
    $this->loadTempUsage ();
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
        'RexInput' => array (),
        'RexOutput' => array (),
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
      preg_match_all ( $this->dollar_rex_pattern, $val ['content'], $matches, PREG_PATTERN_ORDER );
      $this->temp_usage [$key] ['dollarRex'] = array_keys ( array_flip ( $matches [0] ) );
      $matches = array ();
      preg_match_all ( '|REX_TEMPLATE\[(\d*)\]|', $val ['content'], $matches, PREG_PATTERN_ORDER );
      $this->temp_usage [$key] ['templates'] = array_keys ( array_flip ( $matches [1] ) );
      $matches = array ();
      preg_match_all ( '|<\?php(.*?)\?>|', $val ['content'], $matches, PREG_PATTERN_ORDER );
      $this->temp_usage [$key] ['PHP'] = $matches [1];
    }
  }

  /**
   * load details od modules into an array
   */
  function loadModUsage() {
    foreach ( $this->modules as $key => $val ) {
      $matches = array ();
      preg_match_all ( $this->dollar_rex_pattern, $val ['eingabe'], $matches, PREG_PATTERN_ORDER );
      $this->mod_usage [$key] ['dollarRexInput'] = array_keys ( array_flip ( $matches [0] ) );
      $matches = array ();
      preg_match_all ( $this->dollar_rex_pattern, $val ['ausgabe'], $matches, PREG_PATTERN_ORDER );
      $this->mod_usage [$key] ['dollarRexOutput'] = array_keys ( array_flip ( $matches [0] ) );
      $matches = array ();
      preg_match_all ( $this->rex_pattern, $val ['eingabe'], $matches, PREG_PATTERN_ORDER );
      $this->mod_usage [$key] ['RexInput'] = array_keys ( array_flip ( $matches [0] ) );
      $matches = array ();
      preg_match_all ( $this->rex_pattern, $val ['ausgabe'], $matches, PREG_PATTERN_ORDER );
      $this->mod_usage [$key] ['RexOutput'] = array_keys ( array_flip ( $matches [0] ) );
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
    $anker = "#" . substr ( $cat->getUrl (), 1 );
    $anker = str_replace ( "/", "_", $anker );
    $anker = str_replace ( ".html", "", $anker );
    if ($anker == "#") {
      $anker .= strtolower ( $cat->getName () );
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
    return "<div id='elements'>" . join ( "\n", $this->html_elements ) . "</div>";
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
    $pieces [] = '<div id="rex-output">';
    $pieces [] = '<div class="rex-addon-output">';
    $pieces [] = '<h2 class="rex-hl2">Kategorie-Details</h2>';
    $pieces [] = '<div class="rex-addon-content">';
    $pieces [] = "<hr>";
    $pieces [] = "<h2>" . $cat->getName () . "</h2>";
    $articles = $cat->getArticles ();
    foreach ( $articles as $art ) {
      $status = ($art->isOnline () ? "online" : "offline");
      if (! $art->isStartarticle ()) {
        $pieces [] = "<h4><b>Artikel: </b>" . $art->getName () . " | <b>Id: </b>" . $art->getId () . "  | <b>Template: </b>" . $this->showTemplateName ( $art->getTemplateId () ) . " | <b>Status: </b> " . $status . "</h4>";
      } else {
        $pieces [] = "<h4><b>Startartikel: </b>" . $art->getName () . " | <b>Id: </b>" . $art->getId () . "  | <b>Template: </b>" . $this->showTemplateName ( $art->getTemplateId () ) . " | <b>Status: </b> " . $status . "</h4>";
      }
      $pieces [] = $this->getSlicesOfArticle ( $art );
      $this->temp_usage [$art->getTemplateId ()] ['articles'] [] = $art->getId ();
    }
    $pieces [] = '</div>';
    $pieces [] = '</div>';
    $pieces [] = '</div>';
    return join ( "\n", $pieces );
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
   * @return string
   */
  function getFullAnalyze() {
    $pieces = array ();
    $pieces [] = '<div id="rex-output">';
    $pieces [] = '<div class="rex-addon-output">';
    $pieces [] = '<h2 class="rex-hl2">Navigation zu den Kategorie Details</h2>';
    $pieces [] = '<div class="rex-addon-content">';
    $pieces [] = '<div id="analyze">';
    $pieces [] = '<ul class="analyze0">';
    $pieces [] = '<li><a href="#t_overview">Templates</a>';
    $pieces [] = '<li><a href="#m_overview">Modules</a>';
    $pieces [] = '</ul>';
    $pieces [] = '<ul class="analyze1">';
    foreach ( OOCategory::getRootCategories () as $lev1 ) {
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
    $pieces [] = '</div>';
    $pieces [] = '</div>';
    $pieces [] = '</div>';
    $pieces [] = '</div>';
    return join ( "\n", $pieces );
  }

  /**
   * shows all collected details of the available templates
   * @return string
   */
  function showTemplates() {
    $pieces = array ();
    $pieces [] = "<style scoped>";
    $pieces [] = "#t_overview table td.first {width:90px !important;}";
    $pieces [] = "#t_overview table td.right {text-align:right;}";
    $pieces [] = "#t_overview table td {vertical-align:top;}";
    $pieces [] = "</style>";
    $pieces [] = '<div id="rex-output">';
    $pieces [] = '<div class="rex-addon-output">';
    $pieces [] = '<h2 class="rex-hl2">Template - Übersicht</h2>';
    $pieces [] = '<div class="rex-addon-content">';
    $pieces [] = '<div id="t_overview">';
    foreach ( $this->temp_usage as $key => $val ) {
      $pieces [] = "<h4><b>Template: </b> | <b>ID: </b>" . $key . " | <b>Name: </b>" . $this->templates [$key] ['name'] . "</h4>";
      $pieces [] = '<table>';
      $pieces [] = '<tr><td class="first right"><b>$REX :</b></td>';
      $pieces [] = '<td>';
      $glue = "";
      foreach ( $val ['dollarRex'] as $v ) {
        $pieces [] = $glue . $v;
        $glue = ", ";
      }
      $pieces [] = '</td>';
      $pieces [] = "</tr>";
      $pieces [] = '<tr><td class="first right"><b>PHP :</b></td>';
      $pieces [] = '<td>';
      $glue = "";
      foreach ( $val ['PHP'] as $v ) {
        $pieces [] = $glue . $v;
        $glue = "<br>";
      }
      $pieces [] = '</td>';
      $pieces [] = "</tr>";
      $pieces [] = '<tr><td class="first right"><b>Templates :</b></td>';
      $pieces [] = '<td>';
      $glue = "";
      foreach ( $val ['templates'] as $v ) {
        $pieces [] = $glue . $this->templates [$v] ['name'];
        $glue = ", ";
      }
      $pieces [] = '</td>';
      $pieces [] = "</tr>";
      
      $pieces [] = "</table>";
      $pieces [] = "<hr>";
    }
    $pieces [] = "</div>";
    $pieces [] = '</div>';
    $pieces [] = '</div>';
    $pieces [] = '</div>';
    return join ( "\n", $pieces );
  }

  /**
   * shows all collected details of the available modules
   * @return string
   */
  function showModules() {
    $pieces = array ();
    $pieces [] = "<style scoped>";
    $pieces [] = "#m_overview table td.first {width:60px !important;}";
    $pieces [] = "#m_overview table td.right {text-align:right;}";
    $pieces [] = "#m_overview table td {vertical-align:top;}";
    $pieces [] = "</style>";
    $pieces [] = '<div id="rex-output">';
    $pieces [] = '<div class="rex-addon-output">';
    $pieces [] = '<h2 class="rex-hl2">Module - Übersicht</h2>';
    $pieces [] = '<div class="rex-addon-content">';
    $pieces [] = '<div id="m_overview">';
    foreach ( $this->mod_usage as $key => $val ) {
      $pieces [] = "<h4><b>Modul: </b> | <b>ID: </b>" . $key . " | <b>Name: </b>" . $this->modules [$key] ['name'] . " | <b>Status: </b>" . (count ( $val ['articles'] ) ? " verwendet von " . count ( $val ['articles'] ) . " Artikel(n)" : "") . "</h4>";
      
      $pieces [] = '<table>';
      $pieces [] = '<tr><td class="first"><b>Input</b></td><td></td></tr>';
      $pieces [] = '<tr><td class="first right"><b>$REX :</b></td>';
      $pieces [] = '<td>';
      $glue = "";
      foreach ( $val ['dollarRexInput'] as $v ) {
        $pieces [] = $glue . $v;
        $glue = ", ";
      }
      $pieces [] = '</td>';
      $pieces [] = "</tr>";
      $pieces [] = '<tr><td class="first right"><b>REX :</b>';
      $pieces [] = '<td>';
      $glue = "";
      foreach ( $val ['RexInput'] as $v ) {
        $pieces [] = $glue . $v;
        $glue = ", ";
      }
      $pieces [] = '</td>';
      $pieces [] = "</tr>";
      $pieces [] = '<tr><td class="first"><b>Output</b></td><td></td></tr>';
      $pieces [] = '<tr><td class="first right"><b>$REX :</b></td>';
      $pieces [] = '<td>';
      $glue = "";
      foreach ( $val ['dollarRexOutput'] as $v ) {
        $pieces [] = $glue . $v;
        $glue = ", ";
      }
      $pieces [] = '</td>';
      $pieces [] = "</tr>";
      $pieces [] = '<tr><td class="first right"><b>REX :</b></td>';
      $pieces [] = '<td>';
      $glue = "";
      foreach ( $val ['RexOutput'] as $v ) {
        $pieces [] = $glue . $v;
        $glue = ", ";
      }
      $pieces [] = '</td>';
      $pieces [] = "</tr>";
      $pieces [] = '<tr><td class="first"><b>Artikel :</b></td>';
      $pieces [] = '<td>';
      $glue = "";
      foreach ( $val ['articles'] as $v ) {
        $pieces [] = $glue . OOArticle::getArticleById ( $v )->getName ();
        $glue = ", ";
      }
      $pieces [] = '</td>';
      $pieces [] = "</tr>";
      $pieces [] = '</table>';
      $pieces [] = "<hr>";
    }
    $pieces [] = "</div>";
    $pieces [] = '</div>';
    $pieces [] = '</div>';
    $pieces [] = '</div>';
    return join ( "\n", $pieces );
  }
}
 
