<?php


namespace goldbach\CampaignTableLine\Bootstrap;


use goldbach\CampaignTableLine\Interfaces\ModuleInterface;
use goldbach\CampaignTableLine\Modules\Assets;
use goldbach\CampaignTableLine\Modules\TableCampaigns;


class Bootstrap
{
    private static ?Bootstrap $instance = null;

    /**
     * @var array|ModuleInterface
     */
    private array $modules = [];

    public static function instance(): Bootstrap {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void {
        $this->initModule(Assets::class);
        $this->initModule(TableCampaigns::class);
    }

    /**
     * @param ModuleInterface $module
     */
    private function initModule( $module ) : void
    {
        $instance = new $module();
        $instance->init();

        $this->modules[$module] = $instance;
    }
}