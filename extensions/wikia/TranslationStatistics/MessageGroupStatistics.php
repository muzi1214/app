<?php

class MessageGroupStatistics {
	public static function forLanguage( $code, $mode = null ) {
                # Fetch from database
                $dbr = wfGetDB( DB_SLAVE );

                $conds = array( 'gs_lang' => $code );
                if ( !empty( $mode ) ) {
			$conds[] = self::getModeCondition( $mode );
                }

                $res = $dbr->select( 'groupstats', '*', $conds );

                while ( $row = $dbr->fetchRow( $res ) ) {
                        $stats[ $row['gs_group'] ] = array();
                }

		# Go over non-aggregate message groups filling missing entries
		$groups = MessageGroups::singleton()->getGroups();

		foreach ( $groups as $group ) {
			$id = $group->getId();
			if ( !empty( $stats[$id] ) ) {
				continue;
			}
			if ( empty ( $mode ) ) {
				$stats[$id] = self::forItem( $id, $code, $mode );
			}
		}

		# Go over aggregate message groups filling missing entries
		# @TODO :P

		return $stats;
  	}
 
	public static function forGroup( $id, $mode = null ) {
		# Fetch from database
		$dbr = wfGetDB( DB_SLAVE );

                $conds = array( 'gs_group' => $id );
                if ( !empty( $mode ) ) {
                        $conds[] = self::getModeCondition( $mode );
                }

		$res = $dbr->select( 'groupstats', '*', $conds );

		while ( $row = $dbr->fetchRow( $res ) ) {
			$stats[ $row['gs_lang'] ] = $row;
		}

		# Go over each language filling missing entries
		foreach ( Language::getLanguageNames() as $lang => $name ) {
			if ( !empty( $stats[$lang] ) ) {
				continue;
			}

			$stats[$lang] = self::forItem( $id, $lang );
		}

		return $stats;
	}
 
	// Used by the two function above to fill missing entries
	public static function forItem( $groupId, $code, $mode = null ) {
		# Check again if already in db ( to avoid overload in big clusters )

		$dbr = wfGetDB( DB_SLAVE );

		$conds = array( 'gs_group' => $groupId, 'gs_lang' => $code );
		if ( !empty( $mode ) ) {
			$conds[] = self::getModeCondition( $mode );
		}

		$res = $dbr->select( 'groupstats', '*', array( 'gs_group' => $groupId, 'gs_lang' => $code ) );

		if ( $row = $dbr->fetchRow( $res ) ) {
			// convert to array
			return $row;
		}

		// get group object
		$g = MessageGroups::getGroup( $groupId );

		# Calculate if missing and store in the db
		$collection = $g->initCollection( $code );
		$collection->filter( 'optional' );

		// Store the count of real messages for later calculation.
		$total = count( $collection );

		// Count fuzzy first
		$collection->filter( 'fuzzy' );
		$fuzzy = $total - count( $collection );

		// Count the completion percent
		$collection->filter( 'translated', false );
		$translated = count( $collection );

		$data = array(
				'gs_group' => $groupId,
				'gs_lang' => $code,
				'gs_total' => $total,
				'gs_translated' => $translated,
				'gs_fuzzy' => $fuzzy,
			     );

		# store result in DB
		$dbw = wfGetDB( DB_MASTER );

		$dbw->insert(
				'groupstats',
				$data
			    );

		return $data;
	}


	// this is used to completely rebuild statistics
	public static function populateStats() {

		// remove all records
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'groupstats', '*' );

		$groups = MessageGroups::singleton()->getGroups();

		// iterate over all groups
		foreach ( $groups as $g ) {
			echo "Populating " . $g->getId() . "...\n";
			self::forGroup( $g->getId() );
		}

		echo "Done!\n";
	}

	// attaches to ArticleSaveComplete
	public static function invalidateCache( &$article, &$user, $text, $summary, $minoredit, &$watchthis, $sectionanchor, &$flags, $revision, &$status, $baseRevId ) {	

		// we only care about namespace MediaWiki
		if ( $article->mTitle->getNamespace() !== NS_MEDIAWIKI ) {
			return true;
		}

		$name = $article->mTitle->getText();
                $parts = explode( $name, 2 );
                $lang = empty( $parts[1] ) ? false : $parts[1];

		// check if this is a valid language variant
		if ( Language::getLanguageName( $lang ) == ''  ) {
			return true;
		}

		// match message to group here
		if ( false ) {
			// message does not belong to any recognized group
			return true;
		}

		$conds = array(
			'gs_group' => $groupId,
			'gs_lang' => $lang,
		);

		$dbw = wfGetDB( DB_MASTER );

		$dbw->delete( 'groupstats', $conds );

		return true;
	}

	static function getModeCondition( $mode ) {
		switch ( $mode ) {
			case 1:
				return 'gs_translated != 0';
				break;
			case 2:
				return 'gs_translated = 0';
				break;
			case 3:
				return 'gs_translated = gs_total';
				break;
		}
	}
}
