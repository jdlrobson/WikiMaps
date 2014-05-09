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
	/**
	 * CustomEditor hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/CustomEditor
	 *
	 * @param Article $article
	 * @param User $user
	 * @return bool
	 */
	// public static function onCustomEditor( $article, $user ) {
	// 	if( $article->getTitle()->getNamespace() === NS_MAP ) {
	// 		$output = $article->getContext()->getOutput();
	// 		$output->addHtml( 'DO THE MAP EDIT INTERFACE' );
	// 		return false;
	// 	}

	// 	return true;
	// }

	// public static function onBeforePageDisplay( $out, $skin ) {
	// 	$out->clearHtml();
	// 	$out->addHtml( 'PRINT MAP HERE' );
	// 	return true;
	// }

	/**
	 * Declares JSON as the code editor language for Schema: pages.
	 * This hook only runs if the CodeEditor extension is enabled.
	 * @param Title $title
	 * @param string &$lang Page language.
	 * @return bool
	 */
	static function onCodeEditorGetPageLanguage( $title, &$lang ) {
		if ( $title->inNamespace( NS_MAP ) ) {
			$lang = 'json';
		}
		return true;
	}
}
