<?php
/**
 * User: brooke.bryan
 * Date: 25/10/12
 * Time: 23:16
 * Description:
 */

namespace Cubex\Application\Simple;

class defaultController extends \Cubex\Base\Controller
{

  public function runPage()
  {
    $webpage = new \Cubex\Base\WebPage();
    $webpage->setTitle("Simples Application");
    $webpage->beginCapture();

    $partial = new \Cubex\View\Partial(
      '<div><strong>{#name}</strong> - {#title}</div>', array("name", "title")
    );
    $partial->addElement("Brooke Bryan", "CTO");
    $partial->addElement("Gareth Evans", "Director of Developer");
    echo $partial->render();

    $partial = new \Cubex\View\Partial('<div><strong>%s</strong>: %s</div>');
    $partial->addElement("Brooke Bryan", "CTO");
    $partial->addElement("Gareth Evans", "Director of Developer");
    echo $partial->render();

    $user = new \Cubex\Module\User\User();
    $user->authenticate('user','password');

    echo "User Module Version: " . $user->moduleVersion() . "\n<br/>";
    echo "User Module Name: " . $user->moduleName() . "\n<br/>";
    echo "User Module Description: " . $user->moduleDescription() . "\n<br/>";

    $usr       = new \Cubex\Module\User\Model\User();
    $usr->name = ' Brooke ';
    $usr->username = 'brooke@bajb.net';

    //$usr->saveChanges();
    //$usr->delete();

    echo $usr;
    if($usr->isValid()) echo " - <strong>All Valid</strong>";
    else echo " - <strong>Invalid</strong>";

    echo "\n<br/>";
    $usr->loadAllWhere("%C = %s", "name", "brooke");
    echo "\n<br/>";

    $o = new \Cubex\Data\SearchObject();
    $o->name = 'Davide';
    $o->setMatchType("name",$o::MATCH_LIKE);
    $o->addSearch("username","brooke",$o::MATCH_END);
    $usr->loadMatches($o);

    $menu = new \Cubex\Widgets\Menu\Widget(true);
    echo "Random String";
    $menu->addItem("Link 1", '/link1');
    $menu->addItem("Link 2", '/link2');
    echo $menu->render();

    $redirect = new \Cubex\Http\Redirect('http://www.google.com');

    new \Cubex\Http\Response($webpage);
  }
}
