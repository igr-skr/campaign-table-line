<?php


namespace goldbach\CampaignTableLine\Modules;


class Assets implements \goldbach\CampaignTableLine\Interfaces\ModuleInterface
{
    /**
     * @var array
     */
    private array $adminStyles = [];

    /**
     * @var array
     */
    private array $publicStyles = [];

    /**
     * @var array
     */
    private array $adminScripts = [];

    /**
     * @var array
     */
    private array $publicScripts = [];

    /**
     * Assets constructor.
     */
    public function __construct()
    {
        $this->publicStyles = [
            ['handle' => 'style', 'src' => '/assets/css/style.css', 'deps' => [], 'ver' => CAMPAIGN_TABLE_LINE_POSTS_VERSION],
        ];

        $this->publicScripts = [
            ['handle' => 'main_script', 'src' => '/assets/js/index.js', 'deps' => [], 'ver' => CAMPAIGN_TABLE_LINE_POSTS_VERSION]
        ];
    }

    /**
     * @inheritDoc
     */
    public function init() : void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueuePublicStyles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueuePublicScripts']);
        add_action('admin_enqueue_scripts', [$this, 'localizeScript']);
    }

    public function enqueuePublicStyles($hook)
    {
        foreach ($this->publicStyles as $style) {
            wp_enqueue_style(
                CAMPAIGN_TABLE_LINE_POSTS_PREFIX . $style['handle'] . '_public',
                CAMPAIGN_TABLE_LINE_POSTS_URL . $style['src'],
                $style['deps'],
                $style['ver']
            );
        }
    }

    public function enqueuePublicScripts($hook) {
        foreach ($this->publicScripts as $script) {
            wp_enqueue_script(
                CAMPAIGN_TABLE_LINE_POSTS_PREFIX . $script['handle'] . '_public',
                (isset($script['external']) ? '' : CAMPAIGN_TABLE_LINE_POSTS_URL) . $script['src'],
                $script['deps'],
                $script['ver'],
                isset($script['in_footer']) ? $script['in_footer'] : true
            );
        }
    }

    public function localizeScript($hook)
    {
        wp_localize_script(CAMPAIGN_TABLE_LINE_POSTS_PREFIX . 'main_script' . '_public', 'campaign_table_line_global',
            [
                'ajax' => [
                    'url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce(CAMPAIGN_TABLE_LINE_POSTS_NONCE)
                ]
            ]
        );
    }
}