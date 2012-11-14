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
    echo \id(new \Cubex\Base\WebPage())->setTitle("Simple Application")->render();

    $partial = new \Cubex\View\Partial(
      '<div><strong>{#name}</strong> - {#title}</div>', array("name", "title")
    );
    $partial->addElement("Brooke Bryan", "CTO");
    $partial->addElement("Gareth Evans", "Director of Developer");
    echo $partial->render();

    $user = new \Cubex\Module\User\User();
    echo "User Module Version: " . $user->moduleVersion() . "\n<br/>";
    echo "User Module Name: " . $user->moduleName() . "\n<br/>";
    echo "User Module Description: " . $user->moduleDescription() . "\n<br/>";

    $usr = new \Cubex\Module\User\Model\User();
    $usr->name = 'Brooke';
    echo $usr->name;

    $menu = new \Cubex\Widgets\Menu\Widget(true);
    echo "Random String";
    $menu->addItem("Link 1", '/link1');
    $menu->addItem("Link 2", '/link2');
    echo $menu->render();

  }
}
