<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

//echo "<pre>";

$orgs = Yaml::parseFile('organisations.yaml');
//ksort($orgs);

class Org {
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
