<?php 

namespace Modules\Visa;

use Modules\Visa\Models\VisaApplication;

class Hook
{
    static $inst;

    public static function init()
    {
        if (!static::$inst) {
            static::$inst = new self();
        }
        return static::$inst;
    }

    /**
     * Add hooks to dashboard widget
     */
    public function dashboard_widget()
    {
        add_action('dashboard_widget', function () {
            echo view('Visa::frontend.dashboard-widget');
        }, 9);
    }

    /**
     * Add admin menu
     */
    public function admin_menu()
    {
        add_action('admin_menu', function () {
            // Admin menu items
        });
    }

    /**
     * Add user menu
     */
    public function user_menu()
    {
        add_action('user_menu', function () {
            // User menu items
        });
    }

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        $this->dashboard_widget();
        $this->admin_menu();
        $this->user_menu();
    }
}

Hook::init()->add_hooks();
