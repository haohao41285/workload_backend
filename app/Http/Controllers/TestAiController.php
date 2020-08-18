<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;

class TestAiController extends Controller {

	public function import() {
		$access_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo0NjEsInNpZ24iOiJlMTI0ZjdiYzc4OWU3YTk0OWEyNjljY2M2NjBmYzE4YiIsImlhdCI6MTU5NzcxNDQzMH0.8f_M4nJJK3Wc-7Y747bjPLJB43QIa0uBayGQIIwUEng';
		$contacts =
			[
			[
				'phone_number' => '0367389353',
				'otp_code' => '0805',
				'danh_xung' => 'ong',
				'ho_ten' => 'Nguyễn Văn Thiệu',
			],
		];
		$campaign_id = 4871;
		$url_api = 'https://ai-callcenter.vietguys.biz/api/campaigns/' . $campaign_id . '/import';

		$client = new Client();

		$response = $client->request('POST', $url_api, ['form_params' => [
			'access_token' => $access_token,
			'contacts' => $contacts,
			'campaign_id' => $campaign_id,
		]]);

		echo $response->getBody();
	}

	public function export() {

		$access_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo0NjEsInNpZ24iOiJlMTI0ZjdiYzc4OWU3YTk0OWEyNjljY2M2NjBmYzE4YiIsImlhdCI6MTU5NzcxNDQzMH0.8f_M4nJJK3Wc-7Y747bjPLJB43QIa0uBayGQIIwUEng';
		$campaign_id = 4871;
		$url_api = "https://ai-callcenter.vietguys.biz/api/campaigns/" . $campaign_id . "/export?access_token=" . $access_token;

		$client = new Client();
		$response = $client->request('GET', $url_api);
		$body = (string) $response->getBody();

		echo $body;
	}
	public function generateToken() {
		$secret_key = 'vpBsvuSfJyuOY2tAuYPD';
		// $campaign_id = 4871;
		$url_api = "https://ai-callcenter.vietguys.biz/api/users/generate-access-token?secret_key=" . $secret_key;

		$client = new Client();
		$response = $client->request('GET', $url_api);
		$body = (string) $response->getBody();

		echo $body;
	}
}
