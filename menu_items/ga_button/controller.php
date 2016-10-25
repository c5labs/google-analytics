<?php
/**
 * Demo Helper Service Provider File.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\MenuItem\GaButton;

use Concrete\Core\Foundation\Service\Provider;
use Core;
use HtmlObject\Element;
use Page;
use Permissions;
use \Concrete\Core\Application\UserInterface\Menu\Item\Controller as ItemController;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends ItemController 
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAsset('javascript', 'google-analytics/toolbar-button');
    }

    public function displayItem()
    {
        $p = Page::getByPath('/dashboard/google-analytics');
        $cpc = new Permissions($p);

        return $cpc->canViewPage();
    }

    public function getMenuItemLinkElement()
    {
        $a = parent::getMenuItemLinkElement();
        $a->setAttribute('class', 'ga-toolbar-link');

        $child = $a->getChild(0);

        $icon = clone $child;
        $count = Element::div('')->setAttribute('id', 'gaActiveUsers');
        
        $child->setElement('div')->setAttribute('class', 'ga-icon-wrapper')->appendChild($icon)->appendChild($count);

        return $a;
    }
}