<?php

namespace Session;

interface ISessionProviderFactory {
	
	public function getSessionProvider(): ISessionProvider;
}
