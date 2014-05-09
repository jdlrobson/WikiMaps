<?php
/**
 * Hooks for Geo extension.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup EventLogging
 *
 */

class GeoHooks {
	public static function getSkinConfigVariables() {
		global $extWikiMapsTitleServer,
			$extWikiMapsAttribution;

		return array(
			'extWikiMapsTitleServer' => $extWikiMapsTitleServer,
			'extWikiMapsAttribution' => $extWikiMapsAttribution,
		);
	}

	public static function onBeforePageDisplay( $out, $skin ) {
		$action = Action::getActionName( $out->getContext() );
		if ( $out->getTitle()->getNamespace() === NS_MAP && $action === 'view' ) {
			$out->clearHtml();
			$out->addHtml( '<div id="mw-wiki-map-main" class="mw-wiki-map"></div>' );
			$out->addJsConfigVars( self::getSkinConfigVariables() );
			$out->addModuleStyles( 'wikimaps.styles' );
			$out->addModules( 'wikimaps.scripts' );
		}
		return true;
	}

	/**
	 * Declares JSON as the code editor language for Schema: pages.
	 * This hook only runs if the CodeEditor extension is enabled.
	 * @param Title $title
	 * @param string &$lang Page language.
	 * @return bool
	 */
	static function onCodeEditorGetPageLanguage( $title, &$lang ) {
		if ( $title->getContentModel() === 'GeoJSON' ) {
			$lang = 'json';
		}
		return true;
	}
}
