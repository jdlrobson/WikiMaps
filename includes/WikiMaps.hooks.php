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
	public static function onCustomEditor( $article, $user ) {
		if( $article->getTitle()->getNamespace() === NS_MAP ) {
			$output = $article->getContext()->getOutput();
			$output->addHtml( 'DO THE MAP EDIT INTERFACE' );
			return false;
		}
		
		return true;
	}

	public static function onBeforePageDisplay( $out, $skin ) {
		$out->clearHtml();
		$out->addHtml( 'PRINT MAP HERE' );
		return true;
	}
}
