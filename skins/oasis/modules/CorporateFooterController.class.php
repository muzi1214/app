<?php

class CorporateFooterController extends WikiaController {
	
	public function index() {
		global $wgCityId, $wgUser, $wgLang, $wgMemc;
		$catId = WikiFactoryHub::getInstance()->getCategoryId( $wgCityId );
		$mKey = wfSharedMemcKey( 'mOasisFooterLinks', $wgLang->getCode(), $catId );

		$this->footer_links = $wgMemc->get( $mKey );
		$this->copyright = $wgUser->getSkin()->getCopyright();

		if ( empty( $this->footer_links ) ) {
			$this->footer_links = $this->getWikiaFooterLinks();
			$wgMemc->set( $mKey, $this->footer_links, 86400 );
		}

		$this->hub = $this->getHub();
	}

	private function getHub() {
		wfProfileIn( __METHOD__ );
		global $wgCityId;

		$catInfo = HubService::getComscoreCategory($wgCityId);

		//i18n
		if (!empty($catInfo)) {
			$catInfo->cat_link = wfMsg('oasis-corporatefooter-hub-'. $catInfo->cat_name .'-link');
			$catInfo->cat_name = wfMsg('hub-'. $catInfo->cat_name);
		}

		wfProfileOut( __METHOD__ );
		return $catInfo;
	}

	/**
	 * @author Inez Korczynski <inez@wikia.com>
	 */
	 private function getWikiaFooterLinks() {
		wfProfileIn( __METHOD__ );

		global $wgCityId;
		$catId = WikiFactoryHub::getInstance()->getCategoryId( $wgCityId );
		$message_key = 'shared-Oasis-footer-wikia-links';
		$nodes = array();

		if ( !isset( $catId ) || null == ( $lines = getMessageAsArray( $message_key . '-' . $catId, false ) ) ) {
			if ( null == ( $lines = getMessageAsArray( $message_key, false ) ) ) {
				wfProfileOut( __METHOD__ );
				return $nodes;
			}
		}

		foreach( $lines as $line ) {
			$depth = strrpos( $line, '*' );
			if( $depth === 0 ) {
				$nodes[] = parseItem( $line );
			}
		}

		wfProfileOut( __METHOD__ );
		return $nodes;
	}
}
