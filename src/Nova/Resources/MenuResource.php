<?php

namespace OptimistDigital\MenuBuilder\Nova\Resources;

use Laravel\Nova\Panel;
use Laravel\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use OptimistDigital\MenuBuilder\MenuBuilder;
use OptimistDigital\MenuBuilder\Models\Menu;
use OptimistDigital\MenuBuilder\Nova\Fields\MenuBuilderField;

class MenuResource extends Resource
{
    public static $model = Menu::class;
    public static $search = ['name', 'slug'];
    public static $displayInNavigation = false;

    public static function label()
    {
        return __('novaMenuBuilder.menuResourceLabel');
    }

    public static function singularLabel()
    {
        return __('novaMenuBuilder.menuResourceSingularLabel');
    }

    public static function uriKey()
    {
        return 'nova-menus';
    }

    public function title()
    {
        return $this->name . ' (' . $this->slug . ')';
    }

    public function fields(Request $request)
    {
        $menusTableName = MenuBuilder::getMenusTableName();
        $menuOptions = collect(MenuBuilder::getMenus())
            ->mapWithKeys(function ($menu, $key) {
                return [$key => $menu['name']];
            })
            ->toArray();

        return [
            Text::make(__('novaMenuBuilder.nameFieldName'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make(__('novaMenuBuilder.menuResourceSingularLabel'), 'slug')
                ->options($menuOptions)
                ->onlyOnForms()
                ->creationRules('required', 'max:255', "unique:$menusTableName,slug,NULL,id")
                ->updateRules('required', 'max:255', "unique:$menusTableName,slug,{{resourceId}},id"),

            Text::make(__('novaMenuBuilder.menuResourceSingularLabel'), 'slug', function ($key) {
                $menu = MenuBuilder::getMenus()[$key] ?? null;
                return ($menu === null) ? "<s>{$key}</s>" : $menu['name'];
            })
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->asHtml(),

            Panel::make(__('novaMenuBuilder.menuItemsPanelName'), [
                MenuBuilderField::make('', 'menu_items')
                    ->hideWhenCreating()
                    ->readonly(),
            ])
        ];
    }
}
