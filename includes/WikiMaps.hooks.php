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

class WikiMapsHooks {

	public static function onBeforePageDisplay( $out, $skin ) {
		$title = $out->getTitle();
		$qs = $out->getRequest()->getValues();

		$action = Action::getActionName( $out->getContext() );
		if ( $title->getNamespace() === NS_MAP ) {
			$out->addModules( 'wikimaps.scripts' );
			if ( $action === 'view' && !isset( $qs['diff'] ) ) {
				$out->clearHtml();
				$out->addHtml( WikiMapHelpers::getMapHtmlFromTitle( $title ) );
				$out->addJsConfigVars( WikiMapHelpers::getSkinConfigVariables() );
				$out->addModuleStyles( 'wikimaps.styles' );
				$out->addModules( 'wikimaps.view.scripts' );
			}
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
		$parser->setHook( 'map', array( 'WikiMapHelpers', 'embedMapTag' ) );
	}
}
