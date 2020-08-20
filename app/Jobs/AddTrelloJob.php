<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddTrelloJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $input;

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
		//
	}
}
