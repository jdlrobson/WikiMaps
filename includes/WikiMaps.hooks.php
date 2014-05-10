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
		global $extWikiMapsTileServer,
			$extWikiMapsImagePath,
			$extWikiMapsAttribution;

		return array(
			'extWikiMapsTileServer' => $extWikiMapsTileServer,
			'extWikiMapsAttribution' => $extWikiMapsAttribution,
			'extWikiMapsImagePath' => $extWikiMapsImagePath,
		);
	}

	public static function getMapHtml( $title, $className='' ) {
		$page = WikiPage::factory( $title );
		$attrs = array(
			"class" => "mw-wiki-map " . $className,
		);
		if ( $page->exists() ) {
			$content = $page->getContent();
			$data = $content->getJsonData();
			$data = json_encode( $data );
			$attrs['data-map'] = $data;
		}

		return Html::element( 'div',
			$attrs
		);
	}

	public static function onBeforePageDisplay( $out, $skin ) {
		$title = $out->getTitle();

		$action = Action::getActionName( $out->getContext() );
		if ( $title->getNamespace() === NS_MAP && $action === 'view' ) {
			$out->clearHtml();
			$out->addHtml( self::getMapHtml( $title ) );
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

	public static function onWikiMapParserInit( Parser $parser ) {
		$parser->setHook( 'map', array( __CLASS__, 'embedMapTag' ) );
	}

	/**
	 * Probably needs linktable update
	 * <map title="Map:MyMap" />
	 */
	public static function embedMapTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		if ( isset( $args['title'] ) ) {
			$title = Title::newFromText( $args['title'], NS_MAP );
			$out = $parser->getOutput();
			$out->addJsConfigVars( self::getSkinConfigVariables() );
			$out->addModuleStyles( 'wikimaps.styles' );
			$out->addModules( 'wikimaps.scripts' );

			$className = $args['class'] ? $args['class'] : '';
			return self::getMapHtml( $title, $className );
		} else {
			return '';
		}
	}

}
