<?php


namespace goldbach\CampaignTableLine\Modules;


use goldbach\CampaignTableLine\Helpers\View;
use goldbach\CampaignTableLine\Models\Campaign;

class TableCampaigns implements \goldbach\CampaignTableLine\Interfaces\ModuleInterface
{
    protected array $columns;

    public function __construct()
    {
        $this->columns = [
            [ "id" => "name", "title" => __("Name", CAMPAIGN_TABLE_LINE_POSTS_DOMAIN), "sortable" => true ],
            [ "id" => "playlist", "title" => __("Playlist", CAMPAIGN_TABLE_LINE_POSTS_DOMAIN), "sortable" => true ],
            [ "id" => "client", "title" => __("Kunde", CAMPAIGN_TABLE_LINE_POSTS_DOMAIN), "sortable" => true ],
            [ "id" => "center", "title" => __("Standort (Center)", CAMPAIGN_TABLE_LINE_POSTS_DOMAIN), "sortable" => true ],
            [ "id" => "media", "title" => __("Medien", CAMPAIGN_TABLE_LINE_POSTS_DOMAIN), "sortable" => false ],
            [ "id" => "repeats", "title" => __("Repeats/Hour", CAMPAIGN_TABLE_LINE_POSTS_DOMAIN), "sortable" => true ],
            [ "id" => "start_date_campaign", "title" => __("Start der Kampagne", CAMPAIGN_TABLE_LINE_POSTS_DOMAIN), "sortable" => true ],
            [ "id" => "end_date_campaign", "title" => __("Ende der Kampagne", CAMPAIGN_TABLE_LINE_POSTS_DOMAIN), "sortable" => true ],
            [ "id" => "reporting", "title" => __("Reporting", CAMPAIGN_TABLE_LINE_POSTS_DOMAIN), "sortable" => false ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function init() : void
    {
        add_action( 'wp_ajax_get_table_campaigns_columns', [$this, 'getTableCampaignsColumns'] );
        add_action( 'wp_ajax_get_campaigns', [$this, 'getCampaigns'] );

        add_action( 'wp_dashboard_setup', [$this, 'addDashboardWidget'] );
    }

    public function getTableCampaignsColumns()
    {
        wp_send_json_success([
            "columns" => $this->getColumns()
        ]);
    }

    public function getCampaigns()
    {
        $campaignModel = new Campaign();

        $countPerPage = $_POST['limit'] ?? 10;
        $page = $_POST['page'] ?? 1;
        $sortBy = $_POST['sortBy'] ?? 'name';
        $order = $_POST['order'] ?? 'ASC';

        $offset = ($page > 1) ? ($page - 1) * $countPerPage : 0;

        $campaigns = $campaignModel->getAllCampaigns($offset, $countPerPage, $sortBy, $order);
        $pages = ceil($campaignModel->getCount()/$countPerPage);

        wp_send_json_success([
            'campaigns' => $campaigns,
            'pages'     => $pages
        ]);
    }

    /**
     * @return array|array[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    public function addDashboardWidget()
    {
        wp_add_dashboard_widget(
            'goldbach_campaign_widget',
            __('Kampagnen-Übersicht', CAMPAIGN_TABLE_LINE_POSTS_DOMAIN),
            [$this, 'dashboardWidgetCallback']
        );
    }

    public function dashboardWidgetCallback()
    {
        echo View::render('table-campaigns-widget', [
            'page'      => $_GET['p'] ?? 1,
            'sort_by'   => $_GET['sort_by'] ?? 'name',
            'order'   => $_GET['order'] ?? 'ASC'
        ]);
    }
}