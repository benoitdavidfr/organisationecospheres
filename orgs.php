<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

//echo "<pre>";

$orgs = Yaml::parseFile('organisations.yaml');
//ksort($orgs);

class Org {
  // détection du type d'organisation au moyen d'un motif sur l'id de l'organisation
  const PATTERNS = [
    '!^administration-centrale-ou-ministere_!'=> 'AC',
    '!^ddt-!'=> 'DD',
    '!^did_routes-!'=> 'DIRID',
    '!^dir_mer-!'=> 'DIRID',
    '!^dreal-!'=> 'DR',
    '!^dreal_ut-97!'=> 'SOM',
    '!^driea-!'=> 'DIRID',
    '!^driea_ut-!'=> 'DIRID',
    '!^drihl-!'=> 'DIRID',
    '!^dtam-!'=> 'SOM',
  ];
  // code territoire en fonction de l'id de l'organisation
  const TERRITOIRES = [
    // 12 DREAL
    'dreal-45234-01'=> ['CVL'],
    'dreal-59350-01'=> ['HDF'],
    'dreal-57463-01'=> ['GES'],
    'dreal-44109-01'=> ['PDL'],
    'dreal-76540-01'=> ['NOR'],
    'dreal-86194-01'=> ['NAQ'],
    'dreal-13202-01'=> ['PAC'],
    'dreal-25056-01'=> ['BFC'],
    'dreal-2a004-01'=> ['COR'],
    'dreal-35238-01'=> ['BRE'],
    'dreal-69386-01'=> ['ARA'],
    'dreal-31555-01'=> ['OCC'],

    // DRIEAT + DRIHL 
    'driea-75115-01'=> ['IDF'],
    'driea_ut-75115-01'=> ['D75'],
    'driea_ut-92050-01'=> ['D92'],
    'driea_ut-93008-01'=> ['D93'],
    'driea_ut-94028-01'=> ['D94'],
    'drihl-75056-01'=> ['IDF'],

    // 5 DEAL
    'dreal_ut-97105-01'=> ['GLP'],
    'dreal_ut-97229-01'=> ['MTQ'],
    'dreal_ut-97302-01'=> ['GUF'],
    'dreal_ut-97411-01'=> ['REU'],
    'dreal_ut-97611-01'=> ['MYT'],
    'dtam-97502-01'=> ['SPM'],

    // 11 DIR
    'did_routes-33063-01'=> ['D16','D17','D33','D64','D79','D86'], // DIR Atlantique
    'did_routes-69383-01'=> ['D01','D03','D07','D10','D21','D26','D38','D42','D58','D69','D71','D73','D74','D89'], // DIR Centre-Est
    'did_routes-87085-01'=> ['D03','D16','D18','D19','D23','D24','D36','D37','D47','D49','D79','D86','D87'], // DIR Centre-Ouest
    'did_routes-54395-01'=> ['D25','D39','D51','D52','D54','D55','D57','D70','D88','D90'], // DIR Est
    'did_routes-63113-01'=> ['D07','D12','D15','D34','D43','D46','D48','D63'], // DIR Massif central
    'did_routes-13201-01'=> ['D04','D05','D13','D30','D34','D38','D48','D83','D84'], // DIR Méditerranée
    'did_routes-59350-01'=> ['D02','D08','D51','D59','D60','D62','D80'], // DIR Nord
    'did_routes-76540-01'=> ['D14','D27','D28','D37','D41','D50','D60','D61','D76','D78','D80'], // DIR Nord-Ouest
    'did_routes-35238-01'=> ['D22','D29','D35','D44','D49','D53','D56'], // DIR Ouest
    'did_routes-31555-01'=> ['D09','D31','D32','D33','D40','D65','D66','D81'], // DIR Sud-Ouest
    'did_routes-94028-01'=> ['D75','D77','D78','D91','D92','D93','D94','D95'], // DIRIF
    
    // DIRM
    'dir_mer-13202-01'=> ['ZM-FX-Med'], // DIRM Méditerranée
    'dir_mer-76351-01'=> ['ZM-FX-MMN'], // DIRM Manche-Est, Mer-du-Nord
    'dir_mer-33063-01'=> ['ZM-FX-Atl'], // DIRM Sud-Atlantique
    'dir_mer-44109-01'=> ['ZM-FX-Atl','ZM-FX-MMN'], // DIRM Nord-Atlantique, Manche-Ouest
    
    // AC
    'administration-centrale-ou-ministere_172160'=> ['National'], // DGALN
    'administration-centrale-ou-ministere_172170'=> ['National'], // DGITM
    'administration-centrale-ou-ministere_172153'=> ['National'], // DGPR
    'administration-centrale-ou-ministere_172165'=> ['National'], // DGEC
    'administration-centrale-ou-ministere_178624'=> ['National'], // SDES
    'administration-centrale-ou-ministere_172120'=> ['National'], // CGDD
  ];
  
  static function show(array $orgs): void {
    echo "<ul>";
    foreach ($orgs as $id => $org) {
      $type = 'none'
;      foreach ($org['extras'] as $extra) {
        if ($extra['key'] == 'Type')
          $type = $extra['value'];
      }
      //echo "<li>$org[title] (",$org['type'] ?? 'none',")</li>\n";
      echo "<li>$org[name] ($type)</li>\n";
    }
    echo "</ul>\n";
  }

  static function update(array &$orgs): void {
    foreach ($orgs as $id => &$org) {
      $done = false;
      foreach (self::PATTERNS as $pattern => $type) {
        if (preg_match($pattern, $id)) {
          $org['extras'][] = [
            'key'=> 'Type',
            'value'=> $type,
          ];
          $done = true;
          break;
        }
      }
      if (!$done) {
        echo "<pre>No match pour:\n",Yaml::dump([$id => $org]),"</pre>\n";
      }
    
      if ($type == 'DD') {
        if (!preg_match('!^ddt-(\d[\dab])!', $id, $matches))
          throw new Exception("No match sur $id");
        $ndept = $matches[1];
        $org['extras'][] = [
          'key'=> 'Territoire',
          'value'=> ["D$ndept"],
        ];
      }
      elseif (!isset(self::TERRITOIRES[$id])) {
        echo "<pre>",Yaml::dump([$id => $org]),"</pre>\n";
        throw new Exception("Aucun cas TERRITOIRE");
      }
      else {
        $org['extras'][] = [
          'key'=> 'Territoire',
          'value'=> self::TERRITOIRES[$id],
        ];
      }
    }
  }
};

switch ($_GET['action'] ?? null) {
  case null: {
    echo "<ul>\n";
    echo "<li><a href='?action=show'>afficher</a></li>\n";
    echo "<li><a href='?action=update'>mettre à jour</a></li>\n";
    echo "<li><a href='?action=save'>mettre à jour et enregistrer</a></li>\n";
    die("</ul>\n");
  }
  case 'show': {
    Org::show($orgs);
    die();
  }
  case 'update': {
    Org::update($orgs);
    Org::show($orgs);
    die();
  }
  case 'save': {
    Org::update($orgs);
    Org::show($orgs);
    file_put_contents('orgsavectype.yaml', Yaml::dump($orgs, 3, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
    die();
  }
}
