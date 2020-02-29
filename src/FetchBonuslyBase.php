<?php

namespace BonuslyChord;

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
        $json = json_decode(file_get_contents($this->url), true);
        if (!$json['success']) {
            throw new \mysql_xdevapi\Exception('Failed to fetch JSON');
        }
        return $json['result'];
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