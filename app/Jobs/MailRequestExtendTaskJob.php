<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class MailRequestExtendTaskJob implements ShouldQueue {
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
		Mail::send($this->input['view'], $this->input, function ($m) {
			$m->from(env('MAIL_USERNAME'));
			$m->to($this->input['reciever_email'], $this->input['name'])->subject($this->input['subject']);
		});
	}
}
