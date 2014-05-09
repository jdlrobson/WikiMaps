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
		global $extWikiMapsTileServer,
			$extWikiMapsImagePath,
			$extWikiMapsAttribution;

		$vars = array(
			'extWikiMapsTileServer' => $extWikiMapsTileServer,
			'extWikiMapsAttribution' => $extWikiMapsAttribution,
			'extWikiMapsImagePath' => $extWikiMapsImagePath,
		);
		if ( $data ) {
			$vars['extWikiMapsCurrentMap'] = $data;
		}
		return $vars;
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
				$data = null;
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

	public static function onWikiMapParserInit( Parser $parser ) {
		$parser->setHook( 'map', array( __CLASS__, 'embedMapTag' ) );
	}

	/**
	 * Probably needs linktable update
	 * <map title="Map:MyMap" />
	 */
	public static function embedMapTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		$title = Title::newFromText( $args['title'] );
		$page = WikiPage::factory( $title );
		if ( $page->exists() ) {
			$content = $page->getContent();
			$data = $content->getJsonData();
		} else {
			$data = array();
		}
		$out = $parser->getOutput();
		$out->addJsConfigVars( self::getSkinConfigVariables( $data ) );
		$out->addModuleStyles( 'wikimaps.styles' );
		$out->addModules( 'wikimaps.scripts' );

		return '<div id="mw-wiki-map-main" class="mw-wiki-map"></div>';
	}

}
