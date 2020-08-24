<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateTrelloJob implements ShouldQueue {
	protected $input;
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($input) {
		$this->input = $input;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		$client = new Client();

		$response = $client->request('PUT', $this->input['url'], ['form_params' => [
			'key' => $this->input['key'],
			'token' => $this->input['token'],
			'idList' => $this->input['idList'],
			'dueComplete' => $this->input['dueComplete'],
			'desc' => $this->input['desc'],
			'name' => $this->input['name'],
		]]);

	}
}
