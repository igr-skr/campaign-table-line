<?php


namespace goldbach\CampaignTableLine\Models;


class Campaign
{
    protected string $postType = 'playlist';

    protected array $data = [];

    public function getAllCampaigns(int $offset = 0, int $limit = 10, string $orderBy = 'name', string $orderMode = 'ASC') : array
    {
        global $wpdb;

        $playlists = $wpdb->get_results( "
            SELECT ID, post_title
            FROM {$wpdb->prefix}posts
            WHERE post_type = 'playlist'
        ", ARRAY_A );

        foreach ($playlists as $playlist) {

            $playlistLists = get_field('playlist_list', $playlist['ID']);

            foreach ($playlistLists as $playlistList) {
                $campaign = [];

                $currentDate = strtotime(date('Ymd'));
                $startDate = $playlistList['period'][0]['startdate'];
                $endDate = $playlistList['period'][0]['enddate'];

                if ($currentDate >= strtotime($startDate) && $currentDate <= strtotime($endDate)) {

                    if (!$playlistList['kamp_name']) continue;

                    $campaignId = $playlistList['kamp_name'][0];
                    $campaign['campaign_edit_link'] = get_edit_term_link($campaignId, 'kampagnen');
                    $campaign['name'] = get_term($campaignId)->name;

                    $campaign['playlist_edit_link'] = get_edit_post_link($playlist['ID']);
                    $campaign['playlist'] = $playlist['post_title'];

                    $clientId = $playlistList['kunden'][0];
                    $campaign['client_edit_link'] = get_edit_term_link($clientId);
                    $campaign['client'] = get_term($clientId)->name;

                    $locDevices = get_posts(array(
                        'post_type' => 'devices',
                        'meta_key'      => 'device_playlist',
                        'meta_value'    => $playlist['ID']
                    ));

                    foreach ($locDevices as $locDevice) {
                        $centerId = get_post_meta($locDevice->ID,'device_center')[0];
                        $campaign['center_edit_link'] = get_edit_post_link($centerId);
                        $campaign['center'] = get_post_meta($centerId, 'center_name')[0];

                        $reportDate = strtotime($endDate) < strtotime($currentDate) ? $endDate : date('Y-m-d');
                        $campaign['report_link'] = 'https://dooh.mall-cockpit.de/wp-content/plugins/mall-cockpit-device/player/report/index.php?date=' . $reportDate . '&media_id=&device_id=' . $locDevice->ID . '&center_id=' . $centerId . '&s=';
                    }

                    $mediaId = $playlistList['file']['ID'];
                    $campaign['media'] = wp_get_attachment_image_url($mediaId,'thumbnail');
                    $campaign['media_icon'] = wp_get_attachment_image($mediaId,'thumbnail',true);
                    $campaign['repeats'] = $playlistList['period'][0]['repeats_per_hour'];
                    $campaign['start_date_campaign'] = $startDate;
                    $campaign['end_date_campaign'] = $endDate;

                }

                if (!empty($campaign)) array_push($this->data, $campaign);
            }
        }

        $data = $this->getData();

        return array_slice($this->sort($data, $orderBy, $orderMode), $offset, $limit);
    }

    public function getCount() : int
    {
        return count($this->getData());
    }

    protected function sort(array $data, string $orderBy = 'name', string $orderMode = 'ASC') : array
    {
        if ($orderBy == 'name') {
            usort($data, function($a, $b) { return strnatcmp($a["name"], $b["name"]) ; });
        } else if ($orderBy == 'playlist') {
            usort($data, function($a, $b) { return strnatcmp($a["playlist"], $b["playlist"]); });
        } else if ($orderBy == 'client') {
            usort($data, function($a, $b) { return strnatcmp($a["client"], $b["client"]); });
        } else if ($orderBy == 'center') {
            usort($data, function($a, $b) { return strnatcmp($a["center"], $b["center"]); });
        } else if ($orderBy == 'repeats') {
            usort($data, function($a, $b) {
                if ($a['repeats'] == $b['repeats']) return 0;
                return ($a['repeats'] < $b['repeats']) ? -1 : 1;
            });
        } else if ($orderBy == 'start_date_campaign') {
            usort($data, function($a, $b) {
                if ($a['start_date_campaign'] == $b['start_date_campaign']) return 0;
                return ($a['start_date_campaign'] < $b['start_date_campaign']) ? -1 : 1;
            });
        } else if ($orderBy == 'end_date_campaign') {
            usort($data, function($a, $b) {
                if ($a['end_date_campaign'] == $b['end_date_campaign']) return 0;
                return ($a['end_date_campaign'] < $b['end_date_campaign']) ? -1 : 1;
            });
        }

        return ($orderMode == 'DESC') ? array_reverse($data) : $data;
    }

    /**
     * @return string
     */
    public function getPostType(): string
    {
        return $this->postType;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
