<?php

class SpecialMap extends SpecialPage {

	public function __construct() {
		parent::__construct( 'Map' );
	}

	public function execute( $subPage ) {
		$this->setHeaders();
		$this->render( $subPage );
	}

	public function render() {
		$out = $this->getOutput();
		$data = WikiMapHelpers::makeGeoJSONFromRequest( $this->getRequest() );
		$out->addJsConfigVars( WikiMapHelpers::getSkinConfigVariables() );
		$out->addHtml( WikiMapHelpers::getMapHtml( $data ) );
	}

	public function setHeaders() {
		parent::setHeaders();
		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'wikimaps-special-map-title' ) );
		$out->addModuleStyles( 'wikimaps.styles' );
		$out->addModules( 'wikimaps.view.scripts' );
	}
}
