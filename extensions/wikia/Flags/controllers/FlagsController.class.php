<?php

use Flags\Helper;
use Flags\Models\Flag;
use Flags\Models\FlagType;

class FlagsController extends WikiaApiController {

	private
		$model,
		$params,
		$status = false;

	/**
	 * Assigns a request's parameters to the object's property
	 * and sets a wikiId if it hasn't been specified as one
	 * of the parameters.
	 */
	private function getRequestParams() {
		$this->params = $this->request->getParams();
		if ( !isset( $this->params['wikiId'] ) ) {
			$this->params['wikiId'] = $this->wg->CityId;
		}
	}

	/**
	 * To prevent CSRF attacks it checks if a request is a POST one
	 * and if a sent token matches the user's one.
	 * Calls getRequestParams if the request is valid.
	 * @return bool
	 */
	private function processRequest() {
		if ( !$this->request->wasPosted()
			|| !$this->wg->User->matchEditToken( $this->getVal( 'token' ) )
		) {
			$this->response->setException( new \Exception( 'Invalid request' ) );
			return false;
		}

		$this->getRequestParams();
		return true;
	}

	/**
	 * Article level API
	 */

	/**
	 * Retrieves all data for all flag types available on the given wikia
	 * with an intent of rendering a modal with an edit form.
	 * It returns the types with instances on the page first and then all
	 * other types sorted alphabetically.
	 * To retrieve only types with instances on the given page:
	 * @see getFlagsForPage()
	 *
	 * @requestParam int wikiId
	 * @requestParam int pageId
	 * @response Array A list of flags with flag_type_id values as indexes.
	 *  One item contains the following fields:
	 * @if The page has an instance of the flag type
	 *	 	int flag_id
	 *
	 * 		int flag_type_id
	 * 		int wiki_id
	 *		int page_id
	 * 		int flag_group
	 * 		string flag_name
	 * 		string flag_view A name of a template of the flag
	 * 		string flag_view_url A full URL of the template
	 * 		int flag_targeting
	 * 		string|null flag_params_names
	 *
	 * 	@if flag_params_names is not empty
	 * 		params = [
	 * 			param_name => param_value
	 *		]
	 */
	public function getFlagsForPageForEdit() {
		$this->getRequestParams();

		if ( !isset( $this->params['pageId'] ) ) {
			return null;
		}

		/**
		 * 1. Get all flag types with instances for the page
		 */
		$allFlagTypes = $this->getAllFlagTypes( $this->params['wikiId'], $this->params['pageId'] );

		/**
		 * 2. Create links to templates for creation of "See more" links
		 */
		foreach ( $allFlagTypes as $flagTypeId => $flagType ) {
			$title = Title::newFromText( $flagType['flag_view'], NS_TEMPLATE );
			$allFlagTypes[$flagTypeId]['flag_view_url'] = $title->getFullURL();
		}

		/**
		 * 3. Set the response data
		 */
		$this->setResponseData( $allFlagTypes );
	}

	/**
	 * Retrieves all data for flags assigned to the given page
	 * with an intent of rendering them. To get all types of flags:
	 * @see getFlagsForPageForEdit()
	 *
	 * @requestParam int wikiId
	 * @requestParam int pageId
	 * @response Array A list of flags with flag_type_id values as indexes.
	 *  One item contains the following fields:
	 *	 	int flag_id
	 * 		int flag_type_id
	 * 		int wiki_id
	 *		int page_id
	 * 		int flag_group
	 * 		string flag_name
	 * 		string flag_view
	 * 		int flag_targeting
	 * 		string|null flag_params_names
	 *
	 * 	@if flag_params_names is not empty
	 * 		params = [
	 * 			param_name => param_value
	 *		]
	 */
	public function getFlagsForPage() {
		$this->getRequestParams();

		if ( !isset( $this->params['pageId'] ) ) {
			return null;
		}

		$this->model = new Flag();
		$flagsForPage = $this->model->getFlagsForPage( $this->params['wikiId'], $this->params['pageId'] );

		$this->setResponseData( $flagsForPage );
	}

	public function postFlagsEditForm() {
		$this->processRequest();

		if ( !isset( $this->params['pageId'] ) ) {
			return null;
		}

		/**
		 * Get the current status to compare
		 */
		$currentFlags = $this->getAllFlagTypes( $this->params['wikiId'], $this->params['pageId'] );

		$helper = new Helper();
		$flagsToChange = $helper->compareDataAndGetFlagsToChange( $currentFlags, $this->params );

		$this->model = new Flag();
		/**
		 * Add flags
		 */
		if ( !empty( $flagsToChange['toAdd'] ) ) {
			$flagsToAdd = [
				'wikiId' => $this->params['wikiId'],
				'pageId' => $this->params['pageId'],
				'flags' => $flagsToChange['toAdd'],
			];

			if ( $this->model->verifyParamsForAdd( $flagsToAdd ) ) {
				$this->model->addFlagsToPage( $flagsToAdd );
			}
		}

		/**
		 * Remove flags
		 */
		if ( !empty( $flagsToChange['toRemove'] ) ) {
			$flagsToRemove = [
				'flagsIds' => $flagsToChange['toRemove'],
			];
			if ( $this->model->verifyParamsForRemove( $flagsToRemove ) ) {
				$this->model->removeFlagsFromPage( $flagsToRemove );
			}
		}

		/**
		 * Update flags
		 */
		if ( !empty( $flagsToChange['toUpdate'] ) ) {
			$this->model->updateFlagsForPage( $flagsToChange['toUpdate'] );
		}
	}

	/**
	 * Adds flags to the given page. It accepts only POST requests
	 * with a valid User edit token.
	 *
	 * Required parameters:
	 * @requestParam int wikiId
	 * @requestParam int pageId
	 * @requestParam array flags
	 * @requestParam int flags['flagTypeId'] An ID of a flag type
	 *
	 * Optional parameters:
	 * @requestParam array flags['params'] An array of params structured like:
	 * [
	 * 	'paramName1' => 'paramValue1',
	 * 	'paramName2' => 'paramValue2',
	 * ]
	 */
	public function addFlagsToPage() {
		$this->processRequest();
		$this->model = new Flag();

		if ( $this->model->verifyParamsForAdd( $this->params ) ) {
			$this->status = $this->model->addFlagsToPage( $this->params );
		}

		$this->setVal( 'status', $this->status );
	}

	/**
	 * Removes flags from the given page. It accepts only POST requests
	 * with a valid User edit token.
	 *
	 * Required parameters:
	 * @requestParam array flagsIds An array of IDs of flags to remove
	 */
	public function removeFlagsFromPage() {
		$this->processRequest();
		$this->model = new Flag();

		if ( $this->model->verifyParamsForRemove( $this->params ) ) {
			$this->status = $this->model->removeFlagsFromPage( $this->params );
		}

		$this->setVal( 'status', $this->status );
	}

	/**
	 * Flag type level API
	 */

	/**
	 * Adds a new type of flags.
	 *
	 * Required parameters:
	 * @requestParam int wikiId
	 * @requestParam int flagGroup One of the keys in flagGroups property of the FlagType model
	 * @requestParam string flagName A name of the flag (not longer than 128 characters)
	 * @requestParam string flagView A title of a template used for rendering the flag
	 * @requestParam int flagTargeting A level of targeting: 0 -> readers, 1 -> contibutors, 2 -> admins
	 *
	 * Optional parameters:
	 * @requestParam string flagParamsNames A JSON-encoded array of names of parameters
	 * 		It's used for rendering inputs in the "Add a flag" form.
	 */
	public function addFlagType() {
		$this->processRequest();
		$this->model = new FlagType();

		if ( $this->model->verifyParamsForAdd( $this->params ) ) {
			$this->status = $this->model->addFlagType( $this->params );
		}

		$this->setVal( 'status', $this->status );
	}

	/**
	 * Removes a type of flags.
	 *
	 * Required parameters:
	 * @requestParam int flagTypeId
	 *
	 * IMPORTANT!
	 * When using this method be aware that it removes ALL instances of this type
	 * of flags with ALL of their parameters per the database's configuration.
	 */
	public function removeFlagType() {
		$this->processRequest();
		$this->model = new FlagType();

		if ( $this->model->verifyParamsForRemove( $this->params ) ) {
			$this->status = $this->model->removeFlagType( $this->params );
		}

		$this->setVal( 'status', $this->status );
	}

	private function getAllFlagTypes( $wikiId, $pageId ) {
		/**
		 * 1. Get flags assigned to the page
		 */
		$flagModel = new Flag();
		$flagsForPage = $flagModel->getFlagsForPage( $wikiId, $pageId );

		/**
		 * 2. Get all flag types for a wikia
		 */
		$flagTypeModel = new FlagType();
		$flagTypesForWikia = $flagTypeModel->getFlagTypesForWikia( $wikiId );

		/**
		 * 3. Return the united arrays - it is possible to merge them since both arrays use
		 * flag_type_id values as indexes
		 */
		return $flagsForPage + $flagTypesForWikia;
	}
}
