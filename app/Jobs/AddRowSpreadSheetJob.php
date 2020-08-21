<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddRowSpreadSheetJob implements ShouldQueue {
	protected $job_arr;

	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($job_arr) {
		$this->job_arr = $job_arr;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		\Log::info('ok');
		try {
			appendRow($this->job_arr);
		} catch (\Exception $e) {
			\Log::info($e);
		}

	}
}
