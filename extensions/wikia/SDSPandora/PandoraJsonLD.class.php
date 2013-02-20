<?php
/**
 * Created by JetBrains PhpStorm.
 * User: adam
 * Date: 31.01.13
 * Time: 11:38
 * To change this template use File | Settings | File Templates.
 */

class PandoraJsonLD {

	/**
	 * Serialize Pandora object structure into JsonLD string.
	 * @param PandoraSDSElement $object
	 * @return string - json-ld formatted
	 */
	static public function toJsonLD ( PandoraSDSObject $object ) {
		return json_encode( $object );
	}

	/**
	 * Parse JsonLD string into Pandora objects structure.
	 * @param $json - string containing text representation of json-ld object
	 * @return PandoraSDSObject
	 */
	static public function pandoraSDSObjectFromJsonLD ( $json ) {
		$jsonObject = json_decode( $json );
		if ( $jsonObject === null ) {
			throw new WikiaException( "Invalid or malformed JSON" );
		}
		$rootObject = new PandoraSDSObject();
//		$rootObject->setType( PandoraSDSObject::TYPE_OBJECT );

		foreach ( $jsonObject as $key => $value ) {
			$node = new PandoraSDSObject();
			$node->setSubject( $key );
			if ( is_array( $value ) ) {
				$node->setType(PandoraSDSObject::TYPE_COLLECTION);
				$node->setValue( static::buildNextNode( $value ) );
			} elseif ( is_object( $value ) ) {
				$node->setType(PandoraSDSObject::TYPE_OBJECT);
				$node->setValue( static::buildNextNode( $value ) );
			} else {
				$node->setType( PandoraSDSObject::TYPE_LITERAL );
				$node->setValue( $value );
			}
			$rootObject->setValue( $node );
		}

		return $rootObject;
	}

	static protected function buildNextNode ( $jsonObject ) {
		$collection = array();
		foreach ( $jsonObject as $key => $value ) {
			$node = new PandoraSDSObject();
			if ( !is_numeric( $key ) ) {
				$node->setSubject( $key );
			}
			if ( is_array( $value ) ) {
				$node->setType(PandoraSDSObject::TYPE_COLLECTION);
				$node->setValue( static::buildNextNode( $value ) );
			} elseif ( is_object( $value ) ) {
				$node->setType(PandoraSDSObject::TYPE_OBJECT);
				$node->setValue( static::buildNextNode( $value ) );
			} else {
				$node->setType( PandoraSDSObject::TYPE_LITERAL );
				$node->setValue( $value );
			}
			$collection[] = $node;
		}
		return $collection;

	}
}