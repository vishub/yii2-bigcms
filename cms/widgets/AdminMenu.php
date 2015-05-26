<?php
/**
 * @link http://www.bigbrush-agency.com/
 * @copyright Copyright (c) 2015 Big Brush Agency ApS
 * @license http://www.bigbrush-agency.com/license/
 */

namespace cms\components;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;

/**
 * AdminMenu
 */
class AdminMenu extends Object
{
    /**
     * @var array list of menu items.
     */
    private $_items = [];


    /**
     * Initializes this widget by setting parent properties.
     */
    public function init()
    {
        $this->createItems();
        $this->addCollapseItem();
    }

    /**
     * Renders this widget.
     *
     * @return string the rendering result of this widget.
     */
    public function run()
    {
        $this->getView()->registerJs('
            var wrapper = $("#wrapper"),
                menuToggler = $("#menu-toggler"),
                icon = menuToggler.find(".fa");

            function toggleIcon() {
                if (icon.hasClass("fa-arrow-circle-left")) {
                    icon.removeClass("fa-arrow-circle-left");
                    icon.addClass("fa-arrow-circle-right");
                } else {
                    icon.removeClass("fa-arrow-circle-right");
                    icon.addClass("fa-arrow-circle-left");
                }
            }
            
            if(wrapper.hasClass("toggled")) {
                $(".menuitem-text").hide();
            }

            menuToggler.click(function(e) {
                e.preventDefault();
                wrapper.toggleClass("toggled");
                if(wrapper.hasClass("toggled")) {
                    $.get("'.Url::to(['/big/cms/remember-show-sidebar', 'show' => false]).'");
                    $(".menuitem-text").hide();
                    toggleIcon();
                } else {
                    $.get("'.Url::to(['/big/cms/remember-show-sidebar', 'show' => true]).'");
                    $(".menuitem-text").show();
                    toggleIcon();
                }
            });
        ');
        $html = [];
        $html[] = '<div id="adminmenu" class="list-group">';
        $html[] = Nav::widget([
            'items' => $this->_items,
            'options' => ['class' => 'menu'],
            'encodeLabels' => false,
        ]);
        $html[] = '</div>';
        return  implode("\n", $html);
    }

    /**
     * Creates default menu items used in the admin menu.
     *
     * @return array configuration for menu items.
     */
    public function createItems()
    {
        // if no user is logged in hide the menu.
        $userLoggedIn = !Yii::$app->getUser()->getIsGuest();
        if (Yii::$app->getUser()->getIsGuest()) {
            $item = ['label' => '<i class="fa fa-home fa-fw"></i> Welcome', 'url' => ['/']];
            return $this->addItem($item);
        }

        $itemsConfig = [
            ['label' => 'Home', 'url' => ['/'], 'icon' => 'home'],
            ['label' => 'Pages', 'url' => ['/pages/page/index'], 'icon' => 'file',
                // 'items' => [
                //     ['label' => 'Categories', 'url' => ['/pages/categories/index'], 'icon' => 'square', 'options' => ['class' => 'pull-right']],
                // ]
            ],
            ['label' => 'Blocks', 'url' => ['/big/block/index'], 'icon' => 'square'],
            ['label' => 'Menus', 'url' => ['/big/menu/index'], 'icon' => 'bars'],
            ['label' => 'Media', 'url' => ['/big/media/show'], 'icon' => 'picture-o'],
            ['label' => 'Templates', 'url' => ['/big/template/index'], 'icon' => 'simplybuilt'],
            ['label' => 'Users', 'url' => ['/big/user/index'], 'icon' => 'users'],
            ['label' => 'Logout', 'url' => ['/big/frontpage/logout'], 'icon' => 'circle-o-notch'],
        ];

        foreach ($itemsConfig as $item) {   
            $this->addItem($item); 
        }
    }

    /**
     * Creates an item for the menu.
     *
     * @return array
     */
    public function additem($item)
    {
        $icon = $item['icon'];
        unset($item['icon']);
        Html::addCssClass($item['options'], 'list-group-item');

        $item['label'] = '<i class="fa fa-'.$icon.' fa-fw"></i><span class="menuitem-text"> ' . $item['label'] . '</span>';

        // if module and controller of the current route matches the first part of the item
        // url set current item as active.
        $pathInfo = Yii::$app->getRequest()->getPathInfo();
        $route = '/' . substr($pathInfo, 0, strrpos($pathInfo, '/'));
        $url = $item['url'][0];
        if (($route === $url) || ($route !== '/' && strpos($url, $route) === 0)) {
            $item['active'] = true;
        }
        $this->_items[] = $item;
    }

    /**
     * Returns the collapse menu item.
     *
     * @return string the collapse menu item.
     */
    public function addCollapseItem()
    {
        if (Yii::$app->getSession()->get('__app_show_sidebar__', true)) {
            $collapseIcon = 'arrow-circle-left';
        } else {
            $collapseIcon = 'arrow-circle-right';
        }
        $item = ['label' => 'Collapse', 'url' => '#', 'icon' => $collapseIcon, 'options' => ['id' => 'menu-toggler']];
        return $this->addItem($item);
    }
}