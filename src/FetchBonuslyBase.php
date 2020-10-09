<?php

namespace BonuslyChord;

use Balsama\Fetch;

class FetchBonuslyBase
{
    /**
     * @var string
     */
    private $baseUrl = 'https://bonus.ly/api/v1/';

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $url;

    private $recordsToSkip = 0;

    public function __construct($endpoint = 'bonuses') {
        $this->setAccessToken();
        $this->endpoint = $endpoint;
        $this->setUrl();
    }

    public function getJson() {
        $json = Fetch::fetch($this->url);
        if (!$json->success) {
            throw new \Exception('Failed to fetch JSON');
        }
        return json_decode(json_encode($json->result), true);
    }

    private function setAccessToken() {
        $this->accessToken = file_get_contents('.bonusly_access_token');
    }

    private function setUrl() {
        $this->url = $this->baseUrl . $this->endpoint . "?access_token=" . $this->accessToken . "&skip=" . $this->recordsToSkip;
    }

    public function setRecordsToSkip($numberOfRecordsToSkip) {
        $this->recordsToSkip = $numberOfRecordsToSkip;
        $this->setUrl();
    }

}