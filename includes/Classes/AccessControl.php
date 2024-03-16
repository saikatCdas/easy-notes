<?php

namespace easyNotes\Classes;

class AccessControl
{
    public static function hasTopLevelMenuPermission()
    {
        $menuPermissions = array(
            'manage_options',
            'easy-notes_full_access',
            'easy-notes_can_view_menus'
        );
        foreach ($menuPermissions as $menuPermission) {
            if (current_user_can($menuPermission)) {
                return $menuPermission;
            }
        }
        return false;
    }
}
