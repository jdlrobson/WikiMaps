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

	/**
	 * CustomEditor hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/CustomEditor
	 *
	 * @param Article $article
	 * @param User $user
	 * @return bool
	 */
	public static function onCustomEditor( $article, $user ) {
		if( $article->getTitle()->getNamespace() === NS_MAP ) {
			$output = $article->getContext()->getOutput();
			$output->addHtml( 'DO THE MAP EDIT INTERFACE' );
			return false;
		}
		
		return true;
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
}
