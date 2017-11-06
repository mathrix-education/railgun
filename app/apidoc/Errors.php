<?php
/**
 * @apiDefine Http400InvalidRequest
 * @apiError (Error 400) invalid_request Request is invalid
 */
/**
 * @apiDefine Http400InvalidRequest_Pagination
 * @apiError (Error 400) invalid_request page and perPage have to be strictly positive
 */
/**
 * @apiDefine Http401Unauthorized
 * @apiError (Error 401) unauthorized User does not have enough privileges
 */
/**
 * @apiDefine Http404ModelNotFound
 * @apiError (Error 404) model_not_found Model matching given data does not exist
 */
/**
 * @apiDefine Http422ModelSave
 * @apiError (Error 422) model_save Model save failed
 */
/**
 * @apiDefine Http422ModelValidation
 * @apiError (Error 422) model_validation Model data failed to pass validation
 */