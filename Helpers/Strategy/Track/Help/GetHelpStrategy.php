<?php

namespace Strategy\Track\Help;


use Strategy\Track\AbstractTrackStrategy;

class GetHelpStrategy extends AbstractTrackStrategy {

	public function get() {
		if ($this->order->isPayer($this->getUserId())) {
			return (new GetPayerHelpStrategy($this->order))->get();
		}
		return (new GetWorkerHelpStrategy($this->order))->get();
	}
}