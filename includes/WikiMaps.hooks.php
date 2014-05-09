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
	public static function getSkinConfigVariables( $data ) {
		global $extWikiMapsTitleServer,
			$extWikiMapsImagePath,
			$extWikiMapsAttribution;

		return array(
			'extWikiMapsTitleServer' => $extWikiMapsTitleServer,
			'extWikiMapsAttribution' => $extWikiMapsAttribution,
			'extWikiMapsImagePath' => $extWikiMapsImagePath,
			'extWikiMapsCurrentMap' => $data,
		);
	}

	public static function onBeforePageDisplay( $out, $skin ) {
		$title = $out->getTitle();

		$action = Action::getActionName( $out->getContext() );
		if ( $title->getNamespace() === NS_MAP && $action === 'view' ) {
			$page = WikiPage::factory( $title );
			if ( $page->exists() ) {
				$content = $page->getContent();
				$data = $content->getJsonData();
			} else {
				$data = array();
			}

			$out->clearHtml();
			$out->addHtml( '<div id="mw-wiki-map-main" class="mw-wiki-map"></div>' );
			$out->addJsConfigVars( self::getSkinConfigVariables( $data ) );
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
